<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;

name('specialite.etudiants');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $specialite;
    public $hasSpecialite = false;
    public $totalEtudiants = 0;
    public $totalActifs = 0;

    // Pour le modal détails
    public $showDetailsModal = false;
    public $selectedEtudiant = null;

    public function mount()
    {
        $user = auth()->user();

        if ($user->specialite_id && $user->specialite && $user->aLeRole('enseignant')) {
            $this->hasSpecialite = true;
            $this->specialite = $user->specialite;

            if ($this->specialite) {
                $this->totalEtudiants = User::where('specialite_id', $this->specialite->id)
                    ->where('role', 'student')
                    ->count();

                $this->totalActifs = User::where('specialite_id', $this->specialite->id)
                    ->where('role', 'student')
                    ->where('status', 'Success')
                    ->count();
            }
        }
    }

    public function getEtudiantsProperty()
    {
        if (!$this->hasSpecialite || !$this->specialite) {
            return collect()->paginate(15);
        }

        return User::where('specialite_id', $this->specialite->id)
            ->where('role', 'student')
            ->orderBy('name')
            ->paginate(15);
    }

    public function openDetailsModal($id)
    {
        $this->selectedEtudiant = User::findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedEtudiant = null;
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            @if(!$hasSpecialite)
                <div class="min-h-[60vh] flex items-center justify-center">
                    <div class="bg-white rounded-2xl shadow-xl p-10 max-w-lg text-center">
                        <div class="mx-auto w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Aucune spécialité attribuée</h2>
                        <p class="text-gray-600 mb-6">Vous n'êtes responsable d'aucune spécialité pour le moment.</p>
                        <div class="bg-gray-50 rounded-xl p-4 mb-6">
                            <p class="text-sm text-gray-500">Contactez l'administration pour plus d'informations.</p>
                        </div>
                        <a href="/specialite/profil" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Voir mon profil
                        </a>
                    </div>
                </div>
            @else

                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Liste des Apprenants</h1>
                        <p class="text-gray-500">Apprenants inscrits en {{ $specialite->name }}</p>
                    </div>
                </div>

                <!-- Statistiques (sans taux de présence) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-800">Apprenants Inscrits</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ $totalEtudiants }}</p>
                        <p class="text-sm text-gray-500">Dans la spécialité</p>
                    </div>
                    <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-800">Apprenants Actifs</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ $totalActifs }}</p>
                        <p class="text-sm text-gray-500">Semestre actuel</p>
                    </div>
                </div>

                <!-- Liste des Apprenants -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Apprenants Inscrits</h2>

                    @if($this->etudiants->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-separate border-spacing-y-2">
                                <thead>
                                    <tr class="bg-gray-100 rounded-lg">
                                        <th class="p-4 text-sm font-medium text-gray-600">Nom</th>
                                        <th class="p-4 text-sm font-medium text-gray-600">Matricule</th>
                                        <th class="p-4 text-sm font-medium text-gray-600">Contact</th>
                                        <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                        <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->etudiants as $etudiant)
                                        <tr class="bg-gray-50 rounded-lg">
                                            <td class="p-4 font-medium">
                                                {{ $etudiant->name }} {{ $etudiant->lastname ?? '' }}
                                            </td>
                                            <td class="p-4 font-mono">{{ $etudiant->matricule ?? '—' }}</td>
                                            <td class="p-4">
                                                {{ $etudiant->email }}
                                                @if($etudiant->contact)
                                                    <br><span class="text-sm text-gray-500">Tél: {{ $etudiant->contact }}</span>
                                                @endif
                                            </td>
                                            <td class="p-4">
                                                @if($etudiant->status === 'Success')
                                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Inscrit</span>
                                                @else
                                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">{{ ucfirst($etudiant->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="p-4">
                                                <button wire:click="openDetailsModal({{ $etudiant->id }})" class="bouton-icone text-sky-600 hover:bg-sky-50" title="Détails" aria-label="Détails"><x-icone-action nom="voir" /></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-center">
                            {{ $this->etudiants->links() }}
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">
                                Exporter la liste (PDF)
                            </button>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">Aucun apprenant inscrit dans cette spécialité pour le moment.</p>
                        </div>
                    @endif
                </div>

                <!-- Modal Détails Apprenant -->
                @if($showDetailsModal && $selectedEtudiant)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-2xl font-semibold text-gray-800">Détails de l'apprenant</h2>
                                <button wire:click="closeDetailsModal" class="text-gray-500 hover:text-gray-700">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div class="text-center mb-6">
                                    <div class="w-24 h-24 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        @if($selectedEtudiant->photo)
                                            <img src="{{ asset('storage/' . $selectedEtudiant->photo) }}" alt="Photo" class="w-20 h-20 rounded-full object-cover">
                                        @else
                                            <span class="text-4xl font-bold text-indigo-600">
                                                {{ strtoupper(substr($selectedEtudiant->name, 0, 1)) }}{{ strtoupper(substr($selectedEtudiant->lastname ?? '', 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-800">
                                        {{ $selectedEtudiant->name }} {{ $selectedEtudiant->lastname ?? '' }}
                                    </h3>
                                </div>

                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Matricule</span>
                                    <span class="font-medium">{{ $selectedEtudiant->matricule ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Email</span>
                                    <span class="font-medium">{{ $selectedEtudiant->email }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Téléphone</span>
                                    <span class="font-medium">{{ $selectedEtudiant->contact ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">WhatsApp</span>
                                    <span class="font-medium">{{ $selectedEtudiant->whatsapp ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Statut</span>
                                    @if($selectedEtudiant->status === 'Success')
                                        <span class="px-4 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Actif / Inscrit</span>
                                    @else
                                        <span class="px-4 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">{{ ucfirst($selectedEtudiant->status) }}</span>
                                    @endif
                                </div>
                                <div class="flex justify-between py-2">
                                    <span class="text-gray-600">Inscrit le</span>
                                    <span class="font-medium">{{ $selectedEtudiant->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button wire:click="closeDetailsModal" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">
                                    Fermer
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endvolt
</x-layouts.app>