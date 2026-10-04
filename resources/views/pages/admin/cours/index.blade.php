<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Cour;
use App\Models\User;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\Ue;
use App\Models\Examen;
use Illuminate\Support\Str;

name('admin.cours-calendrier');
middleware(['auth', 'verified']);

new class extends Component {
    public $cours;
    public $filieres;
    public $specialites;
    public $ues;
    public $enseignants;

    // ── Recherche & Filtres ───────────────────────────────────────────────────
    public $filter_filiere_id          = null;
    public $filter_specialite_id       = null;
    public $filter_statut              = '';
    public $filter_specialites         = [];

    // ── Formulaire ────────────────────────────────────────────────────────────
    public $name           = '';
    public $code           = '';   // ← modifiable
    public $filiere_id     = null;
    public $specialite_id  = null;
    public $ue_id          = null;
    public $examen_id      = null;
    public $examenLabel    = '';
    public $responsable_id = null;
    public $credit         = 0;
    public $hour_number    = '';
    public $status         = 'pending';

    // ── Modals ────────────────────────────────────────────────────────────────
    public $showCreateModal     = false;
    public $showEditModal       = false;
    public $showDeleteModal     = false;
    public $showActivateModal   = false;
    public $showDeactivateModal = false;
    public $showDetailsModal    = false;

    public $courElement;
    public $editingId = null;   // ← ID scalaire fiable dans Livewire

    public bool   $showNotification    = false;
    public string $notificationMessage = '';
    public string $notificationType    = '';
    public array  $formErrors          = [];

    public function mount()
    {
        $this->loadData();
    }

    // ── Watchers filtres ─────────────────────────────────────────────────────
    public function updatedFilterStatut()       { $this->loadData(); }
    public function updatedFilterSpecialiteId() { $this->loadData(); }

    public function updatedFilterFiliereId($value)
    {
        $this->filter_specialite_id = null;
        $this->filter_specialites   = $value
            ? Specialite::where('status', 'Success')->where('filiere_id', $value)->orderBy('name')->get()
            : collect();
        $this->loadData();
    }

    public function resetFilters()
    {
        $this->filter_filiere_id    = null;
        $this->filter_specialite_id = null;
        $this->filter_statut        = '';
        $this->filter_specialites   = collect();
        $this->loadData();
    }

    // ── Chargement ───────────────────────────────────────────────────────────
    public function loadData()
    {
        try {
            $query = Cour::with(['filiere', 'specialite', 'responsable', 'ue', 'examen'])
                ->where('status', '!=', 'failed');

            if ($this->filter_filiere_id)    $query->where('filiere_id',    $this->filter_filiere_id);
            if ($this->filter_specialite_id) $query->where('specialite_id', $this->filter_specialite_id);
            if ($this->filter_statut !== '') $query->where('status',         $this->filter_statut);

            $this->cours = $query->orderBy('name')->get();

            $this->filieres    = Filiere::where('status', 'Success')->orderBy('name')->get();
            $this->enseignants = User::whereNotIn('role', ['student', 'etudiant', 'admin'])
                ->where('status', 'Success')
                ->orderBy('name')
                ->get();

        } catch (\Exception $e) {
            logger('Erreur loadData: ' . $e->getMessage());
            $this->cours = $this->filieres = $this->enseignants = collect();
        }
    }

    // ── Cascades formulaire ──────────────────────────────────────────────────
    public function updatedFiliereId($value)
    {
        $this->specialite_id = null;
        $this->ue_id         = null;
        $this->examen_id     = null;
        $this->examenLabel   = '';
        $this->specialites   = $value
            ? Specialite::where('status', 'Success')->where('filiere_id', $value)->get()
            : collect();
        $this->ues = collect();
    }

    public function updatedSpecialiteId($value)
    {
        $this->ue_id = null;
        $this->ues   = $value
            ? Ue::where('status', 'Success')->where('specialite_id', $value)->get()
            : collect();
    }

    public function updatedUeId($value)
    {
        if ($value) {
            $ue = Ue::with('examen')->find($value);
            if ($ue && $ue->examen_id) {
                $this->examen_id   = $ue->examen_id;
                $this->examenLabel = $ue->examen
                    ? $ue->examen->titre . ($ue->examen->date ? ' (' . \Carbon\Carbon::parse($ue->examen->date)->format('d/m/Y') . ')' : '')
                    : '';
            } else {
                $this->examen_id   = null;
                $this->examenLabel = '';
            }
        } else {
            $this->examen_id   = null;
            $this->examenLabel = '';
        }
    }

    private function resolveExamenId(): void
    {
        if ($this->examen_id) return;
        if ($this->ue_id) {
            $ue = Ue::find($this->ue_id);
            if ($ue?->examen_id) $this->examen_id = $ue->examen_id;
        }
    }

    // ── Modals ───────────────────────────────────────────────────────────────
    public function functionShowCreateModal()
    {
        $this->resetForm();
        $this->filieres        = Filiere::where('status', 'Success')->get();
        $this->showCreateModal = true;
    }

    public function functionShowEditModal($id)
    {
        try {
            $this->editingId   = $id;   // ← stockage scalaire
            $this->courElement = Cour::with(['filiere','specialite','responsable','ue','examen'])->findOrFail($id);

            $this->name           = $this->courElement->name;
            $this->code           = $this->courElement->code ?? '';
            $this->filiere_id     = $this->courElement->filiere_id;
            $this->specialite_id  = $this->courElement->specialite_id;
            $this->ue_id          = $this->courElement->ue_id;
            $this->examen_id      = $this->courElement->examen_id;
            $this->responsable_id = $this->courElement->responsable_id;
            $this->credit         = $this->courElement->credit ?? 0;
            $this->hour_number    = $this->courElement->hour_number;
            $this->status         = $this->courElement->status;

            if ($this->courElement->examen) {
                $ex = $this->courElement->examen;
                $this->examenLabel = $ex->titre . ($ex->date ? ' (' . \Carbon\Carbon::parse($ex->date)->format('d/m/Y') . ')' : '');
            } else {
                $this->examenLabel = '';
            }

            $this->filieres    = Filiere::where('status', 'Success')->get();
            $this->specialites = $this->filiere_id
                ? Specialite::where('status','Success')->where('filiere_id', $this->filiere_id)->get()
                : collect();
            $this->ues = $this->specialite_id
                ? Ue::where('status','Success')->where('specialite_id', $this->specialite_id)->get()
                : collect();

            $this->showEditModal = true;
            $this->formErrors    = [];
        } catch (\Exception $e) {
            logger('Erreur functionShowEditModal: ' . $e->getMessage());
            $this->formErrors['general'] = "Erreur lors de l'ouverture du formulaire.";
        }
    }

    public function functionShowDetailsModal($id)
    {
        $this->courElement      = Cour::with(['filiere','specialite','responsable','ue','examen'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function functionShowDeleteModal($id)
    {
        $this->courElement     = Cour::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function functionShowActivateModal($id)
    {
        $this->courElement       = Cour::findOrFail($id);
        $this->showActivateModal = true;
    }

    public function functionShowDeactivateModal($id)
    {
        $this->courElement         = Cour::findOrFail($id);
        $this->showDeactivateModal = true;
    }

    // ── CRUD ─────────────────────────────────────────────────────────────────
    public function save()
    {
        try {
            $rules = [
                'name'           => 'required|string|max:255',
                'filiere_id'     => 'required|exists:filieres,id',
                'specialite_id'  => count($this->specialites) > 0 ? 'required|exists:specialites,id' : 'nullable',
                'ue_id'          => count($this->ues) > 0 ? 'required|exists:ues,id' : 'nullable',
                'responsable_id' => 'nullable|exists:users,id',
                'credit'         => 'required|integer|min:0',
                'hour_number'    => 'required|integer|min:1',
                'status'         => 'required|in:pending,Success,completed',
            ];

            $validated = $this->validate($rules, [
                'name.required'        => 'Le nom du cours est obligatoire.',
                'code.unique'          => 'Ce code est déjà utilisé par un autre cours.',
                'filiere_id.required'  => 'Veuillez sélectionner une filière.',
                'hour_number.required' => 'Le nombre d\'heures est obligatoire.',
                'hour_number.min'      => 'Le nombre d\'heures doit être au moins 1.',
            ]);

            $this->resolveExamenId();

            if (!$this->examen_id) {
                $this->formErrors['examen_id'] = ['Aucun examen trouvé pour cette UE.'];
                return;
            }

            Cour::create([
                'name'           => $validated['name'],
                'code'           => 'C-' . Str::upper(Str::random(6)),
                'filiere_id'     => $validated['filiere_id'],
                'specialite_id'  => $validated['specialite_id'] ?? null,
                'ue_id'          => $validated['ue_id'] ?? null,
                'examen_id'      => $this->examen_id,
                'responsable_id' => !empty($validated['responsable_id']) ? $validated['responsable_id'] : null,
                'credit'         => $validated['credit'] ?? 0,
                'hour_number'    => $validated['hour_number'],
                'status'         => $validated['status'],
            ]);

            $this->resetForm();
            $this->showCreateModal = false;
            $this->loadData();
            $this->showSuccessNotification('Cours ajouté avec succès !');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            logger('Erreur save: ' . $e->getMessage());
            $this->formErrors['general'] = 'Erreur : ' . $e->getMessage();
        }
    }

    public function update()
    {
        try {
            // ── Refetch depuis la BDD via ID scalaire ──────────────────────
            $current = Cour::findOrFail($this->editingId);

            // Unique sur le code uniquement si changé
            $codeInput = Str::upper(trim($this->code));
            $codeRule  = ($codeInput === Str::upper($current->code ?? ''))
                ? ['nullable', 'string', 'max:50']
                : ['nullable', 'string', 'max:50', 'unique:cours,code'];

            $rules = [
                'name'           => 'required|string|max:255',
                'code'           => $codeRule,
                'filiere_id'     => 'required|exists:filieres,id',
                'specialite_id'  => count($this->specialites) > 0 ? 'required|exists:specialites,id' : 'nullable',
                'ue_id'          => count($this->ues) > 0 ? 'required|exists:ues,id' : 'nullable',
                'responsable_id' => 'nullable|exists:users,id',
                'credit'         => 'required|integer|min:0',
                'hour_number'    => 'required|integer|min:1',
                'status'         => 'required|in:pending,Success,completed',
            ];

            $validated = $this->validate($rules, [
                'name.required'        => 'Le nom du cours est obligatoire.',
                'code.unique'          => 'Ce code est déjà utilisé par un autre cours.',
                'filiere_id.required'  => 'Veuillez sélectionner une filière.',
                'hour_number.required' => 'Le nombre d\'heures est obligatoire.',
                'hour_number.min'      => 'Le nombre d\'heures doit être au moins 1.',
            ]);

            $this->resolveExamenId();

            if (!$this->examen_id) {
                $this->formErrors['examen_id'] = ['Aucun examen trouvé pour cette UE.'];
                return;
            }

            // Conserver le code existant (ne pas régénérer à chaque modif)
            $current->update([
                'name'           => $validated['name'],
                'code'           => !empty($codeInput) ? $codeInput : ($current->code ?? ''),
                'filiere_id'     => $validated['filiere_id'],
                'specialite_id'  => $validated['specialite_id'] ?? null,
                'ue_id'          => $validated['ue_id'] ?? null,
                'examen_id'      => $this->examen_id,
                'responsable_id' => !empty($validated['responsable_id']) ? $validated['responsable_id'] : null,
                'credit'         => $validated['credit'] ?? 0,
                'hour_number'    => $validated['hour_number'],
                'status'         => $validated['status'],
            ]);

            $this->resetForm();
            $this->showEditModal = false;
            $this->courElement   = null;
            $this->loadData();
            $this->showSuccessNotification('Cours mis à jour avec succès !');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            logger('Erreur update: ' . $e->getMessage());
            $this->formErrors['general'] = 'Erreur : ' . $e->getMessage();
        }
    }

    public function activateCour()
    {
        $this->courElement->update(['status' => 'Success']);
        $this->showActivateModal = false;
        $this->loadData();
        $this->showSuccessNotification('Cours activé !');
    }

    public function deactivateCour()
    {
        $this->courElement->update(['status' => 'pending']);
        $this->showDeactivateModal = false;
        $this->loadData();
        $this->showSuccessNotification('Cours désactivé !');
    }

    public function deleteCour()
    {
        $this->courElement->update(['status' => 'failed']);
        $this->showDeleteModal = false;
        $this->loadData();
        $this->showSuccessNotification('Cours supprimé !');
    }

    public function closeModal()
    {
        $this->showCreateModal     = false;
        $this->showEditModal       = false;
        $this->showDeleteModal     = false;
        $this->showActivateModal   = false;
        $this->showDeactivateModal = false;
        $this->showDetailsModal    = false;
        $this->courElement         = null;
        $this->editingId           = null;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name           = '';
        $this->code           = '';
        $this->filiere_id     = null;
        $this->specialite_id  = null;
        $this->ue_id          = null;
        $this->examen_id      = null;
        $this->examenLabel    = '';
        $this->responsable_id = null;
        $this->credit         = 0;
        $this->hour_number    = '';
        $this->status         = 'pending';
        $this->formErrors     = [];
        $this->specialites    = collect();
        $this->ues            = collect();
        $this->editingId      = null;
    }

    private function showSuccessNotification($message)
    {
        $this->notificationMessage = $message;
        $this->notificationType    = 'success';
        $this->showNotification    = true;
    }

    public function getActiveFiltersCountProperty(): int
    {
        return (int)(!empty($this->filter_filiere_id))
             + (int)(!empty($this->filter_specialite_id))
             + (int)($this->filter_statut !== '');
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ─── Header ─── --}}
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Gestion des Cours et Calendrier</h1>
            <p class="text-gray-500">Configurez les cours, le calendrier et affectez un enseignant.</p>
        </div>
        <button wire:click="functionShowCreateModal"
            class="mt-6 bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition shadow-lg">
            + Ajouter un cours
        </button>
    </div>

    @if (!empty($formErrors['general']))
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border-l-4 border-red-500">
            {{ $formErrors['general'] }}
        </div>
    @endif

    {{-- ════ MODAL CRÉER ════ --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Ajouter un Cours</h2>

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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filière *</label>
                            <select wire:model.live="filiere_id" required
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Sélectionner —</option>
                                @foreach ($filieres as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Spécialité</label>
                            <select wire:model.live="specialite_id"
                                @if(!$filiere_id) disabled @endif
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 disabled:bg-gray-100">
                                <option value="">— Optionnel —</option>
                                @foreach ($specialites as $sp)
                                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unité d'Enseignement</label>
                            <select wire:model.live="ue_id"
                                @if(!$specialite_id) disabled @endif
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 disabled:bg-gray-100">
                                <option value="">— Optionnel —</option>
                                @foreach ($ues as $ue)
                                    <option value="{{ $ue->id }}">{{ $ue->name }} ({{ $ue->credits }} cr.)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Examen lié
                                <span class="text-xs text-gray-400 font-normal">(automatique depuis l'UE)</span>
                            </label>
                            @if ($examen_id && $examenLabel)
                                <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="text-purple-700 text-sm font-medium">{{ $examenLabel }}</span>
                                </div>
                            @else
                                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-700 text-sm">
                                    ⚠️ Aucun examen — sélectionnez une UE avec un examen associé.
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du cours *</label>
                            <input wire:model="name" type="text" required placeholder="Ex : Algèbre Linéaire"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Enseignant responsable</label>
                            <select wire:model="responsable_id"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Optionnel —</option>
                                @foreach ($enseignants as $ens)
                                    <option value="{{ $ens->id }}">{{ $ens->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Crédits *</label>
                            <input wire:model="credit" type="number" required min="0"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre d'heures *</label>
                            <input wire:model="hour_number" type="number" required min="1"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                            <select wire:model="status" required
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="pending">En attente</option>
                                <option value="Success">Actif</option>
                                <option value="completed">Terminé</option>
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

    {{-- ════ MODAL MODIFIER ════ --}}
    @if ($showEditModal && $courElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Modifier le cours</h2>

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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filière *</label>
                            <select wire:model.live="filiere_id" required
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Sélectionner —</option>
                                @foreach ($filieres as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Spécialité</label>
                            <select wire:model.live="specialite_id"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Optionnel —</option>
                                @foreach ($specialites as $sp)
                                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unité d'Enseignement</label>
                            <select wire:model.live="ue_id"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Optionnel —</option>
                                @foreach ($ues as $ue)
                                    <option value="{{ $ue->id }}">{{ $ue->name }} ({{ $ue->credits }} cr.)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Examen lié
                                <span class="text-xs text-gray-400 font-normal">(automatique depuis l'UE)</span>
                            </label>
                            @if ($examen_id && $examenLabel)
                                <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="text-purple-700 text-sm font-medium">{{ $examenLabel }}</span>
                                </div>
                            @else
                                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-700 text-sm">
                                    ⚠️ Aucun examen — sélectionnez une UE avec un examen associé.
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du cours *</label>
                            <input wire:model="name" type="text" required
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Code
                                <span class="text-gray-400 font-normal text-xs ml-1">(sera mis en majuscules)</span>
                            </label>
                            <input wire:model="code" type="text"
                                placeholder="ex : C-ABC123"
                                style="text-transform:uppercase;"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Enseignant responsable</label>
                            <select wire:model="responsable_id"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="">— Optionnel —</option>
                                @foreach ($enseignants as $ens)
                                    <option value="{{ $ens->id }}">{{ $ens->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Crédits *</label>
                            <input wire:model="credit" type="number" required min="0"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre d'heures *</label>
                            <input wire:model="hour_number" type="number" required min="1"
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                            <select wire:model="status" required
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                <option value="pending">En attente</option>
                                <option value="Success">Actif</option>
                                <option value="completed">Terminé</option>
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

    {{-- ════ MODAL DÉTAILS ════ --}}
    @if ($showDetailsModal && $courElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">{{ $courElement->name }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([
                        ['Code',          $courElement->code ?? '—'],
                        ['Filière',       $courElement->filiere->name ?? '—'],
                        ['Spécialité',    $courElement->specialite->name ?? '—'],
                        ['UE',            $courElement->ue->name ?? '—'],
                        ['Enseignant',    $courElement->responsable->name ?? '—'],
                        ['Crédits',       ($courElement->credit ?? 0) . ' cr.'],
                        ['Heures',        ($courElement->hour_number ?? '—') . ' h'],
                    ] as [$label, $val])
                        <div class="p-3 bg-gray-50 rounded-xl">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ $label }}</p>
                            <p class="text-gray-800 font-medium">{{ $val }}</p>
                        </div>
                    @endforeach
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Examen lié</p>
                        @if ($courElement->examen)
                            <span class="inline-flex items-center gap-1 text-xs bg-purple-50 text-purple-700 px-2 py-1 rounded-full font-medium">
                                {{ $courElement->examen->titre }}
                                @if ($courElement->examen->date) ({{ \Carbon\Carbon::parse($courElement->examen->date)->format('d/m/Y') }}) @endif
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Statut</p>
                        @if ($courElement->status === 'Success')
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Actif</span>
                        @elseif ($courElement->status === 'pending')
                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">En attente</span>
                        @else
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">Terminé</span>
                        @endif
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button wire:click="closeModal" class="bg-gray-200 text-gray-700 py-2.5 px-6 rounded-xl hover:bg-gray-300">Fermer</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modals Activer / Désactiver / Supprimer --}}
    @if ($showActivateModal && $courElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-semibold text-gray-800 mb-3">Activer le cours</h3>
                <p class="mb-6 text-gray-600">Voulez-vous activer <strong>{{ $courElement->name }}</strong> ?</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="activateCour" class="bg-green-600 text-white py-2 px-5 rounded-xl hover:bg-green-700">Oui, activer</button>
                    <button wire:click="closeModal"   class="bg-gray-200 text-gray-700 py-2 px-5 rounded-xl hover:bg-gray-300">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showDeactivateModal && $courElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-semibold text-gray-800 mb-3">Désactiver le cours</h3>
                <p class="mb-6 text-gray-600">Voulez-vous désactiver <strong>{{ $courElement->name }}</strong> ?</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="deactivateCour" class="bg-yellow-600 text-white py-2 px-5 rounded-xl hover:bg-yellow-700">Oui, désactiver</button>
                    <button wire:click="closeModal"     class="bg-gray-200 text-gray-700 py-2 px-5 rounded-xl hover:bg-gray-300">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showDeleteModal && $courElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-semibold text-red-600 mb-3">Supprimer le cours</h3>
                <p class="mb-6 text-gray-600">Voulez-vous supprimer <strong>{{ $courElement->name }}</strong> ? Action irréversible.</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="deleteCour" class="bg-red-600 text-white py-2 px-5 rounded-xl hover:bg-red-700">Oui, supprimer</button>
                    <button wire:click="closeModal" class="bg-gray-200 text-gray-700 py-2 px-5 rounded-xl hover:bg-gray-300">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ════ LISTE ════ --}}
    <div class="bg-white rounded-2xl shadow-lg p-8 mt-10">

        {{-- Titre + recherche Alpine (client-side) --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6"
             x-data="coursSearch()" x-init="init()">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-semibold text-gray-800">Liste des Cours</h2>
                <span class="bg-indigo-100 text-indigo-700 text-sm font-semibold px-3 py-0.5 rounded-full"
                      x-text="visibleCount">{{ count($cours) }}</span>
            </div>
            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                    </svg>
                </div>
                <input x-model="query" x-on:input="filter()"
                    type="text"
                    placeholder="Rechercher par nom, code, filière, UE, enseignant…"
                    class="w-full pl-9 pr-9 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500"/>
                <button x-show="query" x-on:click="query=''; filter()" type="button"
                    class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-600 mr-1">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filtres
                    @if ($this->activeFiltersCount > 0)
                        <span class="bg-indigo-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $this->activeFiltersCount }}</span>
                    @endif
                </div>

                <div class="flex flex-col gap-1 min-w-[160px]">
                    <label class="text-xs font-medium text-gray-500">Filière</label>
                    <select wire:model.live="filter_filiere_id"
                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">Toutes les filières</option>
                        @foreach ($filieres as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 min-w-[160px]">
                    <label class="text-xs font-medium text-gray-500">Spécialité</label>
                    <select wire:model.live="filter_specialite_id"
                        @disabled(!$filter_filiere_id)
                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
                        <option value="">Toutes</option>
                        @foreach ($filter_specialites as $sp)
                            <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 min-w-[140px]">
                    <label class="text-xs font-medium text-gray-500">Statut</label>
                    <select wire:model.live="filter_statut"
                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">Tous</option>
                        <option value="pending">En attente</option>
                        <option value="Success">Actif</option>
                        <option value="completed">Terminé</option>
                    </select>
                </div>

                @if ($this->activeFiltersCount > 0)
                    <button wire:click="resetFilters"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 self-end">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Réinitialiser
                    </button>
                @endif
            </div>

            {{-- Badges filtres actifs --}}
            @if ($this->activeFiltersCount > 0)
                <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-200">
                    @if ($filter_filiere_id)
                        @php $fl = $filieres->firstWhere('id', $filter_filiere_id)?->name ?? '' @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 text-indigo-700 text-xs rounded-full font-medium">
                            Filière : {{ $fl }} <button wire:click="$set('filter_filiere_id', null)" class="ml-1 hover:text-indigo-900">×</button>
                        </span>
                    @endif
                    @if ($filter_specialite_id)
                        @php $sl = $filter_specialites->firstWhere('id', $filter_specialite_id)?->name ?? '' @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 text-indigo-700 text-xs rounded-full font-medium">
                            Spécialité : {{ $sl }} <button wire:click="$set('filter_specialite_id', null)" class="ml-1 hover:text-indigo-900">×</button>
                        </span>
                    @endif
                    @if ($filter_statut !== '')
                        @php $stl = ['pending'=>'En attente','Success'=>'Actif','completed'=>'Terminé'][$filter_statut] ?? $filter_statut @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 text-indigo-700 text-xs rounded-full font-medium">
                            {{ $stl }} <button wire:click="$set('filter_statut', '')" class="ml-1 hover:text-indigo-900">×</button>
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Tableau --}}
        @if (count($cours) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Cours</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Filière</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Spécialité</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">UE</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Examen</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Cr.</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">H</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Enseignant</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Statut</th>
                            <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cours as $cour)
                            <tr wire:key="cour-{{ $cour->id }}"
                                data-cour-row
                                data-search="{{ mb_strtolower(implode(' ', [
                                    $cour->name ?? '',
                                    $cour->code ?? '',
                                    $cour->filiere->name ?? '',
                                    $cour->specialite->name ?? '',
                                    $cour->ue->name ?? '',
                                    $cour->responsable->name ?? '',
                                    $cour->examen->titre ?? ''
                                ])) }}"
                                class="bg-gray-50 hover:bg-indigo-50 transition duration-150">
                                <td class="p-4 font-medium text-gray-900">{{ $cour->name }}</td>
                                <td class="p-4 text-gray-600 text-sm">{{ $cour->filiere->name ?? '—' }}</td>
                                <td class="p-4 text-gray-600 text-sm">{{ $cour->specialite->name ?? '—' }}</td>
                                <td class="p-4 text-gray-600 text-sm">{{ $cour->ue->name ?? '—' }}</td>
                                <td class="p-4">
                                    @if ($cour->examen)
                                        <span class="inline-flex items-center gap-1 text-xs bg-purple-50 text-purple-700 px-2 py-1 rounded-full font-medium">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            {{ $cour->examen->titre }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="p-4 text-gray-600 text-sm">{{ $cour->credit ?? 0 }}</td>
                                <td class="p-4 text-gray-600 text-sm">{{ $cour->hour_number ?? '—' }}</td>
                                <td class="p-4 text-gray-600 text-sm">{{ $cour->responsable->name ?? '—' }}</td>
                                <td class="p-4">
                                    @if ($cour->status === 'pending')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                    @elseif ($cour->status === 'Success')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Terminé</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1">
                                        <button wire:click="functionShowDetailsModal({{ $cour->id }})" class="bouton-icone text-sky-600 hover:bg-sky-50" title="Détails" aria-label="Détails"><x-icone-action nom="voir" /></button>
                                        @if ($cour->status === 'pending')
                                            <button wire:click="functionShowActivateModal({{ $cour->id }})" class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Activer" aria-label="Activer"><x-icone-action nom="activer" /></button>
                                        @elseif ($cour->status === 'Success')
                                            <button wire:click="functionShowDeactivateModal({{ $cour->id }})" class="bouton-icone text-amber-600 hover:bg-amber-50" title="Désactiver" aria-label="Désactiver"><x-icone-action nom="desactiver" /></button>
                                        @endif
                                        <button wire:click="functionShowEditModal({{ $cour->id }})" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Modifier" aria-label="Modifier"><x-icone-action nom="modifier" /></button>
                                        <button wire:click="functionShowDeleteModal({{ $cour->id }})" class="bouton-icone text-red-600 hover:bg-red-50" title="Supprimer" aria-label="Supprimer"><x-icone-action nom="supprimer" /></button>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 font-medium">Aucun cours ne correspond à vos critères.</p>
                @if ($this->activeFiltersCount > 0)
                    <button wire:click="resetFilters" class="mt-3 text-indigo-600 hover:underline text-sm">Réinitialiser les filtres</button>
                @endif
            </div>
        @endif
    </div>

    {{-- Toast --}}
    @if ($showNotification)
        <div class="fixed top-6 right-6 z-50 max-w-sm w-full"
             x-data="{ show: true }"
             x-show="show" x-transition
             x-init="setTimeout(() => { show = false; $wire.set('showNotification', false) }, 4000)">
            <div class="flex items-center gap-3 p-4 rounded-xl shadow-lg border-l-4
                {{ $notificationType === 'success' ? 'bg-green-50 border-green-500 text-green-800' : 'bg-red-50 border-red-500 text-red-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-sm font-medium">{{ $notificationMessage }}</span>
            </div>
        </div>
    @endif

</div>

<script>
function coursSearch() {
    return {
        query: '',
        visibleCount: 0,
        init() {
            this.$nextTick(() => this.updateCount());
        },
        filter() {
            const q = this.query.toLowerCase().trim();
            document.querySelectorAll('tr[data-cour-row]').forEach(row => {
                const haystack = row.dataset.search || '';
                row.style.display = (!q || haystack.includes(q)) ? '' : 'none';
            });
            this.updateCount();
        },
        updateCount() {
            this.visibleCount = document.querySelectorAll(
                'tr[data-cour-row]:not([style*="display: none"])'
            ).length;
        }
    }
}
</script>
@endvolt
</x-layouts.app>