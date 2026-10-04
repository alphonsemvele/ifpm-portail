<?php 

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Examen;
use App\Models\Cour;
use App\Models\User;
use App\Models\Note;

name('specialite.examens.notes');
middleware(['auth', 'verified']);

new class extends Component {
    public $examen;
    public $cours;
    public $etudiants = [];
    public $notes = [];

    public function mount($examenId = null, $coursId = null)
    {
        abort_if($examenId === null || $coursId === null, 404);

        $user = auth()->user();

        $this->examen = Examen::findOrFail($examenId);
        $this->cours  = Cour::findOrFail($coursId);

        if ($this->cours->specialite_id !== $user->specialite_id) {
            abort(403, 'Accès non autorisé.');
        }

        $this->etudiants = User::where('specialite_id', $user->specialite_id)
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
                'cc'   => (float) ($note ? $note->cc   : 0),
                'exam' => (float) ($note ? $note->exam : 0),
            ];
        }
    }

    public function saveNotes()
    {
        foreach ($this->notes as $etudiantId => $noteData) {
            Note::updateOrCreate(
                [
                    'examen_id'   => $this->examen->id,
                    'etudiant_id' => $etudiantId,
                ],
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
        $cc   = (float) ($this->notes[$etudiantId]['cc']   ?? 0);
        $exam = (float) ($this->notes[$etudiantId]['exam'] ?? 0);

        $creditCc   = (float) ($this->cours->credit_cc   ?? 0.4);
        $creditExam = (float) ($this->cours->credit_exam ?? 0.6);

        return round(($cc * $creditCc) + ($exam * $creditExam), 2);
    }
};

?>

<x-layouts.app header="true">
    @volt
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Saisie des Notes</h1>
                <p class="text-gray-500">Examen : {{ $examen->titre ?? $examen->type_label }} • 
                Cours : {{ $cours->name }} ({{ $cours->code }})</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Notes des apprenants</h2>

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
                        @forelse($etudiants as $etudiant)
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4 font-medium">{{ $etudiant->name }} {{ $etudiant->lastname ?? '' }}</td>
                                <td class="p-4 font-mono">{{ $etudiant->matricule ?? '—' }}</td>
                                <td class="p-4">
                                    <input type="number" step="0.25" min="0" max="20"
                                           wire:model.live.debounce.500ms="notes.{{ $etudiant->id }}.cc"
                                           class="w-20 p-2 border rounded text-center focus:ring-indigo-500">
                                </td>
                                <td class="p-4">
                                    <input type="number" step="0.25" min="0" max="20"
                                           wire:model.live.debounce.500ms="notes.{{ $etudiant->id }}.exam"
                                           class="w-20 p-2 border rounded text-center focus:ring-indigo-500">
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
                                    Aucun apprenant inscrit dans cette spécialité.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="/specialite/examens" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">
                    Retour aux examens
                </a>
                <button wire:click="saveNotes" class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                    Enregistrer les notes
                </button>
            </div>
        </div>

    </div>
@endvolt
</x-layouts.app>