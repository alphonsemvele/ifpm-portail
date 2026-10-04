<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Cycle;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\Ue;
use App\Models\Cour;
use App\Models\Examen;
use App\Models\Note;
use App\Models\User;

name('admin.notes.index');
middleware(['auth', 'verified']);

new class extends Component {

    // ── Filtres ───────────────────────────────────────────────────────────────
    public $cycle_id       = null;
    public $departement_id = null;
    public $filiere_id     = null;
    public $specialite_id  = null;
    public $ue_id          = null;
    public $cours_id       = null;
    public $examen_id      = null;

    // ── Listes ────────────────────────────────────────────────────────────────
    public $cycles       = [];
    public $departements = [];
    public $filieres     = [];
    public $specialites  = [];
    public $ues          = [];
    public $cours_list   = [];
    public $examens      = [];

    // ── Données ───────────────────────────────────────────────────────────────
    public $etudiants  = [];
    public array $notes = [];

    // ── UI ────────────────────────────────────────────────────────────────────
    public bool $filtersOpen = true;

    // ── Notification ──────────────────────────────────────────────────────────
    public bool   $showNotification    = false;
    public string $notificationMessage = '';
    public string $notificationType    = 'success';

    public function mount(): void
    {
        $this->cycles       = Cycle::where('status', 'Success')->orderBy('name')->get();
        $this->departements = collect();
        $this->filieres     = collect();
        $this->specialites  = collect();
        $this->ues          = collect();
        $this->cours_list   = collect();
        $this->examens      = collect();
    }

    // ── Cascades ──────────────────────────────────────────────────────────────

    public function updatedCycleId($value): void
    {
        $this->departement_id = null;
        $this->filiere_id     = null;
        $this->specialite_id  = null;
        $this->ue_id          = null;
        $this->cours_id       = null;
        $this->examen_id      = null;
        $this->resetEtudiants();

        if ($value) {
            $this->departements = Departement::where('cycle_id', $value)
                ->where('status', 'Success')
                ->orderBy('nom')->get();

            $this->examens = Examen::where('cycle_id', $value)
                ->whereIn('statut', ['ouvert', 'en_cours', 'ferme'])
                ->orderBy('titre')->get();
        } else {
            $this->departements = collect();
            $this->examens      = collect();
        }

        $this->filieres    = collect();
        $this->specialites = collect();
        $this->ues         = collect();
        $this->cours_list  = collect();
    }

    public function updatedDepartementId($value): void
    {
        $this->filiere_id    = null;
        $this->specialite_id = null;
        $this->ue_id         = null;
        $this->cours_id      = null;
        $this->resetEtudiants();

        $this->filieres = $value
            ? Filiere::where('departement_id', $value)
                     ->where('status', 'Success')
                     ->orderBy('name')->get()
            : collect();

        $this->specialites = collect();
        $this->ues         = collect();
        $this->cours_list  = collect();
    }

    public function updatedFiliereId($value): void
    {
        $this->specialite_id = null;
        $this->ue_id         = null;
        $this->cours_id      = null;
        $this->resetEtudiants();

        $this->specialites = $value
            ? Specialite::where('filiere_id', $value)
                        ->where('status', 'Success')
                        ->orderBy('name')->get()
            : collect();

        $this->ues        = collect();
        $this->cours_list = collect();
    }

    public function updatedSpecialiteId($value): void
    {
        $this->ue_id    = null;
        $this->cours_id = null;
        $this->resetEtudiants();

        $this->ues = ($value && $this->examen_id)
            ? Ue::where('specialite_id', $value)
                ->where('examen_id', $this->examen_id)
                ->where('status', 'Success')
                ->orderBy('name')->get()
            : collect();

        $this->cours_list = collect();
    }

    public function updatedExamenId($value): void
    {
        $this->ue_id    = null;
        $this->cours_id = null;
        $this->resetEtudiants();

        $this->ues = ($value && $this->specialite_id)
            ? Ue::where('specialite_id', $this->specialite_id)
                ->where('examen_id', $value)
                ->where('status', 'Success')
                ->orderBy('name')->get()
            : collect();

        $this->cours_list = collect();
    }

    public function updatedUeId($value): void
    {
        $this->cours_id = null;
        $this->resetEtudiants();

        $this->cours_list = $value
            ? Cour::where('ue_id', $value)
                  ->where('status', 'Success')
                  ->orderBy('name')->get()
            : collect();
    }

    public function updatedCoursId($value): void
    {
        $this->resetEtudiants();
        if ($value && $this->specialite_id && $this->examen_id) {
            $this->chargerEtudiantsEtNotes();
        }
    }

    // ── Setter note individuelle ──────────────────────────────────────────────

    public function setNote(int $etudiantId, string $type, $value): void
    {
        $this->notes[$etudiantId][$type] =
            ($value !== '' && $value !== null) ? (float) $value : null;
    }

    // ── Chargement ───────────────────────────────────────────────────────────

    private function resetEtudiants(): void
    {
        $this->etudiants = [];
        $this->notes     = [];
    }

    private function chargerEtudiantsEtNotes(): void
    {
        $this->etudiants = User::where('specialite_id', $this->specialite_id)
            ->whereIn('role', ['student', 'etudiant'])
            ->where('status', 'Success')
            ->orderBy('name')->get();

        $existing = Note::where('examen_id', $this->examen_id)
            ->whereIn('etudiant_id', $this->etudiants->pluck('id'))
            ->where('cours_id', $this->cours_id)
            ->get()
            ->keyBy('etudiant_id');

        $this->notes = [];
        foreach ($this->etudiants as $etudiant) {
            $note = $existing->get($etudiant->id);
            $this->notes[$etudiant->id] = [
                'cc'   => $note?->cc   ?? null,
                'exam' => $note?->exam ?? null,
            ];
        }
    }

    // ── Sauvegarde ───────────────────────────────────────────────────────────

    public function saveNotes(): void
    {
        if (!$this->examen_id || empty($this->etudiants) || !$this->cours_id) return;

        $rules = [];
        foreach ($this->etudiants as $etudiant) {
            $rules["notes.{$etudiant->id}.cc"]   = 'nullable|numeric|min:0|max:20';
            $rules["notes.{$etudiant->id}.exam"]  = 'nullable|numeric|min:0|max:20';
        }
        $this->validate($rules, [], [
            'notes.*.cc'   => 'Note CC',
            'notes.*.exam' => 'Note Examen',
        ]);

        try {
            foreach ($this->etudiants as $etudiant) {
                $cc   = $this->notes[$etudiant->id]['cc']   ?? null;
                $exam = $this->notes[$etudiant->id]['exam'] ?? null;

                Note::updateOrCreate(
                    [
                        'examen_id'   => $this->examen_id,
                        'etudiant_id' => $etudiant->id,
                        'cours_id'    => $this->cours_id,
                    ],
                    [
                        'cc'   => ($cc   !== null && $cc   !== '') ? (float) $cc   : null,
                        'exam' => ($exam !== null && $exam !== '') ? (float) $exam : null,
                    ]
                );
            }

            $this->notify('Notes enregistrées avec succès !', 'success');

        } catch (\Exception $e) {
            logger('Erreur saveNotes: ' . $e->getMessage());
            $this->notify("Erreur lors de l'enregistrement.", 'error');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function notify(string $msg, string $type = 'success'): void
    {
        $this->notificationMessage = $msg;
        $this->notificationType    = $type;
        $this->showNotification    = true;
    }

    public function getMoyenne($etudiant_id): ?float
    {
        $cc   = $this->notes[$etudiant_id]['cc']   ?? null;
        $exam = $this->notes[$etudiant_id]['exam'] ?? null;
        if ($cc === null || $cc === '' || $exam === null || $exam === '') return null;
        return round((float) $cc * 0.4 + (float) $exam * 0.6, 2);
    }

    public function getStatut($moyenne): string
    {
        if ($moyenne === null) return 'En attente';
        return $moyenne >= 10 ? 'Passé' : 'Échec';
    }

    public function getStats(): array
    {
        $passe = $echec = $attente = 0;
        foreach ($this->etudiants as $e) {
            $s = $this->getStatut($this->getMoyenne($e->id));
            if ($s === 'Passé')     $passe++;
            elseif ($s === 'Échec') $echec++;
            else                    $attente++;
        }
        $total = count($this->etudiants);
        return compact('passe', 'echec', 'attente', 'total');
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ─── Header ─── --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                Gestion des Notes
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">Saisissez les notes CC et Examen par cours et par apprenant</p>
        </div>
        <button wire:click="$toggle('filtersOpen')"
            class="flex items-center gap-2 text-sm text-indigo-600 bg-indigo-50 px-4 py-2 rounded-xl hover:bg-indigo-100 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            {{ $filtersOpen ? 'Masquer filtres' : 'Afficher filtres' }}
        </button>
    </div>

    {{-- ─── Filtres ─── --}}
    @if ($filtersOpen)
    <div class="bg-white rounded-2xl shadow-lg p-5 mb-6">

        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Sélection
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- 1. Cycle --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Cycle</label>
                <select wire:model.live="cycle_id"
                    class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">— Cycle —</option>
                    @foreach ($cycles as $cycle)
                        <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 2. Examen (dépend du cycle) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    Examen @if(!$cycle_id)<span class="text-gray-300 normal-case font-normal">(cycle d'abord)</span>@endif
                </label>
                <select wire:model.live="examen_id"
                    wire:key="examen-{{ $cycle_id }}"
                    class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white
                           {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                    @disabled(!$cycle_id)>
                    <option value="">— Examen —</option>
                    @foreach ($examens as $examen)
                        <option value="{{ $examen->id }}">{{ $examen->titre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 3. Département (dépend du cycle) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    Département @if(!$cycle_id)<span class="text-gray-300 normal-case font-normal">(cycle d'abord)</span>@endif
                </label>
                <select wire:model.live="departement_id"
                    wire:key="dep-{{ $cycle_id }}"
                    class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white
                           {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                    @disabled(!$cycle_id)>
                    <option value="">— Département —</option>
                    @foreach ($departements as $dep)
                        <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 4. Filière (dépend département) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    Filière @if(!$departement_id)<span class="text-gray-300 normal-case font-normal">(département d'abord)</span>@endif
                </label>
                <select wire:model.live="filiere_id"
                    wire:key="filiere-{{ $departement_id }}"
                    class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white
                           {{ !$departement_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                    @disabled(!$departement_id)>
                    <option value="">— Filière —</option>
                    @foreach ($filieres as $filiere)
                        <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 5. Spécialité (dépend filière) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    Spécialité @if(!$filiere_id)<span class="text-gray-300 normal-case font-normal">(filière d'abord)</span>@endif
                </label>
                <select wire:model.live="specialite_id"
                    wire:key="specialite-{{ $filiere_id }}"
                    class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white
                           {{ !$filiere_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                    @disabled(!$filiere_id)>
                    <option value="">— Spécialité —</option>
                    @foreach ($specialites as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 6. UE (dépend spécialité + examen) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    UE @if(!$specialite_id || !$examen_id)<span class="text-gray-300 normal-case font-normal">(spécialité + examen d'abord)</span>@endif
                </label>
                <select wire:model.live="ue_id"
                    wire:key="ue-{{ $specialite_id }}-{{ $examen_id }}"
                    class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white
                           {{ (!$specialite_id || !$examen_id) ? 'opacity-50 cursor-not-allowed' : '' }}"
                    @disabled(!$specialite_id || !$examen_id)>
                    <option value="">— UE —</option>
                    @foreach ($ues as $ue)
                        <option value="{{ $ue->id }}">{{ $ue->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 7. Cours (dépend UE) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    Cours <span class="text-red-400">*</span>
                    @if(!$ue_id)<span class="text-gray-300 normal-case font-normal">(UE d'abord)</span>@endif
                </label>
                <select wire:model.live="cours_id"
                    wire:key="cours-{{ $ue_id }}"
                    class="w-full text-sm px-3 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white transition
                           {{ !$ue_id ? 'opacity-50 cursor-not-allowed border-gray-200' : '' }}
                           {{ $cours_id ? 'border-indigo-400 ring-1 ring-indigo-300' : 'border-gray-200' }}"
                    @disabled(!$ue_id)>
                    <option value="">— Cours —</option>
                    @foreach ($cours_list as $cours)
                        <option value="{{ $cours->id }}">{{ $cours->name }}</option>
                    @endforeach
                </select>
                @if ($ue_id && count($cours_list) === 0)
                    <p class="text-xs text-amber-500 mt-1 italic">Aucun cours actif pour cette UE.</p>
                @endif
            </div>

        </div>
    </div>
    @endif

    {{-- ─── Bandeau contexte actif ─── --}}
    @if ($cours_id && $examen_id && $specialite_id)
        @php
            $coursActif  = collect($cours_list)->firstWhere('id', $cours_id);
            $examenActif = collect($examens)->firstWhere('id', $examen_id);
        @endphp
        <div class="flex flex-wrap items-center gap-3 mb-4 bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-2.5 text-sm">
            <span class="text-indigo-600 font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                {{ $coursActif?->name ?? '—' }}
            </span>
            <span class="text-gray-400">·</span>
            <span class="bg-white border border-indigo-200 text-indigo-600 text-xs px-2.5 py-1 rounded-full font-medium">
                {{ $examenActif?->titre ?? '—' }}
            </span>
        </div>
    @endif

    {{-- ─── Tableau ─── --}}
    @if ($examen_id && $specialite_id && $cours_id && count($etudiants) > 0)

        @php $stats = $this->getStats(); @endphp

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl shadow p-5 text-center border-l-4 border-indigo-400">
                <p class="text-xs text-gray-500 mb-1 uppercase tracking-wide">Apprenants</p>
                <p class="text-3xl font-bold text-indigo-600">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-5 text-center border-l-4 border-green-400">
                <p class="text-xs text-gray-500 mb-1 uppercase tracking-wide">Passés</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['passe'] }}</p>
                <p class="text-xs text-green-400 mt-1">{{ $stats['total'] > 0 ? round(($stats['passe'] / $stats['total']) * 100) : 0 }}%</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-5 text-center border-l-4 border-red-400">
                <p class="text-xs text-gray-500 mb-1 uppercase tracking-wide">Échecs</p>
                <p class="text-3xl font-bold text-red-600">{{ $stats['echec'] }}</p>
                <p class="text-xs text-red-400 mt-1">{{ $stats['total'] > 0 ? round(($stats['echec'] / $stats['total']) * 100) : 0 }}%</p>
            </div>
            <div class="bg-white rounded-2xl shadow p-5 text-center border-l-4 border-yellow-400">
                <p class="text-xs text-gray-500 mb-1 uppercase tracking-wide">En attente</p>
                <p class="text-3xl font-bold text-yellow-500">{{ $stats['attente'] }}</p>
                <p class="text-xs text-yellow-400 mt-1">{{ $stats['total'] > 0 ? round(($stats['attente'] / $stats['total']) * 100) : 0 }}%</p>
            </div>
        </div>

        {{-- Tableau --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-indigo-600 text-white text-xs uppercase tracking-wider">
                        <th class="px-4 py-3 text-left w-8">#</th>
                        <th class="px-4 py-3 text-left">Apprenant</th>
                        <th class="px-4 py-3 text-center">Matricule</th>
                        <th class="px-4 py-3 text-center min-w-[140px]">
                            <span class="flex items-center justify-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-blue-300 inline-block"></span>
                                CC <span class="font-normal opacity-75">/ 20</span>
                            </span>
                        </th>
                        <th class="px-4 py-3 text-center min-w-[140px]">
                            <span class="flex items-center justify-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-purple-300 inline-block"></span>
                                Examen <span class="font-normal opacity-75">/ 20</span>
                            </span>
                        </th>
                        <th class="px-4 py-3 text-center">Statut</th>
                    </tr>
                </thead>
                <tbody wire:key="tbody-{{ $cours_id }}-{{ $examen_id }}">
                    @foreach ($etudiants as $i => $etudiant)
                        @php
                            $ccVal   = $notes[$etudiant->id]['cc']   ?? null;
                            $examVal = $notes[$etudiant->id]['exam'] ?? null;
                            $hasCc   = $ccVal   !== null && $ccVal   !== '';
                            $hasExam = $examVal !== null && $examVal !== '';
                            $moyenne = $this->getMoyenne($etudiant->id);
                            $statut  = $this->getStatut($moyenne);
                        @endphp
                        <tr wire:key="row-{{ $cours_id }}-{{ $etudiant->id }}"
                            class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-indigo-50 transition">

                            <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $i + 1 }}</td>

                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold flex-shrink-0">
                                        {{ strtoupper(substr($etudiant->name, 0, 1)) }}
                                    </div>
                                    <p class="font-semibold text-gray-800">{{ strtoupper($etudiant->name) }} {{ $etudiant->lastname }}</p>
                                </div>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-lg">
                                    {{ $etudiant->matricule ?? '—' }}
                                </span>
                            </td>

                            {{-- CC --}}
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <input type="number"
                                        value="{{ $ccVal ?? '' }}"
                                        wire:change="setNote({{ $etudiant->id }}, 'cc', $event.target.value)"
                                        min="0" max="20" step="0.25" placeholder="—"
                                        class="w-20 text-center px-2 py-1.5 text-sm border rounded-lg transition
                                               focus:outline-none focus:ring-2 focus:ring-blue-400
                                               {{ $hasCc
                                                   ? ($ccVal >= 10 ? 'border-green-300 bg-green-50 text-green-700 font-semibold'
                                                                   : 'border-red-300 bg-red-50 text-red-600 font-semibold')
                                                   : 'border-gray-200 bg-white text-gray-700' }}"/>
                                    @if ($hasCc)<span class="text-xs text-gray-400">/20</span>@endif
                                </div>
                                @error("notes.{$etudiant->id}.cc")
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- Exam --}}
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <input type="number"
                                        value="{{ $examVal ?? '' }}"
                                        wire:change="setNote({{ $etudiant->id }}, 'exam', $event.target.value)"
                                        min="0" max="20" step="0.25" placeholder="—"
                                        class="w-20 text-center px-2 py-1.5 text-sm border rounded-lg transition
                                               focus:outline-none focus:ring-2 focus:ring-purple-400
                                               {{ $hasExam
                                                   ? ($examVal >= 10 ? 'border-green-300 bg-green-50 text-green-700 font-semibold'
                                                                     : 'border-red-300 bg-red-50 text-red-600 font-semibold')
                                                   : 'border-gray-200 bg-white text-gray-700' }}"/>
                                    @if ($hasExam)<span class="text-xs text-gray-400">/20</span>@endif
                                </div>
                                @error("notes.{$etudiant->id}.exam")
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </td>

                            {{-- Statut --}}
                            <td class="px-4 py-3 text-center">
                                @if ($statut === 'Passé')
                                    <span class="inline-flex flex-col items-center">
                                        <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Passé</span>
                                        <span class="text-xs text-green-600 font-bold mt-1">{{ $moyenne }}/20</span>
                                    </span>
                                @elseif ($statut === 'Échec')
                                    <span class="inline-flex flex-col items-center">
                                        <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Échec</span>
                                        <span class="text-xs text-red-500 font-bold mt-1">{{ $moyenne }}/20</span>
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">En attente</span>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Bouton bas --}}
        <div class="mt-6 flex justify-end">
            <button wire:click="saveNotes"
                wire:loading.attr="disabled"
                wire:target="saveNotes"
                class="inline-flex items-center gap-2 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-lg transition">
                <svg wire:loading.remove wire:target="saveNotes" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg wire:loading wire:target="saveNotes" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span wire:loading.remove wire:target="saveNotes">Enregistrer les notes</span>
                <span wire:loading wire:target="saveNotes">Enregistrement…</span>
            </button>
        </div>

    @elseif ($examen_id && $specialite_id && $cours_id && count($etudiants) === 0)
        <div class="bg-white rounded-2xl shadow p-12 text-center text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="font-medium text-gray-500">Aucun apprenant trouvé pour cette spécialité.</p>
        </div>

    @else
        <div class="bg-white rounded-2xl shadow p-16 text-center border-2 border-dashed border-gray-200">
            <svg class="w-20 h-20 mx-auto mb-4 text-indigo-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-xl font-medium mb-2 text-gray-500">Les notes apparaîtront ici</p>
            <p class="text-sm text-gray-400">Sélectionnez le cycle, l'examen, la spécialité et le cours pour commencer</p>
        </div>
    @endif

    {{-- ─── Notification toast ─── --}}
    @if ($showNotification)
        <div class="fixed top-6 right-6 z-50 max-w-sm w-full"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => { show = false; $wire.set('showNotification', false) }, 3500)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center gap-3 p-4 rounded-xl shadow-lg border-l-4
                {{ $notificationType === 'success'
                    ? 'bg-green-50 text-green-700 border-green-500'
                    : 'bg-red-50 text-red-700 border-red-500' }}">
                @if ($notificationType === 'success')
                    <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                @endif
                <span class="text-sm font-medium">{{ $notificationMessage }}</span>
            </div>
        </div>
    @endif

</div>
@endvolt
</x-layouts.app>