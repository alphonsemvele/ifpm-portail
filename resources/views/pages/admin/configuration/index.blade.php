<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Configuration;

name('admin.configurations');
middleware(['auth', 'verified']);

new class extends Component {
    public $configurations;
    public $showAddModal = false;
    public $showEditModal = false;
    public $showActivateModal = false;
    public $showDeactivateModal = false;
    public $showDetailsModal = false;
    public $showDeleteModal = false;
    public $configurationElement;
    public $showNotification = false;
    public $notificationMessage = '';
    public $notificationType = '';
    public array $formErrors = [];

    public $name = '';
    public $code = '';
    public $status = 'pending';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->configurations = Configuration::where('status', '!=', 'failed')->get();
            logger('Données des configurations chargées', [
                'configurations_count' => $this->configurations->count(),
            ]);
        } catch (\Exception $e) {
            logger('Erreur loadData: ' . $e->getMessage());
            $this->configurations = collect();
            $this->addError('general', 'Erreur lors du chargement des données');
        }
    }

    public function functionShowAddModal()
    {
        $this->reset(['name', 'code', 'status']);
        $this->formErrors = [];
        $this->status = 'pending';
        $this->showAddModal = true;
    }

    public function functionShowEditModal($id)
    {
        try {
            $this->configurationElement = Configuration::findOrFail($id);
            $this->name = $this->configurationElement->name;
            $this->code = $this->configurationElement->code;
            $this->status = $this->configurationElement->status;
            $this->formErrors = [];
            $this->showEditModal = true;
            logger('Modal édition ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openEditModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal d\'édition');
        }
    }

    public function functionShowActivateModal($id)
    {
        try {
            $this->configurationElement = Configuration::findOrFail($id);
            $this->showActivateModal = true;
            logger('Modal activation ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openActivateModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal');
        }
    }

    public function functionShowDeactivateModal($id)
    {
        try {
            $this->configurationElement = Configuration::findOrFail($id);
            $this->showDeactivateModal = true;
            logger('Modal désactivation ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openDeactivateModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal');
        }
    }

    public function functionShowDetailsModal($id)
    {
        try {
            $this->configurationElement = Configuration::where('status', '!=', 'failed')->findOrFail($id);
            $this->showDetailsModal = true;
            logger('Modal détails ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openDetailsModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal');
        }
    }

    public function functionShowDeleteModal($id)
    {
        try {
            $this->configurationElement = Configuration::findOrFail($id);
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
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:configurations,code',
                'status' => 'required|in:pending,Success,failed',
            ];

            $validated = $this->validate($rules);

            Configuration::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'status' => $validated['status'],
            ]);

            $this->reset(['name', 'code', 'status']);
            $this->showAddModal = false;
            $this->loadData();
            $this->showNotification = true;
            $this->notificationMessage = 'Configuration ajoutée avec succès !';
            $this->notificationType = 'success';
            $this->dispatch('auto-hide-notification');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
            logger('Validation error: ' . json_encode($e->errors()));
        } catch (\Exception $e) {
            logger('Erreur save: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la sauvegarde');
        }
    }

    public function update()
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:configurations,code,' . $this->configurationElement->id,
                'status' => 'required|in:pending,Success,failed',
            ];

            $validated = $this->validate($rules);

            $this->configurationElement->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'status' => $validated['status'],
            ]);

            $this->reset(['name', 'code', 'status']);
            $this->showEditModal = false;
            $this->configurationElement = null;
            $this->loadData();
            $this->showNotification = true;
            $this->notificationMessage = 'Configuration mise à jour avec succès !';
            $this->notificationType = 'success';
            $this->dispatch('auto-hide-notification');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
            logger('Validation error: ' . json_encode($e->errors()));
        } catch (\Exception $e) {
            logger('Erreur update: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la mise à jour');
        }
    }

    public function activateConfiguration($id)
    {
        try {
            Configuration::findOrFail($id)->update(['status' => 'Success']);
            $this->showActivateModal = false;
            $this->loadData();
            $this->showNotification = true;
            $this->notificationMessage = 'Configuration activée avec succès !';
            $this->notificationType = 'success';
            $this->dispatch('auto-hide-notification');
        } catch (\Exception $e) {
            logger('Erreur activate: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'activation');
        }
    }

    public function deactivateConfiguration($id)
    {
        try {
            Configuration::findOrFail($id)->update(['status' => 'pending']);
            $this->showDeactivateModal = false;
            $this->loadData();
            $this->showNotification = true;
            $this->notificationMessage = 'Configuration désactivée avec succès !';
            $this->notificationType = 'success';
            $this->dispatch('auto-hide-notification');
        } catch (\Exception $e) {
            logger('Erreur deactivate: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la désactivation');
        }
    }

    public function deleteConfiguration($id)
    {
        try {
            Configuration::findOrFail($id)->update(['status' => 'failed']);
            $this->showDeleteModal = false;
            $this->loadData();
            $this->showNotification = true;
            $this->notificationMessage = 'Configuration marquée comme échouée avec succès !';
            $this->notificationType = 'success';
            $this->dispatch('auto-hide-notification');
        } catch (\Exception $e) {
            logger('Erreur delete: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la suppression');
        }
    }

    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showActivateModal = false;
        $this->showDeactivateModal = false;
        $this->showDetailsModal = false;
        $this->showDeleteModal = false;
        $this->configurationElement = null;
        $this->reset(['name', 'code', 'status']);
        $this->formErrors = [];
    }

    public function hideNotification()
    {
        $this->showNotification = false;
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Gestion des Configurations</h1>
                    <p class="text-gray-500">Gérez les configurations de l'institution</p>
                </div>
                {{-- 
                 --}}
                <button wire:click="functionShowAddModal"
                    class="mt-6 bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    Ajouter une configuration
                </button>
            </div>

            <!-- Erreurs générales -->
            @error('general')
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border-l-4 border-red-500">
                    {{ $message }}
                </div>
            @enderror

            <!-- Notification -->
            @if ($showNotification)
                <div class="fixed top-6 right-6 z-50 max-w-sm w-full" x-data="{ show: true }"
                    x-init="setTimeout(() => { show = false; $wire.set('showNotification', false); }, 3000)"
                    x-show="show" x-transition>
                    <div
                        class="{{ $notificationType === 'success' ? 'bg-green-100 text-green-700 border-green-500' : 'bg-red-100 text-red-700 border-red-500' }} p-4 rounded-xl shadow-md border-l-4 animate-pulse">
                        {{ $notificationMessage }}
                    </div>
                </div>
            @endif

            <!-- Modal Ajouter Configuration -->
            @if ($showAddModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div
                        class="bg-white rounded-2xl p-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 ease-in-out scale-100 hover:scale-[1.02]">
                        <h2
                            class="text-3xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Ajouter une Configuration</h2>

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
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input type="text" wire:model="name" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Code</label>
                                    <input type="text" wire:model="code" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select wire:model="status" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                        <option value="pending">En attente</option>
                                        <option value="Success">Actif</option>
                                        <option value="failed">Échoué</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end space-x-4">
                                <button type="submit"
                                    class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                    Enregistrer
                                </button>
                                <button type="button" wire:click="closeModal"
                                    class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Modifier Configuration -->
            @if ($showEditModal && $configurationElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div
                        class="bg-white rounded-2xl p-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 ease-in-out scale-100 hover:scale-[1.02]">
                        <h2
                            class="text-3xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Modifier une Configuration</h2>

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
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input type="text" wire:model="name" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Code</label>
                                    <input type="text" wire:model="code" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select wire:model="status" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="Success" {{ $status === 'Success' ? 'selected' : '' }}>Actif</option>
                                        <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Échoué</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end space-x-4">
                                <button type="submit"
                                    class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                    Mettre à jour
                                </button>
                                <button type="button" wire:click="closeModal"
                                    class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Activer -->
            @if ($showActivateModal && $configurationElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div
                        class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Activer la configuration</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous activer {{ $configurationElement->name ?? 'N/A' }} ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="activateConfiguration({{ $configurationElement->id }})"
                                class="bg-green-600 text-white py-2 px-4 rounded-xl hover:bg-green-700 transition duration-300 shadow-md">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Désactiver -->
            @if ($showDeactivateModal && $configurationElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div
                        class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Désactiver la configuration</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous désactiver {{ $configurationElement->name ?? 'N/A' }} ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="deactivateConfiguration({{ $configurationElement->id }})"
                                class="bg-yellow-600 text-white py-2 px-4 rounded-xl hover:bg-yellow-700 transition duration-300 shadow-md">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Supprimer -->
            @if ($showDeleteModal && $configurationElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div
                        class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Supprimer la configuration</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous marquer {{ $configurationElement->name ?? 'N/A' }} comme échouée ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="deleteConfiguration({{ $configurationElement->id }})"
                                class="bg-red-600 text-white py-2 px-4 rounded-xl hover:bg-red-700 transition duration-300 shadow-md">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Détails -->
            @if ($showDetailsModal && $configurationElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div
                        class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Détails de la configuration</h3>
                        <div class="space-y-3">
                            <p><span class="font-medium text-gray-700">Nom :</span> {{ $configurationElement->name ?? 'N/A' }}</p>
                            <p><span class="font-medium text-gray-700">Code :</span> {{ $configurationElement->code ?? 'N/A' }}</p>
                            <p><span class="font-medium text-gray-700">Statut :</span>
                                {{ $configurationElement->status === 'Success' ? 'Actif' : ($configurationElement->status === 'pending' ? 'En attente' : 'Échoué') }}</p>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Liste des Configurations -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Configurations</h2>
                @if(count($configurations) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-2">
                            <thead>
                                <tr class="bg-gray-100 rounded-lg">
                                    <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Nom</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Code</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                    <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($configurations as $configuration)
                                    <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                        <td class="p-4 rounded-l-lg">{{ $configuration->name ?? 'N/A' }}</td>
                                        <td class="p-4">{{ $configuration->code ?? 'N/A' }}</td>
                                        <td class="p-4">
                                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                                {{ $configuration->status === 'Success' ? 'bg-green-100 text-green-800' : ($configuration->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ $configuration->status === 'Success' ? 'Actif' : ($configuration->status === 'pending' ? 'En attente' : 'Échoué') }}
                                            </span>
                                        </td>
                                        <td class="p-4 rounded-r-lg flex space-x-2">
                                            <button wire:click="functionShowDetailsModal({{ $configuration->id }})" class="bouton-icone text-sky-600 hover:bg-sky-50" title="Détails" aria-label="Détails"><x-icone-action nom="voir" /></button>
                                            @if ($configuration->status === 'pending')
                                                <button wire:click="functionShowActivateModal({{ $configuration->id }})" class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Activer" aria-label="Activer"><x-icone-action nom="activer" /></button>
                                            @else
                                                <button wire:click="functionShowDeactivateModal({{ $configuration->id }})" class="bouton-icone text-amber-600 hover:bg-amber-50" title="Désactiver" aria-label="Désactiver"><x-icone-action nom="desactiver" /></button>
                                            @endif
                                            <button wire:click="functionShowEditModal({{ $configuration->id }})" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Modifier" aria-label="Modifier"><x-icone-action nom="modifier" /></button>
                                            <button wire:click="functionShowDeleteModal({{ $configuration->id }})" class="bouton-icone text-red-600 hover:bg-red-50" title="Supprimer" aria-label="Supprimer"><x-icone-action nom="supprimer" /></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-gray-500">Aucune configuration trouvée.</p>
                @endif
            </div>
        </div>
    @endvolt
</x-layouts.app>
