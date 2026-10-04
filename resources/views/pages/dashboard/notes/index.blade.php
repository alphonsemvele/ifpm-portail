<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Note;

name('dashboard.notes');
middleware(['auth', 'verified', 'role']);

new class extends Component {
    public $sessions = [];
    public array $globalStats = ['moy' => null, 'credits' => 0, 'valides' => 0, 'matieres' => 0];

    public function mount()
    {
        $user = auth()->user();

        $notes = Note::where('etudiant_id', $user->id)
            ->with(['cours.ue', 'examen'])
            ->get();

        $sessions   = [];
        $sumMoy     = 0;
        $countMoy   = 0;
        $totCredits = 0;
        $totValides = 0;

        foreach ($notes as $note) {
            $cours = $note->cours;
            if (!$cours) continue;

            $examFinal = ($note->rattrapage !== null && $note->rattrapage > ($note->exam ?? 0))
                ? $note->rattrapage : $note->exam;

            $moy = ($note->cc !== null && $examFinal !== null)
                ? round(0.3 * $note->cc + 0.7 * $examFinal, 2)
                : ($note->valeur !== null ? (float) $note->valeur : null);

            $credit  = (int) ($cours->credit ?? 0);
            $valide  = $moy !== null && $moy >= 10;

            $sessionKey   = $note->examen->id ?? 0;
            $sessionTitre = $note->examen->titre ?? 'Session non définie';

            if (!isset($sessions[$sessionKey])) {
                $sessions[$sessionKey] = ['titre' => $sessionTitre, 'ues' => [], 'credits' => 0, 'valides' => 0];
            }

            $ueKey  = $cours->ue->id ?? 0;
            $ueName = $cours->ue->name ?? 'Autres matières';
            $ueCode = $cours->ue->code ?? '';

            if (!isset($sessions[$sessionKey]['ues'][$ueKey])) {
                $sessions[$sessionKey]['ues'][$ueKey] = ['name' => $ueName, 'code' => $ueCode, 'lignes' => []];
            }

            $sessions[$sessionKey]['ues'][$ueKey]['lignes'][] = [
                'code'    => $cours->code ?? $ueCode,
                'nom'     => $cours->name,
                'cc'      => $note->cc,
                'exam'    => $note->exam,
                'ratt'    => $note->rattrapage,
                'moy'     => $moy,
                'n100'    => $moy !== null ? round($moy * 5, 2) : null,
                'cote'    => $this->cote($moy !== null ? $moy * 5 : null),
                'credit'  => $credit,
                'valide'  => $valide,
            ];

            $sessions[$sessionKey]['credits'] += $credit;
            if ($valide) $sessions[$sessionKey]['valides'] += $credit;

            if ($moy !== null) { $sumMoy += $moy; $countMoy++; }
            $totCredits += $credit;
            if ($valide) $totValides += $credit;
        }

        $this->sessions = $sessions;
        $this->globalStats = [
            'moy'      => $countMoy ? round($sumMoy / $countMoy, 2) : null,
            'credits'  => $totCredits,
            'valides'  => $totValides,
            'matieres' => $countMoy,
        ];
    }

    private function cote($n100): string
    {
        if ($n100 === null) return '—';
        $n = (float) $n100;
        return match (true) {
            $n >= 80 => 'A',  $n >= 75 => 'A-',
            $n >= 70 => 'B+', $n >= 65 => 'B',  $n >= 60 => 'B-',
            $n >= 55 => 'C+', $n >= 50 => 'C',  $n >= 45 => 'C-',
            $n >= 40 => 'D+', $n >= 33 => 'D',  $n >= 27 => 'E',
            default  => 'F',
        };
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- Header --}}
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Mes Notes &amp; Résultats</h1>
                    <p class="text-gray-500">Consultez vos évaluations par session et unité d'enseignement.</p>
                </div>
                <a href="/dashboard" class="text-gray-400 hover:text-emerald-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    
                    
                </div>
            </div>

            {{-- Statistiques globales --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Moyenne générale</p>
                    <p class="mt-1 text-3xl font-bold {{ ($globalStats['moy'] ?? 0) >= 10 ? 'text-emerald-600' : 'text-gray-800' }}">
                        {{ $globalStats['moy'] !== null ? number_format($globalStats['moy'], 2) : '—' }}<span class="text-base text-gray-400">/20</span>
                    </p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Crédits validés</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-600">{{ $globalStats['valides'] }}<span class="text-base text-gray-400">/{{ $globalStats['credits'] }}</span></p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Matières notées</p>
                    <p class="mt-1 text-3xl font-bold text-gray-800">{{ $globalStats['matieres'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Relevé officiel</p>
                    <p class="mt-2 text-sm text-gray-500">Disponible auprès de la scolarité.</p>
                </div>
            </div>

            @forelse ($sessions as $session)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-100 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-emerald-800 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            {{ $session['titre'] }}
                        </h2>
                        <span class="text-sm font-semibold text-emerald-700">{{ $session['valides'] }}/{{ $session['credits'] }} crédits validés</span>
                    </div>

                    <div class="p-4 sm:p-6 space-y-6">
                        @foreach ($session['ues'] as $ue)
                            <div>
                                <h3 class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                    <span class="inline-block px-2 py-0.5 rounded bg-gray-100 text-gray-500 text-xs font-mono">{{ $ue['code'] ?: 'UE' }}</span>
                                    {{ $ue['name'] }}
                                </h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="text-xs text-gray-500 border-b border-gray-100">
                                                <th class="py-2 px-3 text-left font-medium">Matière</th>
                                                <th class="py-2 px-3 text-center font-medium">CC</th>
                                                <th class="py-2 px-3 text-center font-medium">Examen</th>
                                                <th class="py-2 px-3 text-center font-medium">Ratt.</th>
                                                <th class="py-2 px-3 text-center font-medium">Moy./20</th>
                                                <th class="py-2 px-3 text-center font-medium">Cote</th>
                                                <th class="py-2 px-3 text-center font-medium">Crédits</th>
                                                <th class="py-2 px-3 text-center font-medium">Décision</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50">
                                            @foreach ($ue['lignes'] as $l)
                                                <tr class="hover:bg-gray-50/60">
                                                    <td class="py-2.5 px-3 text-gray-800">{{ $l['nom'] }}</td>
                                                    <td class="py-2.5 px-3 text-center text-gray-600">{{ $l['cc'] !== null ? number_format($l['cc'], 2) : '—' }}</td>
                                                    <td class="py-2.5 px-3 text-center text-gray-600">{{ $l['exam'] !== null ? number_format($l['exam'], 2) : '—' }}</td>
                                                    <td class="py-2.5 px-3 text-center text-amber-600">{{ $l['ratt'] !== null ? number_format($l['ratt'], 2) : '—' }}</td>
                                                    <td class="py-2.5 px-3 text-center font-bold {{ $l['moy'] !== null ? ($l['moy'] >= 10 ? 'text-emerald-600' : 'text-red-500') : 'text-gray-400' }}">
                                                        {{ $l['moy'] !== null ? number_format($l['moy'], 2) : '—' }}
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center text-gray-600 font-mono">{{ $l['cote'] }}</td>
                                                    <td class="py-2.5 px-3 text-center text-gray-600">{{ $l['credit'] }}</td>
                                                    <td class="py-2.5 px-3 text-center">
                                                        @if ($l['moy'] === null)
                                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">En attente</span>
                                                        @elseif ($l['valide'])
                                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700 font-medium">Validé</span>
                                                        @else
                                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-600 font-medium">Non validé</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm p-16 text-center text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-base font-medium text-gray-500">Aucune note disponible pour le moment.</p>
                    <p class="text-sm mt-1">Vos résultats apparaîtront ici dès qu'ils seront saisis par vos enseignants.</p>
                </div>
            @endforelse
        </div>
    @endvolt
</x-layouts.app>
