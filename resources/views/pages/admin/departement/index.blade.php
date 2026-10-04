<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Departement;
use App\Models\Cycle;
use App\Models\User;
use Illuminate\Support\Str;

name('admin.departements');
middleware(['auth', 'verified']);

new class extends Component {
    public $departements = [];
    public $cycles = [];
    public $responsables = [];

    public $nom = '';
    public $description = '';
    public $cycle_id = null;
    public $responsable_id = null;
    public $status = 'Success';

    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showActivateModal = false;
    public $showDeactivateModal = false;
    public $showDetailsModal = false;

    public $departementElement;

    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = '';
    public array $formErrors = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->departements = Departement::with(['cycle', 'responsable'])
            ->where('status', '!=', 'failed')
            ->get();

        $this->cycles = Cycle::where('status', 'Success')
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->responsables = User::whereNotIn('role', ['student', 'etudiant', 'admin'])
            ->where('status', 'Success')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function functionShowCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
        $this->formErrors = [];
    }

    public function functionShowEditModal($id)
    {
        $this->departementElement = Departement::with(['cycle', 'responsable'])->findOrFail($id);

        $this->nom = $this->departementElement->nom;
        $this->description = $this->departementElement->description;
        $this->cycle_id = $this->departementElement->cycle_id;
        $this->responsable_id = $this->departementElement->responsable_id;
        $this->status = $this->departementElement->status;

        $this->showEditModal = true;
        $this->formErrors = [];
    }

    public function functionShowDetailsModal($id)
    {
        $this->departementElement = Departement::with(['cycle', 'responsable'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function functionShowDeleteModal($id)
    {
        $this->departementElement = Departement::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function functionShowActivateModal($id)
    {
        $this->departementElement = Departement::findOrFail($id);
        $this->showActivateModal = true;
    }

    public function functionShowDeactivateModal($id)
    {
        $this->departementElement = Departement::findOrFail($id);
        $this->showDeactivateModal = true;
    }

    public function save()
    {
        $rules = [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'cycle_id' => 'required|exists:cycles,id',
            'responsable_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,Success,completed',
        ];

        $validated = $this->validate($rules);

        $code = 'DEP-' . Str::upper(Str::random(6));

        Departement::create([
            'nom' => $this->nom,
            'code' => $code,
            'description' => $this->description,
            'cycle_id' => $this->cycle_id,
            'responsable_id' => $this->responsable_id,
            'status' => $this->status,
        ]);

        $this->resetForm();
        $this->showCreateModal = false;
        $this->loadData();
        $this->showSuccessNotification('Département créé avec succès !');
    }

    public function update()
    {
        $rules = [
            'nom' => 'required|string|max:255|unique:departements,nom,' . $this->departementElement->id,
            'description' => 'nullable|string|max:2000',
            'cycle_id' => 'required|exists:cycles,id',
            'responsable_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,Success,completed',
        ];

        $validated = $this->validate($rules);

        $this->departementElement->update([
            'nom' => $this->nom,
            'description' => $this->description,
            'cycle_id' => $this->cycle_id,
            'responsable_id' => $this->responsable_id,
            'status' => $this->status,
        ]);

        $this->resetForm();
        $this->showEditModal = false;
        $this->departementElement = null;
        $this->loadData();
        $this->showSuccessNotification('Département modifié avec succès !');
    }

    public function activateDepartement()
    {
        $this->departementElement->update(['status' => 'Success']);
        $this->showActivateModal = false;
        $this->loadData();
        $this->showSuccessNotification('Département activé !');
    }

    public function deactivateDepartement()
    {
        $this->departementElement->update(['status' => 'pending']);
        $this->showDeactivateModal = false;
        $this->loadData();
        $this->showSuccessNotification('Département désactivé !');
    }

    public function deleteDepartement()
    {
        $this->departementElement->update(['status' => 'failed']);
        $this->showDeleteModal = false;
        $this->loadData();
        $this->showSuccessNotification('Département supprimé !');
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showActivateModal = false;
        $this->showDeactivateModal = false;
        $this->showDetailsModal = false;
        $this->departementElement = null;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->nom = '';
        $this->description = '';
        $this->cycle_id = null;
        $this->responsable_id = null;
        $this->status = 'Success';
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
            <h1 class="text-3xl font-bold text-gray-800">Gestion des Départements</h1>
            <p class="text-gray-500">Créez et gérez les départements de votre établissement.</p>
        </div>
        <button wire:click="functionShowCreateModal"
            class="mt-6 bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition shadow-lg">
            + Nouveau Département
        </button>
    </div>

    <!-- Erreurs générales -->
    @if (!empty($formErrors['general']))
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border-l-4 border-red-500">
            {{ $formErrors['general'] }}
        </div>
    @endif

    <!-- Modal Créer / Modifier -->
    @if ($showCreateModal || $showEditModal)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                    {{ $showEditModal ? 'Modifier le département' : 'Ajouter un département' }}
                </h2>

                @if (!empty($formErrors))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($formErrors as $field => $messages)
                                @foreach ((array)$messages as $msg)
                                    <li>{{ ucfirst($field) }} : {{ $msg }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit="{{ $showEditModal ? 'update' : 'save' }}">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom du département *</label>
                            <input wire:model="nom" type="text" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea wire:model="description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cycle *</label>
                            <select wire:model="cycle_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Sélectionner un cycle</option>
                                @foreach ($cycles as $cycle)
                                    <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Responsable *</label>
                            <select wire:model="responsable_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Sélectionner un responsable</option>
                                @foreach ($responsables as $resp)
                                    <option value="{{ $resp->id }}">{{ $resp->name }} ({{ $resp->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Statut</label>
                            <select wire:model="status" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="pending">En attente</option>
                                <option value="Success">Actif</option>
                                <option value="completed">Terminé</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            {{ $showEditModal ? 'Modifier' : 'Créer' }}
                        </button>
                        <button wire:click="closeModal" type="button" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Détails -->
    @if ($showDetailsModal && $departementElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">{{ $departementElement->nom }}</h2>

                <div class="space-y-4">
                    <p><strong>Code :</strong> {{ $departementElement->code }}</p>
                    <p><strong>Cycle :</strong> {{ $departementElement->cycle?->name ?? 'Non rattaché' }}</p>
                    <p><strong>Description :</strong> {{ $departementElement->description ?? '—' }}</p>
                    <p><strong>Responsable :</strong> {{ $departementElement->responsable?->name ?? 'Aucun' }}</p>
                    <p><strong>Statut :</strong> 
                        @if ($departementElement->status === 'Success')
                            <span class="text-green-600">Actif</span>
                        @elseif ($departementElement->status === 'pending')
                            <span class="text-yellow-600">En attente</span>
                        @else
                            <span class="text-gray-600">Inactif</span>
                        @endif
                    </p>
                </div>

                <div class="mt-8 flex justify-end">
                    <button wire:click="closeModal" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Confirmation Activer/Désactiver/Supprimer -->
    @if ($showActivateModal && $departementElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold mb-4">Activer le département</h3>
                <p class="mb-6">Confirmez l'activation de <strong>{{ $departementElement->nom }}</strong> ?</p>
                <div class="flex justify-end gap-4">
                    <button wire:click="activateDepartement" class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Oui</button>
                    <button wire:click="closeModal" class="px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Non</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showDeactivateModal && $departementElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold mb-4">Désactiver le département</h3>
                <p class="mb-6">Confirmez la désactivation de <strong>{{ $departementElement->nom }}</strong> ?</p>
                <div class="flex justify-end gap-4">
                    <button wire:click="deactivateDepartement" class="px-5 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">Oui</button>
                    <button wire:click="closeModal" class="px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Non</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showDeleteModal && $departementElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-red-600 mb-4">Supprimer le département</h3>
                <p class="mb-6">Êtes-vous sûr de vouloir supprimer <strong>{{ $departementElement->nom }}</strong> ?</p>
                <div class="flex justify-end gap-4">
                    <button wire:click="deleteDepartement" class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Oui</button>
                    <button wire:click="closeModal" class="px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Liste des départements -->
    <div class="bg-white rounded-2xl shadow-lg p-8 mt-10">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Départements</h2>

        @if (count($departements) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr class="bg-gray-100 rounded-lg">
                            <th class="p-4 text-sm font-medium text-gray-600">Nom</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Code</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Cycle</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Responsable</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departements as $dep)
                            <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                <td class="p-4 font-medium">{{ $dep->nom }}</td>
                                <td class="p-4">{{ $dep->code }}</td>
                                <td class="p-4">{{ $dep->cycle?->name ?? '—' }}</td>
                                <td class="p-4">{{ $dep->responsable?->name ?? '—' }}</td>
                                <td class="p-4">
                                    @if ($dep->status === 'pending')
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                    @elseif ($dep->status === 'Success')
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Actif</span>
                                    @else
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactif</span>
                                    @endif
                                </td>
                                <td class="p-4 flex space-x-2">
                                    <button wire:click="functionShowDetailsModal({{ $dep->id }})" class="bouton-icone text-sky-600 hover:bg-sky-50" title="Détails" aria-label="Détails"><x-icone-action nom="voir" /></button>

                                    @if ($dep->status === 'pending')
                                        <button wire:click="functionShowActivateModal({{ $dep->id }})" class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Activer" aria-label="Activer"><x-icone-action nom="activer" /></button>
                                    @elseif ($dep->status === 'Success')
                                        <button wire:click="functionShowDeactivateModal({{ $dep->id }})" class="bouton-icone text-amber-600 hover:bg-amber-50" title="Désactiver" aria-label="Désactiver"><x-icone-action nom="desactiver" /></button>
                                    @endif

                                    <button wire:click="functionShowEditModal({{ $dep->id }})" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Modifier" aria-label="Modifier"><x-icone-action nom="modifier" /></button>

                                    <button wire:click="functionShowDeleteModal({{ $dep->id }})" class="bouton-icone text-red-600 hover:bg-red-50" title="Supprimer" aria-label="Supprimer"><x-icone-action nom="supprimer" /></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-gray-500 py-8">Aucun département enregistré pour le moment.</p>
        @endif
    </div>

    <!-- Notification -->
    @if ($showNotification)
        <div class="fixed top-6 right-6 z-50 max-w-sm w-full" x-data="{ show: true }" x-show="show" x-transition
             x-init="setTimeout(() => { show = false; $wire.set('showNotification', false) }, 4000)">
            <div class="p-4 rounded-xl shadow-lg border-l-4 bg-green-50 border-green-500 text-green-800">
                {{ $notificationMessage }}
            </div>
        </div>
    @endif
</div>
@endvolt
</x-layouts.app>