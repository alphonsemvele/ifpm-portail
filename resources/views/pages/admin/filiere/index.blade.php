<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Filiere;
use App\Models\Departement;
use App\Models\Cycle;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

name('admin.filieres');
middleware(['auth', 'verified']);

new class extends Component {
    public $filieres = [];
    public $cycles = [];
    public $departements = [];
    public $filteredDepartements = [];
    public $specialitesCount = [];
    public $name = '';
    public $code = '';          // ← nouveau
    public $description = '';
    public $cycle_id = null;
    public $departement_id = null;
    public $status = 'Success';

    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showActivateModal = false;
    public $showDeactivateModal = false;
    public $showDetailsModal = false;

    public $filiereElement;
    public $editingId = null;   // ID scalaire — fiable dans Livewire

    public bool   $showNotification    = false;
    public string $notificationMessage = '';
    public string $notificationType    = '';
    public array  $formErrors          = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->filieres = Filiere::with(['departement.cycle', 'specialites'])
            ->where('status', '!=', 'failed')
            ->get();

        $this->cycles = Cycle::where('status', 'Success')
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->departements = Departement::where('status', 'Success')
            ->orderBy('nom')
            ->get(['id', 'nom', 'cycle_id']);

        foreach ($this->filieres as $f) {
            $this->specialitesCount[$f->id] = $f->specialites->count();
        }
    }

    public function updatedCycleId($value)
    {
        $this->departement_id = null;
        $this->filteredDepartements = $this->departements
            ->where('cycle_id', $value)
            ->values();
    }

    public function functionShowCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
        $this->formErrors = [];
    }

    public function functionShowEditModal($id)
    {
        $this->editingId      = $id;   // ← stocker l'ID séparément
        $this->filiereElement = Filiere::with('departement.cycle')->findOrFail($id);

        $this->name        = $this->filiereElement->name;
        $this->code        = $this->filiereElement->code;      // ← pré-rempli
        $this->description = $this->filiereElement->description;
        $this->departement_id = $this->filiereElement->departement_id;
        $this->status      = $this->filiereElement->status;

        if ($this->filiereElement->departement) {
            $this->cycle_id = $this->filiereElement->departement->cycle_id;
            $this->filteredDepartements = $this->departements
                ->where('cycle_id', $this->cycle_id)
                ->values();
        }

        $this->showEditModal = true;
        $this->formErrors = [];
    }

    public function functionShowDetailsModal($id)
    {
        $this->filiereElement = Filiere::with(['departement.cycle', 'specialites'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function functionShowDeleteModal($id)
    {
        $this->filiereElement = Filiere::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function functionShowActivateModal($id)
    {
        $this->filiereElement = Filiere::findOrFail($id);
        $this->showActivateModal = true;
    }

    public function functionShowDeactivateModal($id)
    {
        $this->filiereElement = Filiere::findOrFail($id);
        $this->showDeactivateModal = true;
    }

    public function save()
    {
        $this->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50|unique:filieres,code',
            'description'   => 'nullable|string|max:2000',
            'cycle_id'      => 'required|exists:cycles,id',
            'departement_id'=> 'required|exists:departements,id',
            'status'        => 'required|in:pending,Success,completed',
        ], [
            'name.required'         => 'Le nom de la filière est obligatoire.',
            'name.unique'           => 'Ce nom de filière existe déjà.',
            'code.unique'           => 'Ce code est déjà utilisé par une autre filière.',
            'code.max'              => 'Le code ne doit pas dépasser 50 caractères.',
            'cycle_id.required'     => 'Veuillez sélectionner un cycle.',
            'departement_id.required'=> 'Veuillez sélectionner un département.',
        ]);

        // Si le code n'est pas saisi, on en génère un
        $code = $this->code !== '' ? Str::upper($this->code) : 'FIL-' . Str::upper(Str::random(6));

        Filiere::create([
            'name'          => $this->name,
            'code'          => $code,
            'cycle_id'      => $this->cycle_id,
            'description'   => $this->description,
            'departement_id'=> $this->departement_id,
            'status'        => $this->status,
        ]);

        $this->resetForm();
        $this->showCreateModal = false;
        $this->loadData();
        $this->showSuccessNotification('Filière créée avec succès !');
    }

    public function update()
    {
        // Recharger la filière depuis la BDD pour comparer les valeurs actuelles
        $current = Filiere::findOrFail($this->editingId);

        // Unique sur le nom uniquement si l'utilisateur l'a changé
        $nameRule = ($this->name === $current->name)
            ? ['required', 'string', 'max:255']
            : ['required', 'string', 'max:255', 'unique:filieres,name'];

        // Unique sur le code uniquement si l'utilisateur l'a changé
        $codeInput = Str::upper($this->code);
        $codeRule = ($codeInput === Str::upper($current->code))
            ? ['required', 'string', 'max:50']
            : ['required', 'string', 'max:50', 'unique:filieres,code'];

        $this->validate([
            'name'          => $nameRule,
            'code'          => $codeRule,
            'description'   => 'nullable|string|max:2000',
            'cycle_id'      => 'required|exists:cycles,id',
            'departement_id'=> 'required|exists:departements,id',
            'status'        => 'required|in:pending,Success,completed',
        ], [
            'name.required'          => 'Le nom de la filière est obligatoire.',
            'name.unique'            => 'Ce nom est déjà utilisé par une autre filière.',
            'code.required'          => 'Le code est obligatoire.',
            'code.unique'            => 'Ce code est déjà utilisé par une autre filière.',
            'code.max'               => 'Le code ne doit pas dépasser 50 caractères.',
            'cycle_id.required'      => 'Veuillez sélectionner un cycle.',
            'departement_id.required'=> 'Veuillez sélectionner un département.',
        ]);

        $this->filiereElement->update([
            'name'          => $this->name,
            'code'          => Str::upper($this->code),   // ← mis à jour
            'cycle_id'      => $this->cycle_id,
            'description'   => $this->description,
            'departement_id'=> $this->departement_id,
            'status'        => $this->status,
        ]);

        $this->resetForm();
        $this->showEditModal = false;
        $this->filiereElement = null;
        $this->loadData();
        $this->showSuccessNotification('Filière modifiée avec succès !');
    }

    public function activateFiliere()
    {
        $this->filiereElement->update(['status' => 'Success']);
        $this->showActivateModal = false;
        $this->loadData();
        $this->showSuccessNotification('Filière activée !');
    }

    public function deactivateFiliere()
    {
        $this->filiereElement->update(['status' => 'pending']);
        $this->showDeactivateModal = false;
        $this->loadData();
        $this->showSuccessNotification('Filière désactivée !');
    }

    public function deleteFiliere()
    {
        $this->filiereElement->update(['status' => 'failed']);
        $this->showDeleteModal = false;
        $this->loadData();
        $this->showSuccessNotification('Filière supprimée !');
    }

    public function closeModal()
    {
        $this->showCreateModal    = false;
        $this->showEditModal      = false;
        $this->showDeleteModal    = false;
        $this->showActivateModal  = false;
        $this->showDeactivateModal = false;
        $this->showDetailsModal   = false;
        $this->filiereElement     = null;
        $this->editingId          = null;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name           = '';
        $this->code           = '';
        $this->description    = '';
        $this->cycle_id       = null;
        $this->departement_id = null;
        $this->filteredDepartements = [];
        $this->status         = 'Success';
        $this->formErrors     = [];
        $this->editingId      = null;
    }

    private function showSuccessNotification($message)
    {
        $this->notificationMessage = $message;
        $this->notificationType    = 'success';
        $this->showNotification    = true;
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Gestion des Filières</h1>
            <p class="text-gray-500">Créez et gérez les filières rattachées aux départements.</p>
        </div>
        <button wire:click="functionShowCreateModal"
            class="mt-6 bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition shadow-lg">
            + Nouvelle Filière
        </button>
    </div>

    <!-- ══ Modal Créer / Modifier ══ -->
    @if ($showCreateModal || $showEditModal)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                    {{ $showEditModal ? 'Modifier la filière' : 'Ajouter une filière' }}
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
                    <div class="space-y-5">

                        <!-- Nom -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la filière *</label>
                            <input wire:model="name" type="text" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Ex : Génie Informatique">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Code ← NOUVEAU -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Code de la filière
                                @if (!$showEditModal)
                                    <span class="text-gray-400 font-normal">(optionnel — généré automatiquement si vide)</span>
                                @else
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <div class="relative">
                                <input wire:model="code" type="text"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 uppercase pr-24"
                                    placeholder="Ex : FIL-INFO"
                                    style="text-transform:uppercase;"
                                    {{ $showEditModal ? 'required' : '' }}>
                                @if (!$showEditModal)
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">
                                        auto si vide
                                    </span>
                                @endif
                            </div>
                            @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-gray-500">Le code sera automatiquement mis en majuscules.</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea wire:model="description" rows="3"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Description de la filière…"></textarea>
                        </div>

                        <!-- Cycle -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cycle *</label>
                            <select wire:model.live="cycle_id" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Sélectionner un cycle —</option>
                                @foreach ($cycles as $cycle)
                                    <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                                @endforeach
                            </select>
                            @error('cycle_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Département -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Département *</label>
                            <select wire:model="departement_id" required
                                @if(!$cycle_id) disabled @endif
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                                <option value="">
                                    {{ $cycle_id ? '— Sélectionner un département —' : "— Choisissez d'abord un cycle —" }}
                                </option>
                                @foreach ($filteredDepartements as $dep)
                                    <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                                @endforeach
                            </select>
                            @error('departement_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            @if ($cycle_id && count($filteredDepartements) === 0)
                                <p class="mt-1 text-sm text-yellow-600">⚠ Aucun département actif pour ce cycle.</p>
                            @endif
                        </div>

                        <!-- Statut -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                            <select wire:model="status"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="pending">En attente</option>
                                <option value="Success">Actif</option>
                                <option value="completed">Terminé</option>
                            </select>
                        </div>

                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            {{ $showEditModal ? 'Enregistrer les modifications' : 'Créer la filière' }}
                        </button>
                        <button wire:click="closeModal" type="button"
                            class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ══ Modal Détails ══ -->
    @if ($showDetailsModal && $filiereElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">{{ $filiereElement->name }}</h2>
                <div class="space-y-4 text-sm">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-semibold text-gray-600 w-40">Code</span>
                        <span class="font-mono text-indigo-700 font-bold text-base">{{ $filiereElement->code }}</span>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-semibold text-gray-600 w-40">Cycle</span>
                        <span>{{ $filiereElement->departement?->cycle?->name ?? '—' }}</span>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-semibold text-gray-600 w-40">Département</span>
                        <span>{{ $filiereElement->departement?->nom ?? 'Non rattaché' }}</span>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-semibold text-gray-600 w-40">Description</span>
                        <span>{{ $filiereElement->description ?? '—' }}</span>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-semibold text-gray-600 w-40">Spécialités</span>
                        <span>{{ $filiereElement->specialites->count() }}</span>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="font-semibold text-gray-600 w-40">Statut</span>
                        @if ($filiereElement->status === 'Success')
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Actif</span>
                        @elseif ($filiereElement->status === 'pending')
                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">En attente</span>
                        @else
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">Inactif</span>
                        @endif
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button wire:click="closeModal"
                        class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ══ Modal Activer ══ -->
    @if ($showActivateModal && $filiereElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold mb-4">Activer la filière</h3>
                <p class="mb-6">Confirmez l'activation de <strong>{{ $filiereElement->name }}</strong> ?</p>
                <div class="flex justify-end gap-4">
                    <button wire:click="activateFiliere" class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Oui, activer</button>
                    <button wire:click="closeModal" class="px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    <!-- ══ Modal Désactiver ══ -->
    @if ($showDeactivateModal && $filiereElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold mb-4">Désactiver la filière</h3>
                <p class="mb-6">Confirmez la désactivation de <strong>{{ $filiereElement->name }}</strong> ?</p>
                <div class="flex justify-end gap-4">
                    <button wire:click="deactivateFiliere" class="px-5 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">Oui, désactiver</button>
                    <button wire:click="closeModal" class="px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    <!-- ══ Modal Supprimer ══ -->
    @if ($showDeleteModal && $filiereElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-red-600 mb-4">Supprimer la filière</h3>
                <p class="mb-6">Êtes-vous sûr de vouloir supprimer <strong>{{ $filiereElement->name }}</strong> ?</p>
                <div class="flex justify-end gap-4">
                    <button wire:click="deleteFiliere" class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Oui, supprimer</button>
                    <button wire:click="closeModal" class="px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    <!-- ══ Liste ══ -->
    <div class="bg-white rounded-2xl shadow-lg p-8 mt-10">

        {{-- Titre + barre de recherche Alpine (client-side) --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6"
             x-data="filiereSearch()" x-init="init()">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-semibold text-gray-800">Liste des Filières</h2>
                <span class="bg-indigo-100 text-indigo-700 text-sm font-semibold px-3 py-0.5 rounded-full"
                      x-text="visibleCount">{{ count($filieres) }}</span>
            </div>
            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                    </svg>
                </div>
                <input x-model="query" x-on:input="filter()"
                    type="text"
                    placeholder="Nom, code, département, cycle…"
                    class="w-full pl-9 pr-9 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"/>
                <button x-show="query" x-on:click="query=''; filter()" type="button"
                    class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        @if (count($filieres) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr class="bg-gray-100 rounded-lg">
                            <th class="p-4 text-sm font-medium text-gray-600">Nom</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Code</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Cycle</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Département</th>
                            <th class="p-4 text-sm font-medium text-gray-600 text-center">Spécialités</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filieres as $fil)
                            <tr wire:key="fil-{{ $fil->id }}"
                                data-fil-row
                                data-search="{{ mb_strtolower(implode(' ', [
                                    $fil->name ?? '',
                                    $fil->code ?? '',
                                    $fil->departement?->nom ?? '',
                                    $fil->departement?->cycle?->name ?? ''
                                ])) }}"
                                class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                <td class="p-4 font-medium">{{ $fil->name }}</td>
                                <td class="p-4">
                                    <span class="font-mono text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded font-semibold">
                                        {{ $fil->code }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="inline-block px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                        {{ $fil->departement?->cycle?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="p-4 text-gray-600">{{ $fil->departement?->nom ?? '—' }}</td>
                                <td class="p-4 text-center">{{ $specialitesCount[$fil->id] ?? 0 }}</td>
                                <td class="p-4">
                                    @if ($fil->status === 'pending')
                                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                    @elseif ($fil->status === 'Success')
                                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Actif</span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactif</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-2">
                                        <button wire:click="functionShowDetailsModal({{ $fil->id }})" class="bouton-icone text-sky-600 hover:bg-sky-50" title="Détails" aria-label="Détails"><x-icone-action nom="voir" /></button>

                                        @if ($fil->status === 'pending')
                                            <button wire:click="functionShowActivateModal({{ $fil->id }})" class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Activer" aria-label="Activer"><x-icone-action nom="activer" /></button>
                                        @elseif ($fil->status === 'Success')
                                            <button wire:click="functionShowDeactivateModal({{ $fil->id }})" class="bouton-icone text-amber-600 hover:bg-amber-50" title="Désactiver" aria-label="Désactiver"><x-icone-action nom="desactiver" /></button>
                                        @endif

                                        <button wire:click="functionShowEditModal({{ $fil->id }})" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Modifier" aria-label="Modifier"><x-icone-action nom="modifier" /></button>

                                        <button wire:click="functionShowDeleteModal({{ $fil->id }})" class="bouton-icone text-red-600 hover:bg-red-50" title="Supprimer" aria-label="Supprimer"><x-icone-action nom="supprimer" /></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500">Aucune filière enregistrée pour le moment.</p>
            </div>
        @endif
    </div>

    <!-- Notification -->
    @if ($showNotification)
        <div class="fixed top-6 right-6 z-50 max-w-sm w-full"
             x-data="{ show: true }" x-show="show" x-transition
             x-init="setTimeout(() => { show = false; $wire.set('showNotification', false) }, 4000)">
            <div class="p-4 rounded-xl shadow-lg border-l-4 bg-green-50 border-green-500 text-green-800 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-sm font-medium">{{ $notificationMessage }}</span>
            </div>
        </div>
    @endif

</div>

<script>
function filiereSearch() {
    return {
        query: '',
        visibleCount: 0,
        init() {
            this.$nextTick(() => this.updateCount());
        },
        filter() {
            const q = this.query.toLowerCase().trim();
            document.querySelectorAll('tr[data-fil-row]').forEach(row => {
                const haystack = row.dataset.search || '';
                row.style.display = (!q || haystack.includes(q)) ? '' : 'none';
            });
            this.updateCount();
        },
        updateCount() {
            this.visibleCount = document.querySelectorAll(
                'tr[data-fil-row]:not([style*="display: none"])'
            ).length;
        }
    }
}
</script>
@endvolt
</x-layouts.app>