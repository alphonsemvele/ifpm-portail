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

name('admin.rattrapage');
middleware(['auth', 'verified']);

new class extends Component {

    // ── Filtres en cascade ────────────────────────────────────────────────────
    public $cycle_id       = null;
    public $departement_id = null;
    public $filiere_id     = null;
    public $specialite_id  = null;
    public $examen_id      = null;
    public $ue_id          = null;
    public $cours_id       = null;

    // ── Listes déroulantes ────────────────────────────────────────────────────
    public $cycles       = [];
    public $departements = [];
    public $filieres     = [];
    public $specialites  = [];
    public $examens      = [];
    public $ues          = [];
    public $cours_list   = [];

    // ── Données tableau ───────────────────────────────────────────────────────
    public $etudiants = [];
    public $notes     = [];   // [etudiant_id => rattrapage_value]

    // ── UI ────────────────────────────────────────────────────────────────────
    public bool   $showNotification    = false;
    public string $notificationMessage = '';
    public string $notificationType    = 'success';
    public string $search              = '';

    public function mount()
    {
        $this->cycles = Cycle::orderBy('name')->get();
    }

    // ── Cascades ──────────────────────────────────────────────────────────────

    public function updatedCycleId($value)
    {
        $this->departement_id = null;
        $this->filiere_id     = null;
        $this->specialite_id  = null;
        $this->examen_id      = null;
        $this->ue_id          = null;
        $this->cours_id       = null;
        $this->resetTableau();

        $this->departements = $value
            ? Departement::where('cycle_id', $value)->orderBy('nom')->get()
            : collect();

        $this->examens      = $value
            ? Examen::where('cycle_id', $value)
                    ->whereIn('statut', ['ouvert', 'en_cours', 'ferme'])
                    ->orderBy('titre')->get()
            : collect();

        $this->filieres   = collect();
        $this->specialites = collect();
        $this->ues         = collect();
        $this->cours_list  = collect();
    }

    public function updatedDepartementId($value)
    {
        $this->filiere_id    = null;
        $this->specialite_id = null;
        $this->ue_id         = null;
        $this->cours_id      = null;
        $this->resetTableau();

        $this->filieres = $value
            ? Filiere::where('departement_id', $value)
                     ->where('status', 'Success')
                     ->orderBy('name')->get()
            : collect();

        $this->specialites = collect();
        $this->ues         = collect();
        $this->cours_list  = collect();
    }

    public function updatedFiliereId($value)
    {
        $this->specialite_id = null;
        $this->ue_id         = null;
        $this->cours_id      = null;
        $this->resetTableau();

        $this->specialites = $value
            ? Specialite::where('filiere_id', $value)
                        ->where('status', 'Success')
                        ->orderBy('name')->get()
            : collect();

        $this->ues        = collect();
        $this->cours_list = collect();
    }

    public function updatedSpecialiteId($value)
    {
        $this->ue_id      = null;
        $this->cours_id   = null;
        $this->resetTableau();

        $this->ues = ($value && $this->examen_id)
            ? Ue::where('specialite_id', $value)
                ->where('examen_id', $this->examen_id)
                ->where('status', 'Success')
                ->orderBy('code')->get()
            : collect();

        $this->cours_list = collect();
    }

    public function updatedExamenId($value)
    {
        $this->ue_id      = null;
        $this->cours_id   = null;
        $this->resetTableau();

        $this->ues = ($value && $this->specialite_id)
            ? Ue::where('specialite_id', $this->specialite_id)
                ->where('examen_id', $value)
                ->where('status', 'Success')
                ->orderBy('code')->get()
            : collect();

        $this->cours_list = collect();
    }

    public function updatedUeId($value)
    {
        $this->cours_id = null;
        $this->resetTableau();

        $this->cours_list = $value
            ? Cour::where('ue_id', $value)
                  ->where('status', 'Success')
                  ->orderBy('name')->get()
            : collect();
    }

    public function updatedCoursId($value)
    {
        $this->resetTableau();
        if ($value && $this->specialite_id && $this->examen_id) {
            $this->chargerEtudiants();
        }
    }

    // ── Chargement des apprenants ──────────────────────────────────────────────

    private function chargerEtudiants(): void
    {
        $this->etudiants = User::where('specialite_id', $this->specialite_id)
            ->whereIn('role', ['student', 'etudiant'])
            ->where('status', 'Success')
            ->orderBy('name')
            ->get();

        $existing = Note::where('examen_id', $this->examen_id)
            ->where('cours_id', $this->cours_id)
            ->whereIn('etudiant_id', $this->etudiants->pluck('id'))
            ->get()
            ->keyBy('etudiant_id');

        $this->notes = [];
        foreach ($this->etudiants as $etudiant) {
            $this->notes[$etudiant->id] = $existing->has($etudiant->id)
                ? $existing[$etudiant->id]->rattrapage
                : null;
        }
    }

    // ── Sauvegarde ────────────────────────────────────────────────────────────

    public function saveNotes()
    {
        if (!$this->examen_id || !$this->cours_id || empty($this->etudiants)) return;

        $rules = [];
        foreach ($this->etudiants as $etudiant) {
            $rules["notes.{$etudiant->id}"] = 'nullable|numeric|min:0|max:20';
        }
        $this->validate($rules, [], ['notes.*' => 'Note rattrapage']);

        try {
            foreach ($this->notes as $etudiant_id => $val) {
                Note::updateOrCreate(
                    [
                        'examen_id'   => $this->examen_id,
                        'etudiant_id' => $etudiant_id,
                        'cours_id'    => $this->cours_id,
                    ],
                    [
                        'rattrapage' => ($val !== null && $val !== '') ? $val : null,
                    ]
                );
            }

            $this->notify('Notes de rattrapage enregistrées avec succès !', 'success');

        } catch (\Exception $e) {
            logger('Erreur saveNotes rattrapage: ' . $e->getMessage());
            $this->notify('Erreur lors de l\'enregistrement.', 'error');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resetTableau(): void
    {
        $this->etudiants = [];
        $this->notes     = [];
    }

    private function notify(string $msg, string $type = 'success'): void
    {
        $this->notificationMessage = $msg;
        $this->notificationType    = $type;
        $this->showNotification    = true;
    }

    public function getNotesSaisiesCount(): int
    {
        return collect($this->notes)->filter(fn($v) => $v !== null && $v !== '')->count();
    }

    public function getEtudiantsFiltres()
    {
        if (empty($this->search)) return collect($this->etudiants);
        $s = mb_strtolower($this->search);
        return collect($this->etudiants)->filter(
            fn($e) => str_contains(mb_strtolower($e->name . ' ' . $e->lastname . ' ' . $e->matricule), $s)
        );
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ─── Header ─── --}}
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Rattrapage</h1>
            <p class="text-gray-500">Saisissez les notes de rattrapage par cours et examen</p>
        </div>
        <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-500 rounded-2xl mb-4 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </div>
    </div>

    {{-- ─── Filtres en cascade ─── --}}
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-widest mb-5 flex items-center gap-2">
            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Sélection
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Cycle --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Cycle</label>
                <select wire:model.live="cycle_id"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-400 bg-white">
                    <option value="">— Cycle —</option>
                    @foreach ($cycles as $cycle)
                        <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Examen (dépend du cycle) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    Examen @if(!$cycle_id)<span class="text-gray-300 normal-case">(cycle d'abord)</span>@endif
                </label>
                <select wire:model.live="examen_id"
                    wire:key="examen-{{ $cycle_id }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-400 bg-white
                           {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                    @disabled(!$cycle_id)>
                    <option value="">— Examen —</option>
                    @foreach ($examens as $examen)
                        <option value="{{ $examen->id }}">{{ $examen->titre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Département (dépend du cycle) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    Département @if(!$cycle_id)<span class="text-gray-300 normal-case">(cycle d'abord)</span>@endif
                </label>
                <select wire:model.live="departement_id"
                    wire:key="departement-{{ $cycle_id }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-400 bg-white
                           {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                    @disabled(!$cycle_id)>
                    <option value="">— Département —</option>
                    @foreach ($departements as $dep)
                        <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filière --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    Filière @if(!$departement_id)<span class="text-gray-300 normal-case">(département d'abord)</span>@endif
                </label>
                <select wire:model.live="filiere_id"
                    wire:key="filiere-{{ $departement_id }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-400 bg-white
                           {{ !$departement_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                    @disabled(!$departement_id)>
                    <option value="">— Filière —</option>
                    @foreach ($filieres as $filiere)
                        <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Spécialité --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    Spécialité @if(!$filiere_id)<span class="text-gray-300 normal-case">(filière d'abord)</span>@endif
                </label>
                <select wire:model.live="specialite_id"
                    wire:key="specialite-{{ $filiere_id }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-400 bg-white
                           {{ !$filiere_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                    @disabled(!$filiere_id)>
                    <option value="">— Spécialité —</option>
                    @foreach ($specialites as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- UE --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    UE @if(!$specialite_id || !$examen_id)<span class="text-gray-300 normal-case">(spécialité + examen d'abord)</span>@endif
                </label>
                <select wire:model.live="ue_id"
                    wire:key="ue-{{ $specialite_id }}-{{ $examen_id }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-400 bg-white
                           {{ (!$specialite_id || !$examen_id) ? 'opacity-50 cursor-not-allowed' : '' }}"
                    @disabled(!$specialite_id || !$examen_id)>
                    <option value="">— UE —</option>
                    @foreach ($ues as $ue)
                        <option value="{{ $ue->id }}">{{ $ue->code }} — {{ $ue->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Cours --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                    Cours @if(!$ue_id)<span class="text-gray-300 normal-case">(UE d'abord)</span>@endif
                </label>
                <select wire:model.live="cours_id"
                    wire:key="cours-{{ $ue_id }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-400 bg-white
                           {{ !$ue_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                    @disabled(!$ue_id)>
                    <option value="">— Cours —</option>
                    @foreach ($cours_list as $cours)
                        <option value="{{ $cours->id }}">{{ $cours->name }}</option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    {{-- ─── Tableau des notes ─── --}}
    @if (!empty($etudiants))

        {{-- Barre d'action --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">

            {{-- Compteur --}}
            <div class="flex items-center gap-3">
                <div class="bg-orange-50 border border-orange-200 rounded-xl px-4 py-2 flex items-center gap-2">
                    <span class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Apprenants</span>
                    <span class="text-lg font-bold text-orange-600">{{ count($etudiants) }}</span>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-2 flex items-center gap-2">
                    <span class="text-xs font-semibold text-green-600 uppercase tracking-wide">Saisis</span>
                    <span class="text-lg font-bold text-green-600">{{ $this->getNotesSaisiesCount() }}</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Recherche --}}
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
                    </svg>
                    <input wire:model.live.debounce.200ms="search" type="text"
                        placeholder="Rechercher un apprenant…"
                        class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-400 w-56"/>
                </div>

                {{-- Sauvegarder --}}
                <button wire:click="saveNotes"
                    wire:loading.attr="disabled"
                    wire:target="saveNotes"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl shadow transition">
                    <svg wire:loading.remove wire:target="saveNotes" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    <svg wire:loading wire:target="saveNotes" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="saveNotes">Enregistrer</span>
                    <span wire:loading wire:target="saveNotes">Enregistrement…</span>
                </button>
            </div>
        </div>

        {{-- Tableau --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-orange-500 text-white text-xs uppercase tracking-wider">
                        <th class="px-4 py-3 text-left w-8">#</th>
                        <th class="px-4 py-3 text-left">Apprenant</th>
                        <th class="px-4 py-3 text-center">Matricule</th>
                        <th class="px-4 py-3 text-center">CC</th>
                        <th class="px-4 py-3 text-center">Exam</th>
                        <th class="px-4 py-3 text-center min-w-[140px]">Note Rattrapage</th>
                    </tr>
                </thead>
                <tbody wire:key="tbody-{{ $cours_id }}">
                    @php $idx = 0; @endphp
                    @foreach ($this->getEtudiantsFiltres() as $etudiant)
                        @php
                            $idx++;
                            $noteCC   = null;
                            $noteExam = null;
                            // Récupérer cc et exam existants pour info
                            $existingNote = \App\Models\Note::where('examen_id', $this->examen_id)
                                ->where('cours_id', $this->cours_id)
                                ->where('etudiant_id', $etudiant->id)
                                ->first();
                            $noteCC   = $existingNote?->cc;
                            $noteExam = $existingNote?->exam;

                            $hasRattrapage = isset($notes[$etudiant->id]) && $notes[$etudiant->id] !== null && $notes[$etudiant->id] !== '';
                        @endphp
                        <tr wire:key="row-{{ $cours_id }}-{{ $etudiant->id }}"
                            class="{{ $idx % 2 === 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-orange-50 transition">

                            <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $idx }}</td>

                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-800">{{ strtoupper($etudiant->name) }} {{ $etudiant->lastname }}</div>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-lg">
                                    {{ $etudiant->matricule ?? '—' }}
                                </span>
                            </td>

                            {{-- CC (lecture seule) --}}
                            <td class="px-4 py-3 text-center">
                                @if ($noteCC !== null)
                                    <span class="font-mono text-sm font-semibold
                                        {{ $noteCC >= 10 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ number_format($noteCC, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Exam (lecture seule) --}}
                            <td class="px-4 py-3 text-center">
                                @if ($noteExam !== null)
                                    <span class="font-mono text-sm font-semibold
                                        {{ $noteExam >= 10 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ number_format($noteExam, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Note Rattrapage (saisie) --}}
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <input
                                        wire:model.live="notes.{{ $etudiant->id }}"
                                        type="number"
                                        min="0" max="20" step="0.25"
                                        placeholder="—"
                                        class="w-20 text-center px-2 py-1.5 text-sm border rounded-lg transition
                                               focus:outline-none focus:ring-2 focus:ring-orange-400
                                               {{ $hasRattrapage
                                                   ? ($notes[$etudiant->id] >= 10
                                                       ? 'border-green-300 bg-green-50 text-green-700 font-semibold'
                                                       : 'border-red-300 bg-red-50 text-red-600 font-semibold')
                                                   : 'border-gray-200 bg-white text-gray-700' }}"/>
                                    @if ($hasRattrapage)
                                        <span class="text-xs text-gray-400">/20</span>
                                    @endif
                                </div>
                                @error("notes.{$etudiant->id}")
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>
                    @endforeach

                    @if ($this->getEtudiantsFiltres()->isEmpty())
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">
                                Aucun apprenant ne correspond à la recherche.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Bouton bas de page --}}
        <div class="mt-6 flex justify-end">
            <button wire:click="saveNotes"
                wire:loading.attr="disabled"
                wire:target="saveNotes"
                class="inline-flex items-center gap-2 px-8 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl shadow-lg transition">
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

    @elseif ($cours_id)
        <div class="bg-white rounded-2xl shadow p-12 text-center text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="font-medium text-gray-500">Aucun apprenant trouvé pour cette spécialité.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow p-16 text-center text-gray-400">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <p class="text-xl font-medium mb-2 text-gray-500">Les notes de rattrapage apparaîtront ici</p>
            <p class="text-sm text-gray-400">Sélectionnez le cycle, l'examen, la filière, la spécialité, l'UE et le cours</p>
        </div>
    @endif

    {{-- ─── Notification toast ─── --}}
    @if ($showNotification)
        <div class="fixed top-6 right-6 z-50 max-w-sm w-full"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => { show = false; $wire.set('showNotification', false) }, 4000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="p-4 rounded-xl shadow-lg border-l-4 flex items-center gap-3
                {{ $notificationType === 'success'
                    ? 'bg-green-50 border-green-500 text-green-800'
                    : 'bg-red-50 border-red-500 text-red-800' }}">
                @if ($notificationType === 'success')
                    <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                @endif
                <span class="text-sm font-medium">{{ $notificationMessage }}</span>
            </div>
        </div>
    @endif

</div>
@endvolt
</x-layouts.app>