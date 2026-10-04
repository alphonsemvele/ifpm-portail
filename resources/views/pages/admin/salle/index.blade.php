<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Salle;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\Cycle;

name('admin.salles');
middleware(['auth', 'verified']);

new class extends Component {
    public $salles;
    public $filieres;
    public $specialites;
    public $cycles;
    public $showAddModal = false;
    public $showEditModal = false;
    public $showActivateModal = false;
    public $showDeactivateModal = false;
    public $showDetailsModal = false;
    public $showDeleteModal = false;
    public $salleElement;
    public $showNotification = false;
    public $notificationMessage = '';
    public $notificationType = '';
    public $name = '';
    public $capacite = '';
    public $filiere_id = '';
    public $specialite_id = '';
    public $cycle_id = '';

    public function mount()
    {
        $this->loadData();
    }

    private function loadData()
    {
        $this->salles = Salle::with(['filiere', 'specialite', 'cycle'])
            ->where('status', '!=', 'failed')
            ->get();
        $this->filieres = Filiere::all();
        $this->specialites = Specialite::all();
        $this->cycles = Cycle::all();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'capacite' => 'required|integer|min:1',
            'filiere_id' => 'nullable|exists:filieres,id',
            'specialite_id' => 'nullable|exists:specialites,id',
            'cycle_id' => 'nullable|exists:cycles,id',
        ]);

        Salle::create([
            'name' => $this->name,
            'capacite' => $this->capacite,
            'filiere_id' => $this->filiere_id,
            'specialite_id' => $this->specialite_id,
            'cycle_id' => $this->cycle_id,
            'status' => 'pending',
        ]);

        $this->resetForm();
        $this->showAddModal = false;
        $this->loadData();
        $this->showNotification('Salle ajoutée avec succès !', 'success');
    }

    public function functionShowAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function functionShowEditModal($id)
    {
        try {
            $this->salleElement = Salle::with(['filiere', 'specialite', 'cycle'])->findOrFail($id);
            $this->name = $this->salleElement->name;
            $this->capacite = $this->salleElement->capacite;
            $this->filiere_id = $this->salleElement->filiere_id;
            $this->specialite_id = $this->salleElement->specialite_id;
            $this->cycle_id = $this->salleElement->cycle_id;
            $this->showEditModal = true;
        } catch (\Exception $e) {
            $this->showNotification('Erreur lors de l\'ouverture du modal d\'édition : ' . $e->getMessage(), 'error');
        }
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'capacite' => 'required|integer|min:1',
            'filiere_id' => 'nullable|exists:filieres,id',
            'specialite_id' => 'nullable|exists:specialites,id',
            'cycle_id' => 'nullable|exists:cycles,id',
        ]);

        $this->salleElement->update([
            'name' => $this->name,
            'capacite' => $this->capacite,
            'filiere_id' => $this->filiere_id,
            'specialite_id' => $this->specialite_id,
            'cycle_id' => $this->cycle_id,
        ]);

        $this->resetForm();
        $this->showEditModal = false;
        $this->loadData();
        $this->showNotification('Salle mise à jour avec succès !', 'success');
    }

    public function functionShowActivateModal($id)
    {
        try {
            $this->salleElement = Salle::findOrFail($id);
            $this->showActivateModal = true;
            $this->dispatch('activate-modal-opened', id: $id);
        } catch (\Exception $e) {
            $this->showNotification('Erreur lors de l\'ouverture du modal d\'activation : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowDeactivateModal($id)
    {
        try {
            \Log::info('functionShowDeactivateModal called with ID: ' . $id); // Journalisation pour débogage
            $this->salleElement = Salle::findOrFail($id);
            $this->showDeactivateModal = true;
            $this->dispatch('deactivate-modal-opened', id: $id); // Événement pour débogage
        } catch (\Exception $e) {
            $this->showNotification('Erreur lors de l\'ouverture du modal de désactivation : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowDetailsModal($id)
    {
        try {
            $this->salleElement = Salle::with(['filiere', 'specialite', 'cycle'])->findOrFail($id);
            $this->showDetailsModal = true;
        } catch (\Exception $e) {
            $this->showNotification('Erreur lors de l\'ouverture du modal de détails : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowDeleteModal($id)
    {
        try {
            $this->salleElement = Salle::findOrFail($id);
            $this->showDeleteModal = true;
        } catch (\Exception $e) {
            $this->showNotification('Erreur lors de l\'ouverture du modal de suppression : ' . $e->getMessage(), 'error');
        }
    }

    public function activateSalle($id)
    {
        try {
            Salle::findOrFail($id)->update(['status' => 'Success']);
            $this->showActivateModal = false;
            $this->salleElement = null;
            $this->loadData();
            $this->showNotification('Salle activée avec succès !', 'success');
            $this->dispatch('salle-activated', id: $id);
        } catch (\Exception $e) {
            $this->showNotification('Erreur lors de l\'activation de la salle : ' . $e->getMessage(), 'error');
        }
    }

    public function deactivateSalle($id)
    {
        try {
            \Log::info('deactivateSalle called with ID: ' . $id); // Journalisation pour débogage
            $salle = Salle::findOrFail($id);
            $salle->update(['status' => 'pending']);
            $this->showDeactivateModal = false;
            $this->salleElement = null; // Réinitialiser pour éviter les conflits
            $this->loadData();
            $this->showNotification('Salle désactivée avec succès !', 'success');
            $this->dispatch('salle-deactivated', id: $id); // Événement pour débogage
        } catch (\Exception $e) {
            $this->showNotification('Erreur lors de la désactivation de la salle : ' . $e->getMessage(), 'error');
        }
    }

    public function deleteSalle($id)
    {
        try {
            Salle::findOrFail($id)->update(['status' => 'suspended']);
            $this->showDeleteModal = false;
            $this->salleElement = null;
            $this->loadData();
            $this->showNotification('Salle marquée comme suspendue avec succès !', 'success');
        } catch (\Exception $e) {
            $this->showNotification('Erreur lors de la suppression de la salle : ' . $e->getMessage(), 'error');
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
        $this->resetForm();
        $this->salleElement = null;
    }

    private function resetForm()
    {
        $this->reset(['name', 'capacite', 'filiere_id', 'specialite_id', 'cycle_id']);
    }

    private function showNotification($message, $type)
    {
        $this->notificationMessage = $message;
        $this->notificationType = $type;
        $this->showNotification = true;
        $this->dispatch('auto-hide-notification');
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Gestion des Salles</h1>
                    <p class="text-gray-500">Créez, modifiez, supprimez des salles et associez-les à une filière, une spécialité et un cycle</p>
                </div>
                <button wire:click="functionShowAddModal"
                    class="mt-6 bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter une salle
                </button>
            </div>
            <!-- Notification -->
            @if ($showNotification)
                <div class="fixed top-6 right-6 z-50 max-w-sm w-full" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)"
                    x-show="show" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4">
                    <div class="bg-green-100 text-green-700 p-4 rounded-xl shadow-md border-l-4 border-green-500 animate-pulse">
                        {{ $notificationMessage }}
                    </div>
                </div>
            @endif
            <!-- Modal Ajouter/Modifier Salle -->
            @if ($showAddModal || $showEditModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 ease-in-out scale-100 hover:scale-[1.02]">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            {{ $showAddModal ? 'Ajouter une Salle' : 'Modifier une Salle' }}
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom de la Salle</label>
                                <input type="text" wire:model="name"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                @error('name')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Capacité</label>
                                <input type="number" wire:model="capacite"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                @error('capacite')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Filière</label>
                                <select wire:model="filiere_id"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    <option value="">Aucune</option>
                                    @foreach ($filieres as $filiere)
                                        <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                                    @endforeach
                                </select>
                                @error('filiere_id')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Spécialité</label>
                                <select wire:model="specialite_id"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    <option value="">Aucune</option>
                                    @foreach ($specialites as $specialite)
                                        <option value="{{ $specialite->id }}">{{ $specialite->name }}</option>
                                    @endforeach
                                </select>
                                @error('specialite_id')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cycle</label>
                                <select wire:model="cycle_id"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    <option value="">Aucun</option>
                                    @foreach ($cycles as $cycle)
                                        <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                                    @endforeach
                                </select>
                                @error('cycle_id')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end space-x-4">
                            <button wire:click="{{ $showAddModal ? 'save' : 'update' }}"
                                class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                {{ $showAddModal ? 'Enregistrer' : 'Mettre à jour' }}
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif
            <!-- Liste des Salles -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Salles</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Nom</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Capacité</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Filière</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Spécialité</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Cycle</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($salles as $salle)
                                <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <td class="p-4 rounded-l-lg">{{ $salle->name }}</td>
                                    <td class="p-4">{{ $salle->capacite }}</td>
                                    <td class="p-4">{{ $salle->filiere->name ?? 'Aucune' }}</td>
                                    <td class="p-4">{{ $salle->specialite->name ?? 'Aucune' }}</td>
                                    <td class="p-4">{{ $salle->cycle->name ?? 'Aucun' }}</td>
                                    <td class="p-4">
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                            {{ $salle->status === 'Success' ? 'bg-green-100 text-green-800' : ($salle->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($salle->status) }}
                                        </span>
                                    </td>
                                    <td class="p-4 rounded-r-lg flex space-x-2">
                                        <button wire:click="functionShowDetailsModal({{ $salle->id }})"
                                            class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Détails
                                        </button>
                                        @if ($salle->status === 'pending')
                                            <button wire:click="functionShowActivateModal({{ $salle->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Activer
                                            </button>
                                        @else
                                            <button wire:click="functionShowDeactivateModal({{ $salle->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition duration-200"
                                                x-on:click="console.log('Bouton Désactiver cliqué pour salle ID: {{ $salle->id }}')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Désactiver
                                            </button>
                                        @endif
                                        <button wire:click="functionShowEditModal({{ $salle->id }})"
                                            class="inline-flex items-center px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Modifier
                                        </button>
                                        <button wire:click="functionShowDeleteModal({{ $salle->id }})"
                                            class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Modal Activer -->
            @if ($showActivateModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Activer la salle</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous activer {{ $salleElement->name ?? 'N/A' }} ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="activateSalle({{ $salleElement->id ?? 'N/A' }})"
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
            @if ($showDeactivateModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Désactiver la salle</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous désactiver {{ $salleElement->name ?? 'N/A' }} ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="deactivateSalle({{ $salleElement->id ?? 'N/A' }})"
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
            @if ($showDeleteModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Supprimer la salle</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous marquer {{ $salleElement->name ?? 'N/A' }} comme suspendue ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="deleteSalle({{ $salleElement->id ?? 'N/A' }})"
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
            @if ($showDetailsModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Détails de la salle</h3>
                        <div class="space-y-3">
                            <p><span class="font-medium text-gray-700">Nom :</span> {{ $salleElement->name ?? 'N/A' }}</p>
                            <p><span class="font-medium text-gray-700">Capacité :</span> {{ $salleElement->capacite ?? 'N/A' }}</p>
                            <p><span class="font-medium text-gray-700">Filière :</span> {{ $salleElement->filiere->name ?? 'Aucune' }}</p>
                            <p><span class="font-medium text-gray-700">Spécialité :</span> {{ $salleElement->specialite->name ?? 'Aucune' }}</p>
                            <p><span class="font-medium text-gray-700">Cycle :</span> {{ $salleElement->cycle->name ?? 'Aucun' }}</p>
                            <p><span class="font-medium text-gray-700">Statut :</span> {{ ucfirst($salleElement->status ?? 'N/A') }}</p>
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
        </div>
    @endvolt
</x-layouts.app>
