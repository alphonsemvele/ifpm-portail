<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Ue;
use App\Models\Cycle;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\Examen;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

name('admin.ue.index');
middleware(['auth', 'verified']);

new class extends Component {
    public $ues     = [];   // chargé une fois, jamais filtré par Livewire
    public $cycles  = [];
    public $filieres = [];
    public $specialites = [];
    public $examens = [];

    public string $recherche = '';

    public bool $showAddModal        = false;
    public bool $showEditModal       = false;
    public bool $showDeleteModal     = false;
    public bool $showActivateModal   = false;
    public bool $showDeactivateModal = false;

    public $ueElement  = null;
    public $editingId  = null;   // ID scalaire fiable dans Livewire

    public bool   $showNotification    = false;
    public string $notificationMessage = '';
    public string $notificationType    = '';
    public array  $formErrors          = [];

    public string $name          = '';
    public string $code          = '';
    public $filiere_id    = null;
    public $cycle_id      = null;
    public $specialite_id = null;
    public $examen_id     = null;
    public $credits       = 0;
    public string $status = 'Success';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->ues = Ue::with(['filiere', 'filiere.cycle', 'specialite', 'examen'])
                ->where('status', '!=', 'failed')
                ->get();

            $this->cycles      = Cycle::where('status', 'Success')->get();
            $this->filieres    = Filiere::with('cycle')->where('status', 'Success')->get();
            $this->specialites = collect();
            $this->examens     = Examen::where('statut', '!=', 'annule')->get();
        } catch (\Exception $e) {
            logger('Erreur loadData UE: ' . $e->getMessage());
            $this->ues = collect();
        }
    }



    public function updatedFiliereId($value)
    {
        $this->specialite_id = null;
        $this->cycle_id      = null;
        if ($value) {
            $filiere = Filiere::find($value);
            if ($filiere) {
                $this->cycle_id    = $filiere->cycle_id;
                $this->specialites = Specialite::where('status', 'Success')
                    ->where('filiere_id', $value)->get();
            }
        } else {
            $this->specialites = collect();
        }
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function openEditModal($id)
    {
        try {
            $this->editingId  = $id;
            $this->ueElement  = Ue::with(['filiere', 'filiere.cycle', 'specialite', 'examen'])->findOrFail($id);

            $this->name          = $this->ueElement->name    ?? '';
            $this->code          = $this->ueElement->code    ?? '';
            $this->credits       = $this->ueElement->credits ?? 0;
            $this->filiere_id    = $this->ueElement->filiere_id;
            $this->cycle_id      = $this->ueElement->cycle_id;
            $this->specialite_id = $this->ueElement->specialite_id;
            $this->examen_id     = $this->ueElement->examen_id;
            $this->status        = $this->ueElement->status  ?? 'Success';

            $this->specialites = Specialite::where('status', 'Success')
                ->where('filiere_id', $this->filiere_id)->get();

            $this->showEditModal = true;
            $this->formErrors    = [];
        } catch (\Exception $e) {
            $this->addError('general', 'Erreur lors de l\'ouverture du formulaire.');
        }
    }

    public function openActivateModal($id)
    {
        $this->ueElement         = Ue::findOrFail($id);
        $this->showActivateModal = true;
    }

    public function openDeactivateModal($id)
    {
        $this->ueElement           = Ue::findOrFail($id);
        $this->showDeactivateModal = true;
    }

    public function openDeleteModal($id)
    {
        $this->ueElement       = Ue::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function save()
    {
        try {
            // Forcer null si vide (string vide "" invalide pour exists:)
            $this->specialite_id = $this->specialite_id ?: null;
            $this->examen_id     = $this->examen_id     ?: null;

            $this->validate([
                'name'          => 'required|string|max:255',
                'filiere_id'    => 'required|exists:filieres,id',
                'specialite_id' => 'nullable|exists:specialites,id',
                'examen_id'     => 'nullable|exists:examens,id',
                'credits'       => 'required|integer|min:0|max:30',
                'status'        => 'required|in:Success,pending,failed',
            ], [
                'name.required'        => 'Le nom de l\'UE est obligatoire.',
                'filiere_id.required'  => 'Veuillez sélectionner une filière.',
                'credits.required'     => 'Le nombre de crédits est obligatoire.',
                'examen_id.exists'     => 'L\'examen sélectionné est introuvable.',
                'specialite_id.exists' => 'La spécialité sélectionnée est introuvable.',
            ]);

            $filiere  = Filiere::find($this->filiere_id);
            $cycle_id = $filiere?->cycle_id;
            $code     = 'UE-' . Str::upper(Str::random(6));

            Ue::create([
                'name'          => $this->name,
                'code'          => $code,
                'filiere_id'    => $this->filiere_id,
                'cycle_id'      => $cycle_id,
                'specialite_id' => $this->specialite_id ?: null,
                'examen_id'     => $this->examen_id     ?: null,
                'credits'       => $this->credits,
                'status'        => $this->status,
            ]);

            $this->resetForm();
            $this->showAddModal = false;
            $this->loadData();
            $this->showSuccessNotification('UE ajoutée avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            logger('Erreur save UE: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la création.');
        }
    }

    public function update()
    {
        try {
            // Forcer null si vide (string vide "" invalide pour exists:)
            $this->specialite_id = $this->specialite_id ?: null;
            $this->examen_id     = $this->examen_id     ?: null;

            // Recharger depuis la BDD pour comparer
            $current = Ue::findOrFail($this->editingId);

            // Unique sur le code seulement si changé
            $codeInput = Str::upper($this->code);
            $codeRule  = ($codeInput === Str::upper($current->code))
                ? ['required', 'string', 'max:50']
                : ['required', 'string', 'max:50', 'unique:ues,code'];

            $this->validate([
                'name'          => 'required|string|max:255',
                'code'          => $codeRule,
                'filiere_id'    => 'required|exists:filieres,id',
                'specialite_id' => 'nullable|exists:specialites,id',
                'examen_id'     => 'nullable|exists:examens,id',
                'credits'       => 'required|integer|min:0|max:30',
                'status'        => 'required|in:Success,pending,failed',
            ], [
                'name.required'       => 'Le nom de l\'UE est obligatoire.',
                'code.unique'         => 'Ce code est déjà utilisé par une autre UE.',
                'filiere_id.required' => 'Veuillez sélectionner une filière.',
                'credits.required'    => 'Le nombre de crédits est obligatoire.',
                'examen_id.exists'    => 'L\'examen sélectionné est introuvable.',
                'specialite_id.exists'=> 'La spécialité sélectionnée est introuvable.',
            ]);

            $filiere  = Filiere::find($this->filiere_id);
            $cycle_id = $filiere?->cycle_id;

            $current->update([
                'name'          => $this->name,
                'code'          => $codeInput,
                'filiere_id'    => $this->filiere_id,
                'cycle_id'      => $cycle_id,
                'specialite_id' => $this->specialite_id ?: null,
                'examen_id'     => $this->examen_id     ?: null,
                'credits'       => $this->credits,
                'status'        => $this->status,
            ]);

            $this->resetForm();
            $this->showEditModal = false;
            $this->ueElement     = null;
            $this->loadData();
            $this->showSuccessNotification('UE modifiée avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            logger('Erreur update UE: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la mise à jour.');
        }
    }

    public function activate($id)
    {
        Ue::findOrFail($id)->update(['status' => 'Success']);
        $this->closeModal();
        $this->loadData();
        $this->showSuccessNotification('UE activée !');
    }

    public function deactivate($id)
    {
        Ue::findOrFail($id)->update(['status' => 'pending']);
        $this->closeModal();
        $this->loadData();
        $this->showSuccessNotification('UE désactivée !');
    }

    public function delete($id)
    {
        Ue::findOrFail($id)->update(['status' => 'failed']);
        $this->closeModal();
        $this->loadData();
        $this->showSuccessNotification('UE supprimée !');
    }

    public function closeModal()
    {
        $this->showAddModal        = false;
        $this->showEditModal       = false;
        $this->showDeleteModal     = false;
        $this->showActivateModal   = false;
        $this->showDeactivateModal = false;
        $this->ueElement           = null;
        $this->editingId           = null;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name          = '';
        $this->code          = '';
        $this->credits       = 0;
        $this->filiere_id    = null;
        $this->cycle_id      = null;
        $this->specialite_id = null;
        $this->examen_id     = null;
        $this->status        = 'Success';
        $this->specialites   = collect();
        $this->formErrors    = [];
        $this->editingId     = null;
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

    {{-- ── Header ── --}}
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Gestion des Unités d'Enseignement</h1>
            <p class="text-gray-500">Créez, modifiez, activez, désactivez ou supprimez des unités d'enseignement.</p>
        </div>
    </div>

    {{-- ── Bouton ajouter : HORS Alpine pour que wire:click fonctionne ── --}}
    <div class="mb-4">
        <button wire:click="openAddModal" type="button"
            class="flex items-center gap-2 bg-indigo-600 text-white py-2.5 px-6 rounded-xl hover:bg-indigo-700 transition shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter une UE
        </button>
    </div>

    {{-- ── Barre de recherche Alpine (client-side, séparée du bouton) ── --}}
    <div class="mb-8" x-data="ueSearch()" x-init="init()">
        <div class="relative w-full sm:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </div>
            <input x-model="query" x-on:input="filter()"
                type="text"
                placeholder="Rechercher par nom, code, filière, spécialité, cycle…"
                class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            <button x-show="query" x-on:click="query=''; filter()" type="button"
                class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>


    @error('general')
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border-l-4 border-red-500">{{ $message }}</div>
    @enderror

    {{-- ── Statistiques ── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl shadow p-5 text-center">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Total</p>
            <p class="text-3xl font-bold text-indigo-600">{{ collect($ues)->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-5 text-center">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Actives</p>
            <p class="text-3xl font-bold text-green-600">{{ collect($ues)->where('status','Success')->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-5 text-center">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">En attente</p>
            <p class="text-3xl font-bold text-yellow-600">{{ collect($ues)->where('status','pending')->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-5 text-center">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Résultats</p>
            <p class="text-3xl font-bold text-gray-700" x-data="{ n: {{ collect($ues)->count() }} }" x-on:ue-count.window="n = $event.detail" x-text="n">{{ collect($ues)->count() }}</p>
        </div>
    </div>

    {{-- ── Liste ── --}}
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Liste des UE</h2>
        </div>

        @if(count($ues) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Nom</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Code</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Crédits</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Spécialité</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Examen lié</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Filière</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Cycle</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Statut</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ues as $ue)
                            <tr wire:key="ue-{{ $ue->id }}"
                                data-ue-row
                                data-search="{{ mb_strtolower(implode(' ', [
                                    $ue->name ?? '',
                                    $ue->code ?? '',
                                    $ue->filiere->name ?? '',
                                    $ue->specialite->name ?? '',
                                    $ue->filiere->cycle->name ?? ''
                                ])) }}"
                                class="bg-gray-50 hover:bg-indigo-50 transition duration-150 rounded-lg">
                                <td class="p-4 font-medium text-gray-900">
                                    {{ $ue->name ?? '—' }}
                                </td>
                                <td class="p-4">
                                    <span class="font-mono text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded font-semibold">
                                        {{ $ue->code ?? '—' }}
                                    </span>
                                </td>
                                <td class="p-4 text-gray-600">{{ $ue->credits ?? 0 }} cr.</td>
                                <td class="p-4 text-gray-600">{{ $ue->specialite->name ?? '—' }}</td>
                                <td class="p-4">
                                    @if ($ue->examen)
                                        <span class="inline-flex items-center gap-1 text-xs bg-purple-50 text-purple-700 px-2 py-1 rounded-full font-medium">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            {{ $ue->examen->titre }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="p-4 text-gray-600">{{ $ue->filiere->name ?? '—' }}</td>
                                <td class="p-4">
                                    <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800">
                                        {{ $ue->filiere->cycle->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                                        {{ $ue->status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $ue->status === 'Success' ? 'Actif' : 'En attente' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-2">
                                        @if ($ue->status === 'pending')
                                            <button wire:click="openActivateModal({{ $ue->id }})" class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Activer" aria-label="Activer"><x-icone-action nom="activer" /></button>
                                        @else
                                            <button wire:click="openDeactivateModal({{ $ue->id }})" class="bouton-icone text-amber-600 hover:bg-amber-50" title="Désactiver" aria-label="Désactiver"><x-icone-action nom="desactiver" /></button>
                                        @endif
                                        <button wire:click="openEditModal({{ $ue->id }})" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Modifier" aria-label="Modifier"><x-icone-action nom="modifier" /></button>
                                        <button wire:click="openDeleteModal({{ $ue->id }})" class="bouton-icone text-red-600 hover:bg-red-50" title="Supprimer" aria-label="Supprimer"><x-icone-action nom="supprimer" /></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-16">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500">Aucune unité d'enseignement enregistrée.</p>
            </div>
        @endif
    </div>

    {{-- ── Modal Ajouter ── --}}
    @if ($showAddModal)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-start justify-center z-50 overflow-y-auto p-4">
            <div class="bg-white rounded-2xl p-8 w-full max-w-xl shadow-2xl my-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Ajouter une UE</h2>

                @if (!empty($formErrors))
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl border-l-4 border-red-500 text-sm">
                        <ul class="list-disc ml-4 space-y-1">
                            @foreach ($formErrors as $errors)
                                @foreach ((array)$errors as $err)<li>{{ $err }}</li>@endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit="save">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filière *</label>
                            <select wire:model.live="filiere_id" required
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Sélectionner une filière —</option>
                                @foreach ($filieres as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Spécialité <span class="text-gray-400 font-normal">(optionnel)</span></label>
                            <select wire:model="specialite_id"
                                @if(!$filiere_id) disabled @endif
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 disabled:bg-gray-100">
                                <option value="">— Optionnel —</option>
                                @foreach ($specialites as $sp)
                                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Examen lié <span class="text-gray-400 font-normal">(optionnel)</span></label>
                            <select wire:model="examen_id"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Optionnel —</option>
                                @foreach ($examens as $ex)
                                    <option value="{{ $ex->id }}">
                                        {{ $ex->titre }}@if($ex->date) ({{ \Carbon\Carbon::parse($ex->date)->format('d/m/Y') }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'UE *</label>
                            <input type="text" wire:model="name" required
                                placeholder="Ex : Mathématiques Appliquées"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Crédits *</label>
                            <input type="number" wire:model="credits" required min="0" max="30"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                            <select wire:model="status"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="Success">Actif</option>
                                <option value="pending">En attente</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <button type="submit" class="bg-indigo-600 text-white py-2.5 px-6 rounded-xl hover:bg-indigo-700">Enregistrer</button>
                        <button type="button" wire:click="closeModal" class="bg-gray-200 text-gray-700 py-2.5 px-6 rounded-xl hover:bg-gray-300">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ── Modal Modifier ── --}}
    @if ($showEditModal && $ueElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-start justify-center z-50 overflow-y-auto p-4">
            <div class="bg-white rounded-2xl p-8 w-full max-w-xl shadow-2xl my-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Modifier l'UE</h2>

                @if (!empty($formErrors))
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-xl border-l-4 border-red-500 text-sm">
                        <ul class="list-disc ml-4 space-y-1">
                            @foreach ($formErrors as $errors)
                                @foreach ((array)$errors as $err)<li>{{ $err }}</li>@endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit="update">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filière *</label>
                            <select wire:model.live="filiere_id" required
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Sélectionner une filière —</option>
                                @foreach ($filieres as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Spécialité <span class="text-gray-400 font-normal">(optionnel)</span></label>
                            <select wire:model="specialite_id"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Optionnel —</option>
                                @foreach ($specialites as $sp)
                                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Examen lié <span class="text-gray-400 font-normal">(optionnel)</span></label>
                            <select wire:model="examen_id"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Optionnel —</option>
                                @foreach ($examens as $ex)
                                    <option value="{{ $ex->id }}">
                                        {{ $ex->titre }}@if($ex->date) ({{ \Carbon\Carbon::parse($ex->date)->format('d/m/Y') }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'UE *</label>
                            <input type="text" wire:model="name" required
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Code *
                                <span class="text-gray-400 font-normal text-xs ml-1">(sera mis en majuscules)</span>
                            </label>
                            <input type="text" wire:model="code" required
                                placeholder="ex : UE-ABC123"
                                style="text-transform:uppercase;"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Crédits *</label>
                            <input type="number" wire:model="credits" required min="0" max="30"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                            <select wire:model="status"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="Success">Actif</option>
                                <option value="pending">En attente</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <button type="submit" class="bg-indigo-600 text-white py-2.5 px-6 rounded-xl hover:bg-indigo-700">Mettre à jour</button>
                        <button type="button" wire:click="closeModal" class="bg-gray-200 text-gray-700 py-2.5 px-6 rounded-xl hover:bg-gray-300">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ── Modal Activer ── --}}
    @if ($showActivateModal && $ueElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-semibold text-gray-800 mb-3">Activer l'UE</h3>
                <p class="mb-6 text-gray-600">Voulez-vous activer <strong>{{ $ueElement->name }}</strong> ?</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="activate({{ $ueElement->id }})" class="bg-green-600 text-white py-2 px-5 rounded-xl hover:bg-green-700">Oui, activer</button>
                    <button wire:click="closeModal" class="bg-gray-200 text-gray-700 py-2 px-5 rounded-xl hover:bg-gray-300">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal Désactiver ── --}}
    @if ($showDeactivateModal && $ueElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-semibold text-gray-800 mb-3">Désactiver l'UE</h3>
                <p class="mb-6 text-gray-600">Voulez-vous désactiver <strong>{{ $ueElement->name }}</strong> ?</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="deactivate({{ $ueElement->id }})" class="bg-yellow-600 text-white py-2 px-5 rounded-xl hover:bg-yellow-700">Oui, désactiver</button>
                    <button wire:click="closeModal" class="bg-gray-200 text-gray-700 py-2 px-5 rounded-xl hover:bg-gray-300">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal Supprimer ── --}}
    @if ($showDeleteModal && $ueElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-semibold text-red-600 mb-3">Supprimer l'UE</h3>
                <p class="mb-6 text-gray-600">Voulez-vous supprimer <strong>{{ $ueElement->name }}</strong> ? Cette action est irréversible.</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="delete({{ $ueElement->id }})" class="bg-red-600 text-white py-2 px-5 rounded-xl hover:bg-red-700">Oui, supprimer</button>
                    <button wire:click="closeModal" class="bg-gray-200 text-gray-700 py-2 px-5 rounded-xl hover:bg-gray-300">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Toast notification ── --}}
    @if ($showNotification)
        <div class="fixed top-6 right-6 z-50 max-w-sm w-full"
            x-data="{ show: true }"
            x-init="setTimeout(() => { show = false; $wire.set('showNotification', false); }, 3500)"
            x-show="show" x-transition>
            <div class="flex items-center gap-3 p-4 rounded-xl shadow-lg border-l-4
                {{ $notificationType === 'success' ? 'bg-green-50 text-green-700 border-green-500' : 'bg-red-50 text-red-700 border-red-500' }}">
                @if ($notificationType === 'success')
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                @endif
                <span class="text-sm font-medium">{{ $notificationMessage }}</span>
            </div>
        </div>
    @endif

</div>

<script>
function ueSearch() {
    return {
        query: '',
        visibleCount: 0,
        init() {
            this.$nextTick(() => this.updateCount());
        },
        filter() {
            const q = this.query.toLowerCase().trim();
            document.querySelectorAll('tr[data-ue-row]').forEach(row => {
                const haystack = row.dataset.search || '';
                row.style.display = (!q || haystack.includes(q)) ? '' : 'none';
            });
            this.updateCount();
        },
        updateCount() {
            this.visibleCount = document.querySelectorAll('tr[data-ue-row]:not([style*="display: none"])').length;
            window.dispatchEvent(new CustomEvent('ue-count', { detail: this.visibleCount }));
        }
    }
}
</script>
@endvolt
</x-layouts.app>