<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Ue;
use App\Models\Specialite;
use Illuminate\Support\Str;

name('filiere.ue.index');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $filiere;
    public $hasFiliere = false;

    // Formulaire UE
    public $name = '';
    public $specialite_id = null;

    // Modals
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showDetailsModal = false;

    public $selectedUe = null;

    // Filtre
    public $selectedSpecialiteFilter = 'all';

    // Notifications
    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = 'success';
    public array $formErrors = [];

    public function mount()
    {
        $user = auth()->user();
        if ($user->filiere_id && $user->filiere) {
            $this->hasFiliere = true;
            $this->filiere = $user->filiere;
        }
    }

    public function getUesProperty()
    {
        if (!$this->hasFiliere || !$this->filiere) {
            return collect()->paginate(15);
        }

        $query = Ue::with(['specialite', 'cour'])
            ->where('filiere_id', $this->filiere->id)
            ->where('status', '!=', 'failed')
            ->orderBy('name');

        if ($this->selectedSpecialiteFilter !== 'all') {
            $query->where('specialite_id', $this->selectedSpecialiteFilter);
        }

        return $query->paginate(15);
    }

    public function getSpecialitesProperty()
    {
        if (!$this->filiere) return collect();

        return Specialite::where('filiere_id', $this->filiere->id)
            ->where('status', 'Success')
            ->orderBy('name')
            ->get();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($id)
    {
        $this->selectedUe = Ue::findOrFail($id);
        $this->name = $this->selectedUe->name;
        $this->specialite_id = $this->selectedUe->specialite_id;
        $this->formErrors = [];
        $this->showEditModal = true;
    }

    public function openDetailsModal($id)
    {
        $this->selectedUe = Ue::with(['specialite', 'cour', 'filiere'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function openDeleteModal($id)
    {
        $this->selectedUe = Ue::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showDetailsModal = false;
        $this->selectedUe = null;
        $this->resetForm();
    }

    private function generateCode()
    {
        // Génère un code unique : UE-XXXXXX
        do {
            $code = 'UE-' . strtoupper(Str::random(6));
        } while (Ue::where('code', $code)->exists());
        
        return $code;
    }

    public function save()
    {
        $this->formErrors = [];

        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'specialite_id' => 'nullable|exists:specialites,id',
            ]);

            Ue::create([
                'name' => $this->name,
                'code' => $this->generateCode(),
                'specialite_id' => $this->specialite_id ?: null,
                'filiere_id' => $this->filiere->id,
                'status' => 'Success',
            ]);

            $this->closeModal();
            $this->resetPage();
            $this->showSuccessNotification('Unité d\'enseignement créée avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur lors de la création : ' . $e->getMessage();
        }
    }

    public function update()
    {
        $this->formErrors = [];

        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'specialite_id' => 'nullable|exists:specialites,id',
            ]);

            $this->selectedUe->update([
                'name' => $this->name,
                'specialite_id' => $this->specialite_id ?: null,
            ]);

            $this->closeModal();
            $this->resetPage();
            $this->showSuccessNotification('Unité d\'enseignement modifiée avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur lors de la mise à jour';
        }
    }

    public function delete()
    {
        try {
            $this->selectedUe->update(['status' => 'failed']);
            $this->closeModal();
            $this->resetPage();
            $this->showSuccessNotification('Unité d\'enseignement supprimée avec succès !');
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur lors de la suppression';
        }
    }

    public function toggleStatus($id)
    {
        try {
            $ue = Ue::findOrFail($id);
            $newStatus = $ue->status === 'Success' ? 'pending' : 'Success';
            $ue->update(['status' => $newStatus]);
            $this->showSuccessNotification('Statut mis à jour !');
        } catch (\Exception $e) {
            $this->showErrorNotification('Erreur lors du changement de statut');
        }
    }

    private function resetForm()
    {
        $this->name = '';
        $this->specialite_id = null;
        $this->formErrors = [];
    }

    private function showSuccessNotification($message)
    {
        $this->notificationMessage = $message;
        $this->notificationType = 'success';
        $this->showNotification = true;
    }

    private function showErrorNotification($message)
    {
        $this->notificationMessage = $message;
        $this->notificationType = 'error';
        $this->showNotification = true;
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            @if(!$hasFiliere)
                <!-- Message si aucune filière -->
                <div class="min-h-[60vh] flex items-center justify-center">
                    <div class="bg-white rounded-2xl shadow-xl p-10 max-w-lg text-center">
                        <div class="mx-auto w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Aucune filière attribuée</h2>
                        <p class="text-gray-600 mb-6">Vous devez avoir une filière attribuée pour gérer les unités d'enseignement.</p>
                        <a href="/filiere" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Retour
                        </a>
                    </div>
                </div>
            @else
                <!-- Header -->
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Unités d'Enseignement</h1>
                        <p class="text-gray-500">{{ $filiere->name }}</p>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center space-x-4">
                            <a href="/filiere" class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                            <div>
                                
                                
                            </div>
                        </div>
                        <button wire:click="openCreateModal" class="mt-4 md:mt-0 px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition duration-200 flex items-center shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Nouvelle UE
                        </button>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-indigo-500 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100">Total UE</p>
                                <p class="text-3xl font-bold">{{ $this->ues->total() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-500 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100">UE Actives</p>
                                <p class="text-3xl font-bold">{{ $this->ues->where('status', 'Success')->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-purple-500 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100">Spécialités</p>
                                <p class="text-3xl font-bold">{{ $this->specialites->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtre -->
                <div class="bg-white rounded-xl shadow p-4 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filtrer par spécialité</label>
                            <select wire:model.live="selectedSpecialiteFilter" class="w-full md:w-80 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="all">Toutes les spécialités</option>
                                @foreach($this->specialites as $spec)
                                    <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Liste des UE -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Liste des Unités d'Enseignement</h2>
                    </div>

                    @if($this->ues->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nom UE</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Spécialité</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cours</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($this->ues as $ue)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4">
                                                <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $ue->code }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="font-medium text-gray-800">{{ $ue->name }}</p>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($ue->specialite)
                                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">{{ $ue->specialite->name }}</span>
                                                @else
                                                    <span class="text-gray-400 text-sm">Générale</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">{{ $ue->cour->count() }} cours</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($ue->status === 'Success')
                                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Active</span>
                                                @else
                                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">{{ ucfirst($ue->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end space-x-2">
                                                    <button wire:click="openDetailsModal({{ $ue->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Détails">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="openEditModal({{ $ue->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Modifier">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="toggleStatus({{ $ue->id }})" class="p-2 {{ $ue->status === 'Success' ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition" title="{{ $ue->status === 'Success' ? 'Désactiver' : 'Activer' }}">
                                                        @if($ue->status === 'Success')
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                            </svg>
                                                        @else
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        @endif
                                                    </button>
                                                    <button wire:click="openDeleteModal({{ $ue->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Supprimer">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $this->ues->links() }}
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Aucune unité d'enseignement</h3>
                            <p class="text-gray-500 mb-6">Commencez par créer votre première UE.</p>
                            <button wire:click="openCreateModal" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                Créer une UE
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Modal Créer -->
                @if($showCreateModal)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Nouvelle Unité d'Enseignement</h2>
                            
                            @if(!empty($formErrors))
                                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                                    <strong>Erreurs :</strong>
                                    <ul class="list-disc ml-5 mt-2">
                                        @foreach($formErrors as $field => $errors)
                                            @foreach((array)$errors as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <form wire:submit="save">
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Nom de l'UE <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Ex: Algorithmique et Structures de Données">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Spécialité <span class="text-gray-400">(optionnel)</span></label>
                                        <select wire:model="specialite_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="">-- Générale / Non spécifique --</option>
                                            @foreach($this->specialites as $spec)
                                                <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-blue-700">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Un code unique sera généré automatiquement.
                                    </p>
                                </div>
                                
                                <div class="mt-8 flex justify-end space-x-4">
                                    <button type="button" wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                        Créer l'UE
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Modal Modifier -->
                @if($showEditModal && $selectedUe)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Modifier l'UE</h2>
                            
                            @if(!empty($formErrors))
                                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                                    <strong>Erreurs :</strong>
                                    <ul class="list-disc ml-5 mt-2">
                                        @foreach($formErrors as $field => $errors)
                                            @foreach((array)$errors as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <form wire:submit="update">
                                <div class="space-y-6">
                                    <div class="p-3 bg-gray-100 rounded-lg">
                                        <p class="text-sm text-gray-600">Code UE</p>
                                        <p class="font-mono font-semibold text-gray-800">{{ $selectedUe->code }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Nom de l'UE <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Spécialité</label>
                                        <select wire:model="specialite_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="">-- Générale / Non spécifique --</option>
                                            @foreach($this->specialites as $spec)
                                                <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mt-8 flex justify-end space-x-4">
                                    <button type="button" wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                        Mettre à jour
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Modal Détails -->
                @if($showDetailsModal && $selectedUe)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
                            <div class="text-center mb-6">
                                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span class="text-3xl font-bold text-indigo-600">{{ strtoupper(substr($selectedUe->name, 0, 2)) }}</span>
                                </div>
                                <h2 class="text-2xl font-semibold text-gray-800">{{ $selectedUe->name }}</h2>
                                <p class="text-gray-500 font-mono">{{ $selectedUe->code }}</p>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Filière</span>
                                    <span class="font-medium">{{ $selectedUe->filiere->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Spécialité</span>
                                    @if($selectedUe->specialite)
                                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">{{ $selectedUe->specialite->name }}</span>
                                    @else
                                        <span class="font-medium text-gray-400">Générale</span>
                                    @endif
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Nombre de cours</span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">{{ $selectedUe->cour->count() }} cours</span>
                                </div>
                                <div class="flex justify-between py-3">
                                    <span class="text-gray-500">Statut</span>
                                    @if($selectedUe->status === 'Success')
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Active</span>
                                    @else
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">{{ ucfirst($selectedUe->status) }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Liste des cours associés -->
                            @if($selectedUe->cour->count() > 0)
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Cours associés</h3>
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        @foreach($selectedUe->cour as $cours)
                                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                                <span class="text-sm text-gray-700">{{ $cours->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $cours->credit ?? 0 }} crédits</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <div class="mt-8 flex justify-end">
                                <button wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                    Fermer
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Modal Supprimer -->
                @if($showDeleteModal && $selectedUe)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 mb-2">Supprimer l'UE</h3>
                                <p class="text-gray-600 mb-2">Voulez-vous vraiment supprimer</p>
                                <p class="font-semibold text-gray-800 mb-6">{{ $selectedUe->name }} ?</p>
                                
                                @if($selectedUe->cour->count() > 0)
                                    <div class="mb-6 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <p class="text-sm text-yellow-700">
                                            ⚠️ Cette UE contient {{ $selectedUe->cour->count() }} cours associé(s).
                                        </p>
                                    </div>
                                @endif
                                
                                <div class="flex justify-center space-x-4">
                                    <button wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                        Annuler
                                    </button>
                                    <button wire:click="delete" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Notification -->
                @if($showNotification)
                    <div class="fixed top-6 right-6 z-50 max-w-md w-full" 
                         x-data="{ show: true }" 
                         x-init="setTimeout(() => { show = false; $wire.set('showNotification', false); }, 5000)"
                         x-show="show"
                         x-transition>
                        <div class="{{ $notificationType === 'success' ? 'bg-green-100 text-green-700 border-green-500' : 'bg-red-100 text-red-700 border-red-500' }} p-4 rounded-xl shadow-lg border-l-4">
                            <div class="flex items-start">
                                @if($notificationType === 'success')
                                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                                <span class="flex-1">{{ $notificationMessage }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endvolt
</x-layouts.app>