<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Rapport;
use Illuminate\Support\Facades\Storage;

name('specialite.rapports');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;
    use WithFileUploads;

    public $specialite;
    public $hasSpecialite = false;

    // Statistiques
    public $totalSoumis = 0;
    public $totalEnAttente = 0;
    public $totalRejetes = 0;

    // Formulaire nouveau rapport
    public $titre = '';
    public $fichier;

    // Modal modification
    public $showEditModal = false;
    public $editRapportId = null;
    public $editTitre = '';
    public $editFichier;

    // Modal voir rapport
    public $showViewModal = false;
    public $selectedRapport = null;

    public function mount()
    {
        $user = auth()->user();

        if ($user->specialite_id && $user->specialite && $user->aLeRole('enseignant')) {
            $this->hasSpecialite = true;
            $this->specialite = $user->specialite;

            if ($this->specialite) {
                $this->totalSoumis = Rapport::where('user_id', $user->id)
                    ->where('specialite_id', $this->specialite->id)
                    ->where('status', 'Success')
                    ->count();

                $this->totalEnAttente = Rapport::where('user_id', $user->id)
                    ->where('specialite_id', $this->specialite->id)
                    ->where('status', 'pending')
                    ->count();

                $this->totalRejetes = Rapport::where('user_id', $user->id)
                    ->where('specialite_id', $this->specialite->id)
                    ->where('status', 'failed')
                    ->count();
            }
        }
    }

    public function getMesRapportsProperty()
    {
        if (!$this->hasSpecialite) {
            return collect()->paginate(10);
        }

        return Rapport::where('user_id', auth()->id())
            ->where('specialite_id', $this->specialite->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function submitRapport()
    {
        $this->validate([
            'titre'   => 'required|string|max:255',
            'fichier' => 'required|file|mimes:pdf|max:10240',
        ]);

        $cheminFichier = $this->fichier->store('rapports', 'public');

        Rapport::create([
            'title'         => $this->titre,
            'content'       => $cheminFichier,
            'user_id'       => auth()->id(),
            'specialite_id' => $this->specialite->id,
            'filiere_id'    => $this->specialite->filiere_id,
            'status'        => 'pending',
        ]);

        $this->reset(['titre', 'fichier']);
        $this->dispatch('notify', ['message' => 'Rapport soumis avec succès ! En attente de validation.', 'type' => 'success']);
        $this->resetPage();
    }

    public function editRapport($id)
    {
        $rapport = Rapport::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $this->editRapportId = $id;
        $this->editTitre = $rapport->title;
        $this->showEditModal = true;
    }

    public function updateRapport()
    {
        $this->validate([
            'editTitre'   => 'required|string|max:255',
            'editFichier' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $rapport = Rapport::findOrFail($this->editRapportId);

        if ($this->editFichier) {
            // Supprimer l'ancien fichier si existant
            if ($rapport->content) {
                Storage::disk('public')->delete($rapport->content);
            }
            $cheminFichier = $this->editFichier->store('rapports', 'public');
            $rapport->content = $cheminFichier;
        }

        $rapport->title = $this->editTitre;
        $rapport->save();

        $this->reset(['editTitre', 'editFichier', 'editRapportId', 'showEditModal']);
        $this->dispatch('notify', ['message' => 'Rapport modifié avec succès !', 'type' => 'success']);
        $this->resetPage();
    }

    public function deleteRapport($id)
    {
        $rapport = Rapport::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        if ($rapport->content) {
            Storage::disk('public')->delete($rapport->content);
        }

        $rapport->delete();

        $this->dispatch('notify', ['message' => 'Rapport supprimé avec succès.', 'type' => 'success']);
        $this->resetPage();
    }

    public function sendRapport($id)
    {
        $rapport = Rapport::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $rapport->status = 'Success';
        $rapport->save();

        $this->dispatch('notify', ['message' => 'Rapport envoyé avec succès !', 'type' => 'success']);
        $this->resetPage();
    }

    public function viewRapport($id)
    {
        $this->selectedRapport = Rapport::findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedRapport = null;
    }

    public function downloadRapport($id)
    {
        $rapport = Rapport::findOrFail($id);
        if ($rapport->content) {
            return Storage::disk('public')->download($rapport->content);
        }
        $this->dispatch('notify', ['message' => 'Aucun fichier disponible.', 'type' => 'error']);
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
                        <h1 class="text-3xl font-bold text-gray-800">Rapports Semestriels</h1>
                        <p class="text-gray-500">Soumettez vos rapports PDF pour la spécialité : {{ $specialite->name }}</p>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-800">Rapports Envoyés</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ $totalSoumis }}</p>
                        <p class="text-sm text-gray-500">Validés / Envoyés</p>
                    </div>
                    <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-800">Rapports en Attente</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ $totalEnAttente }}</p>
                        <p class="text-sm text-gray-500">En attente de validation</p>
                    </div>
                    <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-800">Rapports Rejetés</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ $totalRejetes }}</p>
                        <p class="text-sm text-gray-500">Rejetés ou supprimés</p>
                    </div>
                </div>

                <!-- Liste de tes rapports -->
                <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Vos Rapports</h2>

                    @if($this->mesRapports->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-separate border-spacing-y-2">
                                <thead>
                                    <tr class="bg-gray-100 rounded-lg">
                                        <th class="p-4 text-sm font-medium text-gray-600">Titre</th>
                                        <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                        <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                        <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->mesRapports as $rapport)
                                        <tr class="bg-gray-50 rounded-lg">
                                            <td class="p-4 font-medium">{{ $rapport->title }}</td>
                                            <td class="p-4">{{ $rapport->created_at->format('d/m/Y') }}</td>
                                            <td class="p-4">
                                                @if($rapport->status === 'Success')
                                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Rapport envoyé</span>
                                                @elseif($rapport->status === 'pending')
                                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">En attente</span>
                                                @else
                                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">Rejeté</span>
                                                @endif
                                            </td>
                                            <td class="p-4 space-x-4">
                                                @if($rapport->status === 'pending')
                                                    <button wire:click="editRapport({{ $rapport->id }})" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Modifier" aria-label="Modifier"><x-icone-action nom="modifier" /></button>
                                                    <button wire:click="deleteRapport({{ $rapport->id }})" onclick="return confirm('Voulez-vous vraiment supprimer ce rapport ?')" class="bouton-icone text-red-600 hover:bg-red-50" title="Supprimer" aria-label="Supprimer"><x-icone-action nom="supprimer" /></button>
                                                    <button wire:click="sendRapport({{ $rapport->id }})" class="text-green-600 hover:underline">Envoyer</button>
                                                @endif
                                                @if($rapport->content)
                                                    <a href="{{ Storage::url($rapport->content) }}" target="_blank" class="text-indigo-600 hover:underline">Voir PDF</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-center">
                            {{ $this->mesRapports->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">Vous n'avez encore soumis aucun rapport pour cette spécialité.</p>
                        </div>
                    @endif
                </div>

                <!-- Formulaire Soumettre Nouveau Rapport (PDF uniquement) -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Soumettre un Nouveau Rapport (PDF)</h2>

                    <form wire:submit="submitRapport">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Titre du Rapport</label>
                            <input wire:model="titre" type="text" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ex: Rapport Semestriel - Performances et Recommandations">
                        </div>

                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Fichier PDF du rapport <span class="text-red-500">*</span></label>
                            <input type="file" wire:model="fichier" accept=".pdf" class="w-full p-3 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('fichier') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white py-3 px-8 rounded-lg hover:bg-indigo-700 transition duration-200 font-medium">
                                Soumettre le rapport
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Modal Modifier Rapport (PDF uniquement) -->
                @if($showEditModal)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Modifier le Rapport</h2>

                            <form wire:submit="updateRapport">
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Titre du Rapport</label>
                                    <input wire:model="editTitre" type="text" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div class="mb-8">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Nouveau Fichier PDF (optionnel - remplace l'ancien)</label>
                                    <input type="file" wire:model="editFichier" accept=".pdf" class="w-full p-3 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>

                                <div class="flex justify-end space-x-4">
                                    <button type="button" wire:click="$set('showEditModal', false)" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                                        Enregistrer les modifications
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Modal Voir Rapport -->
                @if($showViewModal && $selectedRapport)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
                            <div class="flex justify-between items-center mb-6 border-b pb-4">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800">{{ $selectedRapport->title }}</h2>
                                    <p class="text-gray-500 mt-1">Soumis le {{ $selectedRapport->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                                <button wire:click="closeViewModal" class="text-gray-500 hover:text-gray-700">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="text-center py-8">
                                <p class="text-gray-600 mb-6">Le rapport est disponible en PDF :</p>
                                @if($selectedRapport->content)
                                    <a href="{{ Storage::url($selectedRapport->content) }}" target="_blank" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Ouvrir / Télécharger le PDF
                                    </a>
                                @else
                                    <p class="text-red-500">Aucun fichier joint.</p>
                                @endif
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button wire:click="closeViewModal" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">
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