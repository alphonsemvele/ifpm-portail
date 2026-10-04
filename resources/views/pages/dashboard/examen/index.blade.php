<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Examen;

name('examens.index');
middleware(['auth', 'verified']);

new class extends Component {
    public $examens;

    public function mount()
    {
        $user = auth()->user();

        // Les sessions d'examen sont rattachees a un cycle : celui de l'etudiant,
        // ou a defaut celui de sa specialite.
        $cycleId = $user->cycle_id ?? $user->specialite?->cycle_id;

        $this->examens = $cycleId
            ? Examen::where('cycle_id', $cycleId)
                ->where('statut', '!=', 'annule')
                ->orderBy('date')
                ->get()
            : collect();
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Mes examens</h1>
                    <p class="text-gray-500">Sessions d'examen prévues pour votre cycle.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Planning des sessions</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Session</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($examens as $examen)
                                @php
                                    [$libelle, $couleur] = match ($examen->statut) {
                                        'en_cours' => ['En cours', 'bg-amber-100 text-amber-700'],
                                        'ferme' => ['Fermée', 'bg-gray-200 text-gray-700'],
                                        default => ['Ouverte', 'bg-green-100 text-green-700'],
                                    };
                                @endphp
                                <tr class="bg-gray-50 rounded-lg">
                                    <td class="p-4 font-medium text-gray-800">{{ $examen->titre ?? 'Session' }}</td>
                                    <td class="p-4">{{ $examen->date?->format('d/m/Y') ?? 'À préciser' }}</td>
                                    <td class="p-4"><span class="px-2 py-1 rounded-full text-xs font-medium {{ $couleur }}">{{ $libelle }}</span></td>
                                    <td class="p-4 text-gray-600">{{ $examen->description ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-gray-600 text-center">Aucune session d'examen prévue pour votre cycle.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>
