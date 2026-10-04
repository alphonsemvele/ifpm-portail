<?php 

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Ue;
use App\Models\Cour;
use App\Models\Examen;
use App\Models\User;
use App\Models\Note;

name('filiere.examens');
middleware(['auth', 'verified']);

new class extends Component {
    public $filiere;
    public $hasFiliere = false;

    public $selectedUeId = null;
    public $selectedCoursId = null;
    public $selectedExamenId = null;

    public $ues = [];
    public $cours = [];
    public $examens = [];
    public $etudiants = [];
    public $notes = [];

    public function mount()
    {
        $user = auth()->user();

        if ($user->filiere_id && $user->filiere) {
            $this->hasFiliere = true;
            $this->filiere = $user->filiere;

            $this->ues = Ue::where('filiere_id', $this->filiere->id)
                ->where('status', 'Success')
                ->orderBy('name')
                ->get();
        }
    }

    public function selectUe($ueId)
    {
        $this->resetExcept(['filiere', 'hasFiliere', 'ues']);
        $this->selectedUeId = $ueId;

        $this->cours = Cour::where('ue_id', $ueId)
            ->where('status', '!=', 'failed')
            ->orderBy('name')
            ->get();

        // Pas de réinitialisation des examens ici → ils restent globaux
    }

    public function selectCours($coursId)
    {
        $this->selectedCoursId = $coursId;
        $this->selectedExamenId = null;

        // On charge TOUS les examens (pas dépendants du cours choisi)
        $this->examens = Examen::orderBy('date', 'desc')->get();
    }

    public function selectExamen($examenId)
    {
        $this->selectedExamenId = $examenId;

        $this->etudiants = User::where('filiere_id', $this->filiere->id)
            ->where('role', 'student')
            ->where('status', 'Success')
            ->orderBy('name')
            ->get();

        $this->notes = [];
        foreach ($this->etudiants as $etudiant) {
            $note = Note::where('examen_id', $examenId)
                        ->where('etudiant_id', $etudiant->id)
                        ->first();

            $this->notes[$etudiant->id] = [
                'cc'   => $note ? (float) $note->cc   : 0.0,
                'exam' => $note ? (float) $note->exam : 0.0,
            ];
        }
    }

    public function saveNotes()
    {
        if (!$this->selectedExamenId) return;

        foreach ($this->notes as $etudiantId => $noteData) {
            Note::updateOrCreate(
                ['examen_id' => $this->selectedExamenId, 'etudiant_id' => $etudiantId],
                [
                    'cc'   => (float) ($noteData['cc']   ?? 0),
                    'exam' => (float) ($noteData['exam'] ?? 0),
                ]
            );
        }

        $this->dispatch('notify', ['message' => 'Notes enregistrées !', 'type' => 'success']);
    }

    public function getMoyenne($etudiantId)
    {
        if (!$this->selectedCoursId) return 0;

        $cours = Cour::find($this->selectedCoursId);

        $cc   = (float) ($this->notes[$etudiantId]['cc']   ?? 0);
        $exam = (float) ($this->notes[$etudiantId]['exam'] ?? 0);

        $creditCc   = (float) ($cours->credit_cc   ?? 0.4);
        $creditExam = (float) ($cours->credit_exam ?? 0.6);

        return round(($cc * $creditCc) + ($exam * $creditExam), 2);
    }
};

?>

<x-layouts.app header="true">
    @volt
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if(!$hasFiliere)
            <div class="min-h-[60vh] flex items-center justify-center">
                <div class="bg-white rounded-2xl shadow-xl p-10 max-w-lg text-center">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Aucune filière attribuée</h2>
                    <p class="text-gray-600">Vous n'êtes pas gestionnaire d'une filière.</p>
                </div>
            </div>
        @else

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Examens de la Filière</h1>
                    <p class="text-gray-500">{{ $filiere->name }}</p>
                </div>
            </div>

            <!-- Sélection UE -->
            <div class="bg-white rounded-xl shadow p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Choisir une UE</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($this->ues as $ue)
                        <button wire:click="selectUe({{ $ue->id }})"
                                class="p-4 border rounded-lg hover:bg-indigo-50 {{ $selectedUeId == $ue->id ? 'bg-indigo-100 border-indigo-500' : '' }}">
                            <div class="font-medium">{{ $ue->code }} - {{ $ue->name }}</div>
                            <div class="text-sm text-gray-500">{{ $ue->cours->count() }} cours</div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Sélection Cours -->
            @if($selectedUeId)
                <div class="bg-white rounded-xl shadow p-6 mb-8">
                    <h2 class="text-xl font-semibold mb-4">Cours de l'UE</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($this->cours as $cour)
                            <button wire:click="selectCours({{ $cour->id }})"
                                    class="p-4 border rounded-lg hover:bg-indigo-50 {{ $selectedCoursId == $cour->id ? 'bg-indigo-100 border-indigo-500' : '' }}">
                                <div class="font-medium">{{ $cour->name }}</div>
                                <div class="text-sm text-gray-500">{{ $cour->code }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Sélection Examen : TOUS les examens, indépendamment du cours -->
            @if($selectedCoursId)
                <div class="bg-white rounded-xl shadow p-6 mb-8">
                    <h2 class="text-xl font-semibold mb-4">Tous les Examens</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($this->examens as $examen)
                            <button wire:click="selectExamen({{ $examen->id }})"
                                    class="p-4 border rounded-lg hover:bg-indigo-50 {{ $selectedExamenId == $examen->id ? 'bg-indigo-100 border-indigo-500' : '' }}">
                                <div class="font-medium">{{ $examen->titre ?? 'Examen sans titre' }}</div>
                                <div class="text-sm text-gray-500">{{ $examen->date?->format('d/m/Y') ?? '—' }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Tableau des notes -->
            @if($selectedExamenId)
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-semibold mb-6">
                        Notes - Examen : {{ $this->selectedExamen->titre ?? 'Sans titre' }}
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-2">
                            <thead>
                                <tr class="bg-gray-100 rounded-lg">
                                    <th class="p-4 text-sm font-medium text-gray-600">Apprenant</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Matricule</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Note CC (/20)</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Note Examen (/20)</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Moyenne Pondérée</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($this->etudiants as $etudiant)
                                    <tr class="bg-gray-50 rounded-lg">
                                        <td class="p-4 font-medium">{{ $etudiant->name }} {{ $etudiant->lastname ?? '' }}</td>
                                        <td class="p-4 font-mono">{{ $etudiant->matricule ?? '—' }}</td>
                                        <td class="p-4">
                                            <input type="number" step="0.25" min="0" max="20"
                                                   wire:model.live.debounce.500ms="notes.{{ $etudiant->id }}.cc"
                                                   class="w-20 p-2 border rounded text-center">
                                        </td>
                                        <td class="p-4">
                                            <input type="number" step="0.25" min="0" max="20"
                                                   wire:model.live.debounce.500ms="notes.{{ $etudiant->id }}.exam"
                                                   class="w-20 p-2 border rounded text-center">
                                        </td>
                                        <td class="p-4 font-medium text-center">
                                            {{ number_format($this->getMoyenne($etudiant->id), 2) }}
                                            <span class="{{ $this->getMoyenne($etudiant->id) >= 10 ? 'text-green-600' : 'text-red-600' }}">
                                                / 20
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-gray-500">
                                            Aucun apprenant dans cette filière.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-end space-x-4">
                        <button wire:click="$set('selectedExamenId', null)" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300">
                            Retour aux examens
                        </button>
                        <button wire:click="saveNotes" class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700">
                            Enregistrer les notes
                        </button>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endvolt
</x-layouts.app>