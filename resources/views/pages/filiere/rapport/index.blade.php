<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Rapport;

name('specialites.rapports');
middleware(['auth', 'verified']);

new class extends Component {
    public $filiere;
    public $hasFiliere = false;

    public $enseignants = [];
    public $selectedEnseignantId = null;
    public $rapports = [];

    public $showRapportsModal = false;

    public function mount()
    {
        $user = auth()->user();

        if ($user->filiere_id && $user->filiere) {
            $this->hasFiliere = true;
            $this->filiere = $user->filiere;

            // Charger tous les enseignants de la filière
            $this->enseignants = User::where('filiere_id', $this->filiere->id)
                ->where('role', 'enseignant')
                ->where('status', 'Success')
                ->orderBy('name')
                ->get();
        }
    }

    public function openRapportsModal($enseignantId)
    {
        $this->selectedEnseignantId = $enseignantId;
        $this->rapports = Rapport::with(['specialite'])
            ->where('user_id', $enseignantId)
            ->where('filiere_id', $this->filiere->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->showRapportsModal = true;
    }

    public function closeRapportsModal()
    {
        $this->showRapportsModal = false;
        $this->selectedEnseignantId = null;
        $this->rapports = [];
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Rapports Semestriels</h1>
                    <p class="text-gray-500">Consultez et téléchargez les rapports semestriels des spécialités de la filière.</p>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Rapports Soumis</h3>
                    <p class="text-3xl font-bold text-indigo-600">{{ Rapport::where('filiere_id', ($filiere?->id ?? 0))->where('status', 'Success')->count() }}</p>
                    <p class="text-sm text-gray-500">Pour l'année en cours</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Rapports en Attente</h3>
                    <p class="text-3xl font-bold text-indigo-600">{{ Rapport::where('filiere_id', ($filiere?->id ?? 0))->where('status', 'pending')->count() }}</p>
                    <p class="text-sm text-gray-500">Pour le semestre actuel</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Rapports Rejetés/Supprimés</h3>
                    <p class="text-3xl font-bold text-indigo-600">{{ Rapport::where('filiere_id', ($filiere?->id ?? 0))->where('status', 'failed')->count() }}</p>
                    <p class="text-sm text-gray-500">Pour l'année en cours</p>
                </div>
            </div>

            <!-- Liste des enseignants -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Enseignants de la filière</h2>

                @if(count($enseignants) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-2">
                            <thead>
                                <tr class="bg-gray-100 rounded-lg">
                                    <th class="p-4 text-sm font-medium text-gray-600">Enseignant</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Email</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Spécialité</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Nombre de rapports</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enseignants as $enseignant)
                                    <tr class="bg-gray-50 rounded-lg">
                                        <td class="p-4">{{ $enseignant->name }} {{ $enseignant->lastname ?? '' }}</td>
                                        <td class="p-4">{{ $enseignant->email }}</td>
                                        <td class="p-4">
                                            @if($enseignant->specialite)
                                                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">{{ $enseignant->specialite->name }}</span>
                                            @else
                                                <span class="text-gray-500">Non spécifiée</span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-center">
                                            {{ Rapport::where('user_id', $enseignant->id)->where('filiere_id', ($filiere?->id ?? 0))->count() }}
                                        </td>
                                        <td class="p-4">
                                            <button wire:click="openRapportsModal({{ $enseignant->id }})" class="text-indigo-600 hover:underline">
                                                Voir les rapports
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        Aucun enseignant enregistré dans votre filière pour le moment.
                    </div>
                @endif
            </div>

            <!-- Modal Liste des rapports -->
            @if($showRapportsModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-semibold text-gray-800">
                                Rapports de {{ $enseignants->find($selectedEnseignantId)?->name ?? 'Enseignant' }}
                            </h2>
                            <button wire:click="closeRapportsModal" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        @if($rapports->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-separate border-spacing-y-2">
                                    <thead>
                                        <tr class="bg-gray-100 rounded-lg">
                                            <th class="p-4 text-sm font-medium text-gray-600">Titre</th>
                                            <th class="p-4 text-sm font-medium text-gray-600">Spécialité</th>
                                            <th class="p-4 text-sm font-medium text-gray-600">Date de création</th>
                                            <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                            <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rapports as $rapport)
                                            <tr class="bg-gray-50 rounded-lg">
                                                <td class="p-4 font-medium">{{ $rapport->title }}</td>
                                                <td class="p-4">
                                                    @if($rapport->specialite)
                                                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">{{ $rapport->specialite->name }}</span>
                                                    @else
                                                        <span class="text-gray-500">Générale</span>
                                                    @endif
                                                </td>
                                                <td class="p-4 text-gray-600">{{ $rapport->created_at->format('d/m/Y H:i') }}</td>
                                                <td class="p-4">
                                                    @if($rapport->status === 'Success')
                                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Validé</span>
                                                    @elseif($rapport->status === 'pending')
                                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">En attente</span>
                                                    @elseif($rapport->status === 'failed')
                                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">Rejeté/Supprimé</span>
                                                    @else
                                                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">{{ ucfirst($rapport->status) }}</span>
                                                    @endif
                                                </td>
                                                <td class="p-4">
                                                    <a href="#" class="bouton-icone text-sky-600 hover:bg-sky-50" title="Voir" aria-label="Voir"><x-icone-action nom="voir" /></a>
                                                    <!-- <a href="#" class="text-indigo-600 hover:underline">Télécharger PDF</a> -->
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <p class="text-gray-500 text-lg">Cet enseignant n'a soumis aucun rapport pour le moment.</p>
                            </div>
                        @endif

                        <div class="mt-8 flex justify-end">
                            <button wire:click="closeRapportsModal" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endvolt
</x-layouts.app>