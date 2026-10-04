<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Ue;

name('specialite.ue');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $specialite;
    public $hasSpecialite = false;
    public $totalUe = 0;

    // Modal détails
    public $showDetailsModal = false;
    public $selectedUe = null;

    public function mount()
    {
        $user = auth()->user();

        if ($user->specialite_id && $user->specialite && $user->aLeRole('enseignant')) {
            $this->hasSpecialite = true;
            $this->specialite = $user->specialite;

            if ($this->specialite) {
                $this->totalUe = Ue::where('specialite_id', $this->specialite->id)
                    ->where('status', '!=', 'failed')
                    ->count();
            }
        }
    }

    #[Computed]
    public function ues()
    {
        if (!$this->hasSpecialite || !$this->specialite) {
            return Ue::whereRaw('1 = 0')->paginate(15);
        }

        return Ue::with('cour')->where('specialite_id', $this->specialite->id)
            ->where('status', '!=', 'failed')
            ->orderBy('name')
            ->paginate(15);
    }

    public function openDetailsModal($id)
    {
        $this->selectedUe = Ue::with(['cour', 'specialite'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedUe = null;
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
                        <a href="/specialite/profil" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Voir mon profil
                        </a>
                    </div>
                </div>
            @else

                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Unités d'Enseignement (UE)</h1>
                        <p class="text-gray-500">UE rattachées à {{ $specialite->name }}</p>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-8">
                    <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-800">UE Actives</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ $totalUe }}</p>
                        <p class="text-sm text-gray-500">Dans la spécialité</p>
                    </div>
                </div>

                <!-- Liste des UE -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Unités d'Enseignement</h2>

                    @if($this->ues->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-separate border-spacing-y-2">
                                <thead>
                                    <tr class="bg-gray-100 rounded-lg">
                                        <th class="p-4 text-sm font-medium text-gray-600">Code</th>
                                        <th class="p-4 text-sm font-medium text-gray-600">Nom UE</th>
                                        <th class="p-4 text-sm font-medium text-gray-600">Nb Cours</th>
                                        <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                        <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->ues as $ue)
                                        <tr class="bg-gray-50 rounded-lg">
                                            <td class="p-4 font-mono">{{ $ue->code }}</td>
                                            <td class="p-4 font-medium">{{ $ue->name }}</td>
                                            <td class="p-4 text-center">{{ $ue->cour->count() }}</td>
                                            <td class="p-4">
                                                @if($ue->status === 'Success')
                                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Active</span>
                                                @else
                                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">{{ ucfirst($ue->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="p-4">
                                                <button wire:click="openDetailsModal({{ $ue->id }})" class="bouton-icone text-sky-600 hover:bg-sky-50" title="Détails" aria-label="Détails"><x-icone-action nom="voir" /></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-center">
                            {{ $this->ues->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">Aucune UE rattachée à cette spécialité pour le moment.</p>
                        </div>
                    @endif
                </div>

                <!-- Modal Détails UE -->
                @if($showDetailsModal && $selectedUe)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-2xl font-semibold text-gray-800">Détails de l'UE</h2>
                                <button wire:click="closeDetailsModal" class="text-gray-500 hover:text-gray-700">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div class="text-center mb-6">
                                    <h3 class="text-xl font-bold text-gray-800">{{ $selectedUe->name }}</h3>
                                    <p class="text-gray-500 font-mono mt-1">Code : {{ $selectedUe->code }}</p>
                                </div>

                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Spécialité</span>
                                    <span class="font-medium">{{ $selectedUe->specialite->name ?? 'Générale' }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Nombre de cours</span>
                                    <span class="font-medium">{{ $selectedUe->cour->count() }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Type de formation</span>
                                    <span class="font-medium">{{ $selectedUe->formation_type ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between py-2">
                                    <span class="text-gray-600">Statut</span>
                                    @if($selectedUe->status === 'Success')
                                        <span class="px-4 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Active</span>
                                    @else
                                        <span class="px-4 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">{{ ucfirst($selectedUe->status) }}</span>
                                    @endif
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