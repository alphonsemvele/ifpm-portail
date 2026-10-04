<?php 

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Examen;
use App\Models\Cour;
use App\Models\User;
use App\Models\Note;

name('specialite.examens');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $specialite;
    public $hasSpecialite = false;

    public $showCoursModal = false;
    public $selectedExamenId = null;
    public $selectedExamen = null;
    public $cours = [];

    public function mount()
    {
        $user = auth()->user();

        if ($user->specialite_id && $user->specialite && $user->aLeRole('enseignant')) {
            $this->hasSpecialite = true;
            $this->specialite = $user->specialite;
        }
    }

    public function openCoursModal($examenId)
    {
        $this->selectedExamenId = $examenId;
        $this->selectedExamen = Examen::findOrFail($examenId);
        $this->cours = Cour::where('specialite_id', $this->specialite->id)
            ->where('status', '!=', 'failed')
            ->orderBy('name')
            ->get();
        $this->showCoursModal = true;
    }

    public function closeCoursModal()
    {
        $this->showCoursModal = false;
        $this->selectedExamenId = null;
        $this->selectedExamen = null;
        $this->cours = [];
    }

    public function getMesExamensProperty()
    {
        if (!$this->hasSpecialite || !$this->specialite) {
            return collect()->paginate(10);
        }

        return Examen::orderBy('date', 'desc')->paginate(10);
    }
};

?>

<x-layouts.app header="true">
    @volt
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if(!$hasSpecialite)
            <div class="min-h-[60vh] flex items-center justify-center">
                <div class="bg-white rounded-2xl shadow-xl p-10 max-w-lg text-center">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Aucune spécialité attribuée</h2>
                    <p class="text-gray-600 mb-6">Vous n'êtes responsable d'aucune spécialité.</p>
                    <a href="/specialite/profil" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Voir mon profil
                    </a>
                </div>
            </div>
        @else

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Examens de la Spécialité</h1>
                    <p class="text-gray-500">Gérez les notes CC et Examen - {{ $specialite->name }}</p>
                </div>
            </div>

            <!-- Liste des examens -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Examens</h2>

                @if($this->mesExamens->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-2">
                            <thead>
                                <tr class="bg-gray-100 rounded-lg">
                                    <th class="p-4 text-sm font-medium text-gray-600">Titre</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->mesExamens as $examen)
                                    <tr class="bg-gray-50 rounded-lg">
                                        <td class="p-4 font-medium">{{ $examen->titre ?? 'Sans titre' }}</td>
                                        <td class="p-4">{{ $examen->date?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="p-4">
                                            <button wire:click="openCoursModal({{ $examen->id }})"
                                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition duration-200 shadow-sm">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                Entrez les notes
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-center">
                        {{ $this->mesExamens->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-gray-500 text-lg">Aucun examen n'a encore été créé.</p>
                        <p class="text-sm text-gray-400 mt-2">Créez un examen pour qu'il apparaisse ici.</p>
                    </div>
                @endif
            </div>

            <!-- Modal Popup pour liste des cours -->
            @if($showCoursModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                            Cours disponibles pour l'examen : {{ $selectedExamen->titre ?? 'Sans titre' }}
                        </h2>

                        @if($cours->count() > 0)
                            <div class="space-y-4">
                                @foreach($cours as $cour)
                                    <a href="/specialite/examens/notes/{{ $selectedExamen->id }}/{{ $cour->id }}"
                                       class="block p-4 border rounded-lg hover:bg-indigo-50 transition">
                                        <div class="font-medium">{{ $cour->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $cour->code }}</div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-6">Aucun cours trouvé dans votre spécialité.</p>
                        @endif

                        <div class="mt-8 flex justify-end">
                            <button wire:click="closeCoursModal" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endvolt
</x-layouts.app>