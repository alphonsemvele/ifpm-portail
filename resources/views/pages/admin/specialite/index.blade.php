<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Specialite;
use App\Models\Filiere;
use App\Models\User;

name('admin.specialites');
middleware(['auth', 'verified']);

new class extends Component {
    public $specialites = [];
    public $filieres = [];
    public $responsables = [];

    public bool $showAddModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public bool $showActivateModal = false;
    public bool $showDeactivateModal = false;
    public $specialiteElement = null;
    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = '';
    public array $formErrors = [];

    // Propriétés du formulaire
    public string $name = '';
    public $filiere_id = null;
    public $responsable_id = null;
    public string $status = '';
    public $price = 0;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->specialites = Specialite::with(['filiere', 'filiere.cycle', 'responsable'])
                ->where('status', '!=', 'failed')
                ->get();
            $this->filieres = Filiere::with('cycle')->where('status', 'Success')->get();

            $this->responsables = User::whereNotIn('role', ['student', 'etudiant', 'admin'])
                ->where('status', 'Success')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            logger('Données chargées', [
                'specialites_count' => $this->specialites->count(),
                'filieres_count' => $this->filieres->count(),
                'responsables_count' => $this->responsables->count(),
            ]);
        } catch (\Exception $e) {
            logger('Erreur loadData: ' . $e->getMessage());
            $this->specialites = collect();
            $this->filieres = collect();
            $this->responsables = collect();
        }
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
        $this->formErrors = [];
        logger('Modal ajouter ouvert');
    }

    public function openEditModal($id)
    {
        try {
            $this->specialiteElement = Specialite::with(['filiere', 'filiere.cycle', 'responsable'])->findOrFail($id);
            $this->name = $this->specialiteElement->name ?? '';
            $this->filiere_id = $this->specialiteElement->filiere_id;
            $this->responsable_id = $this->specialiteElement->responsable_id;
            $this->status = $this->specialiteElement->status ?? '';
            $this->price = $this->specialiteElement->price ?? 0;
            $this->showEditModal = true;
            $this->formErrors = [];
            logger('Modal édition ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openEditModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal d\'édition');
        }
    }

    public function openActivateModal($id)
    {
        try {
            $this->specialiteElement = Specialite::findOrFail($id);
            $this->showActivateModal = true;
            logger('Modal activation ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openActivateModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal');
        }
    }

    public function openDeactivateModal($id)
    {
        try {
            $this->specialiteElement = Specialite::findOrFail($id);
            $this->showDeactivateModal = true;
            logger('Modal désactivation ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openDeactivateModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal');
        }
    }

    public function openDeleteModal($id)
    {
        try {
            $this->specialiteElement = Specialite::findOrFail($id);
            $this->showDeleteModal = true;
            logger('Modal suppression ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openDeleteModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal');
        }
    }

    public function save()
    {
        try {
            $rules = [
                'name'           => 'required|string|max:255',
                'filiere_id'     => 'required|exists:filieres,id',
                'responsable_id' => 'nullable|exists:users,id',
                'price'          => 'nullable|numeric|min:0',
                'status'         => 'nullable|in:Success,pending',
            ];

            $validated = $this->validate($rules);

            $filiere = Filiere::find($this->filiere_id);
            $cycle_id = $filiere ? $filiere->cycle_id : null;

            Specialite::create([
                'name'           => $this->name,
                'filiere_id'     => $this->filiere_id,
                'cycle_id'       => $cycle_id,
                'responsable_id' => $this->responsable_id ?: null,
                'price'          => $this->price,
                'status'         => $this->status ?: 'Success',
            ]);

            $this->resetForm();
            $this->showAddModal = false;
            $this->loadData();
            $this->showSuccessNotification('Spécialité ajoutée avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            logger('Erreur save: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la sauvegarde');
        }
    }

    public function update()
    {
        try {
            $rules = [
                'name'           => 'required|string|max:255',
                'filiere_id'     => 'required|exists:filieres,id',
                'responsable_id' => 'nullable|exists:users,id',
                'price'          => 'required|numeric|min:0',
                'status'         => 'nullable|in:Success,pending',
            ];

            $this->validate($rules);

            $filiere = Filiere::find($this->filiere_id);
            $cycle_id = $filiere ? $filiere->cycle_id : null;

            $this->specialiteElement->update([
                'name'           => $this->name,
                'filiere_id'     => $this->filiere_id,
                'cycle_id'       => $cycle_id,
                'responsable_id' => $this->responsable_id ?: null,
                'price'          => $this->price,
                'status'         => $this->status ?: 'Success',
            ]);

            $this->resetForm();
            $this->showEditModal = false;
            $this->specialiteElement = null;
            $this->loadData();
            $this->showSuccessNotification('Spécialité mise à jour avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            logger('Erreur update: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la mise à jour');
        }
    }

    public function activate($id)
    {
        try {
            $specialite = Specialite::findOrFail($id);
            $specialite->update(['status' => 'Success']);
            $this->closeModal();
            $this->loadData();
            $this->showSuccessNotification('Spécialité activée avec succès !');
        } catch (\Exception $e) {
            logger('Erreur activate: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'activation');
        }
    }

    public function deactivate($id)
    {
        try {
            $specialite = Specialite::findOrFail($id);
            $specialite->update(['status' => 'pending']);
            $this->closeModal();
            $this->loadData();
            $this->showSuccessNotification('Spécialité désactivée avec succès !');
        } catch (\Exception $e) {
            logger('Erreur deactivate: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la désactivation');
        }
    }

    public function delete($id)
    {
        try {
            $specialite = Specialite::findOrFail($id);
            $specialite->update(['status' => 'failed']);
            $this->closeModal();
            $this->loadData();
            $this->showSuccessNotification('Spécialité supprimée avec succès !');
        } catch (\Exception $e) {
            logger('Erreur delete: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la suppression');
        }
    }

    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showActivateModal = false;
        $this->showDeactivateModal = false;
        $this->specialiteElement = null;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->filiere_id = null;
        $this->responsable_id = null;
        $this->status = '';
        $this->price = 0;
        $this->formErrors = [];
    }

    private function showSuccessNotification($message)
    {
        $this->notificationMessage = $message;
        $this->notificationType = 'success';
        $this->showNotification = true;
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Gestion des Spécialités</h1>
                    <p class="text-gray-500">Créez, modifiez, activez, désactivez ou supprimez des spécialités</p>
                </div>
            </div>

            <!-- Bouton Ajouter -->
            <div class="mb-8 text-center">
                <button wire:click="openAddModal" type="button"
                    class="bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-lg">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter une spécialité
                </button>
            </div>

            <!-- Erreurs générales -->
            @error('general')
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border-l-4 border-red-500">
                    {{ $message }}
                </div>
            @enderror

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Total Spécialités</h3>
                    <p class="text-3xl font-bold text-indigo-600">{{ count($specialites) }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Spécialités Actives</h3>
                    <p class="text-3xl font-bold text-green-600">
                        {{ collect($specialites)->where('status', 'Success')->count() }}
                    </p>
                </div>
            </div>

            <!-- Liste des Spécialités -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Spécialités</h2>

                @if(count($specialites) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-2">
                            <thead>
                                <tr class="bg-gray-100 rounded-lg">
                                    <th class="p-4 text-sm font-medium text-gray-600">Nom</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Filière</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Cycle</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Responsable</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Prix</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($specialites as $specialite)
                                    <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                        <td class="p-4">{{ $specialite->name ?? 'Non défini' }}</td>
                                        <td class="p-4">{{ $specialite->filiere->name ?? 'Non défini' }}</td>
                                        <td class="p-4">{{ $specialite->filiere->cycle->name ?? 'N/A' }}</td>
                                        <td class="p-4">{{ $specialite->responsable->name ?? 'Non assigné' }}</td>
                                        <td class="p-4">{{ number_format($specialite->price ?? 0, 0, ',', ' ') }} FCFA</td>
                                        <td class="p-4">
                                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                                {{ $specialite->status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $specialite->status === 'Success' ? 'Actif' : 'En attente' }}
                                            </span>
                                        </td>
                                        <td class="p-4 flex space-x-2">
                                            @if ($specialite->status === 'pending')
                                                <button wire:click="openActivateModal({{ $specialite->id }})" type="button" class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Activer" aria-label="Activer"><x-icone-action nom="activer" /></button>
                                            @else
                                                <button wire:click="openDeactivateModal({{ $specialite->id }})" type="button" class="bouton-icone text-amber-600 hover:bg-amber-50" title="Désactiver" aria-label="Désactiver"><x-icone-action nom="desactiver" /></button>
                                            @endif
                                            <button wire:click="openEditModal({{ $specialite->id }})" type="button" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Modifier" aria-label="Modifier"><x-icone-action nom="modifier" /></button>
                                            <button wire:click="openDeleteModal({{ $specialite->id }})" type="button" class="bouton-icone text-red-600 hover:bg-red-50" title="Supprimer" aria-label="Supprimer"><x-icone-action nom="supprimer" /></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-gray-500">Aucune spécialité trouvée.</p>
                @endif
            </div>

            <!-- Modal Ajouter -->
            @if ($showAddModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6">Ajouter une Spécialité</h2>

                        @if (!empty($formErrors))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                                <strong>Erreurs :</strong>
                                <ul class="list-disc ml-5 mt-2">
                                    @foreach ($formErrors as $field => $errors)
                                        @foreach ((array)$errors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form wire:submit="save">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom de la Spécialité</label>
                                    <input type="text" wire:model="name" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Filière</label>
                                    <select wire:model="filiere_id" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Sélectionner une filière</option>
                                        @if(count($filieres) > 0)
                                            @foreach ($filieres as $filiere)
                                                <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>Aucune filière disponible, veuillez en créer une</option>
                                        @endif
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Responsable</label>
                                    <select wire:model="responsable_id"
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                        <option value="">— Aucun responsable assigné —</option>
                                        @foreach ($responsables as $resp)
                                            <option value="{{ $resp->id }}">{{ $resp->name }} ({{ $resp->email }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Prix (FCFA)</label>
                                    <input type="number" wire:model="price"  min="0"
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select wire:model="status"
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Sélectionner un statut</option>
                                        <option value="Success">Actif</option>
                                        <option value="pending">En attente</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end space-x-4">
                                <button type="submit"
                                    class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700">
                                    Enregistrer
                                </button>
                                <button type="button" wire:click="closeModal"
                                    class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Modifier -->
            @if ($showEditModal && $specialiteElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6">Modifier la Spécialité</h2>

                        @if (!empty($formErrors))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                                <strong>Erreurs :</strong>
                                <ul class="list-disc ml-5 mt-2">
                                    @foreach ($formErrors as $field => $errors)
                                        @foreach ((array)$errors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form wire:submit="update">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom de la Spécialité</label>
                                    <input type="text" wire:model="name" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Filière</label>
                                    <select wire:model="filiere_id" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Sélectionner une filière</option>
                                        @if(count($filieres) > 0)
                                            @foreach ($filieres as $filiere)
                                                <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>Aucune filière disponible, veuillez en créer une</option>
                                        @endif
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Responsable</label>
                                    <select wire:model="responsable_id"
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                        <option value="">— Aucun responsable assigné —</option>
                                        @foreach ($responsables as $resp)
                                            <option value="{{ $resp->id }}">{{ $resp->name }} ({{ $resp->email }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Prix (FCFA)</label>
                                    <input type="number" wire:model="price" required min="0"
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select wire:model="status"
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Sélectionner un statut</option>
                                        <option value="Success">Actif</option>
                                        <option value="pending">En attente</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end space-x-4">
                                <button type="submit"
                                    class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700">
                                    Mettre à jour
                                </button>
                                <button type="button" wire:click="closeModal"
                                    class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Activer -->
            @if ($showActivateModal && $specialiteElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Activer la spécialité</h3>
                        <p class="mb-6 text-gray-600">Voulez-vous activer la spécialité "{{ $specialiteElement->name }}" ?</p>
                        <div class="flex justify-end space-x-4">
                            <button type="button" wire:click="activate({{ $specialiteElement->id }})"
                                class="bg-green-600 text-white py-2 px-4 rounded-xl hover:bg-green-700">
                                Oui
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Désactiver -->
            @if ($showDeactivateModal && $specialiteElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Désactiver la spécialité</h3>
                        <p class="mb-6 text-gray-600">Voulez-vous désactiver la spécialité "{{ $specialiteElement->name }}" ?</p>
                        <div class="flex justify-end space-x-4">
                            <button type="button" wire:click="deactivate({{ $specialiteElement->id }})"
                                class="bg-yellow-600 text-white py-2 px-4 rounded-xl hover:bg-yellow-700">
                                Oui
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Supprimer -->
            @if ($showDeleteModal && $specialiteElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Supprimer la spécialité</h3>
                        <p class="mb-6 text-gray-600">Voulez-vous supprimer la spécialité "{{ $specialiteElement->name }}" ? Cette action est irréversible.</p>
                        <div class="flex justify-end space-x-4">
                            <button type="button" wire:click="delete({{ $specialiteElement->id }})"
                                class="bg-red-600 text-white py-2 px-4 rounded-xl hover:bg-red-700">
                                Oui
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notification -->
            @if ($showNotification)
                <div class="fixed top-6 right-6 z-50 max-w-sm w-full"
                    x-data="{ show: true }"
                    x-init="setTimeout(() => { show = false; $wire.set('showNotification', false); }, 3000)"
                    x-show="show">
                    <div class="{{ $notificationType === 'success' ? 'bg-green-100 text-green-700 border-green-500' : 'bg-red-100 text-red-700 border-red-500' }} p-4 rounded-xl shadow-md border-l-4">
                        {{ $notificationMessage }}
                    </div>
                </div>
            @endif
        </div>
    @endvolt
</x-layouts.app>