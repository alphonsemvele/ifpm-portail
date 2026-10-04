<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\PaiementSalaire;

name('personnel.bulletins');
middleware(['auth', 'verified', 'role']);

new class extends Component {
    public array $annees = [];

    // Option 1 — un mois
    public $anneeUn;
    public int $moisUn = 1;

    // Option 2 — une plage
    public $anneeDebut;
    public int $moisDebut = 1;
    public $anneeFin;
    public int $moisFin = 12;

    // Résultats de recherche (null = pas encore recherché)
    public $results = null;
    public string $downloadUrl = '';
    public string $resultLabel = '';

    public function mount()
    {
        $annees = PaiementSalaire::where('user_id', auth()->id())
            ->select('annee')->distinct()->orderByDesc('annee')->pluck('annee')->toArray();
        $this->annees = $annees;

        $courante = $annees[0] ?? (int) date('Y');
        $this->anneeUn    = $courante;
        $this->moisUn     = (int) date('n');
        $this->anneeDebut = min($annees ?: [$courante]);
        $this->anneeFin   = $courante;
    }

    private function moisNom(int $n): string
    {
        return [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'][$n] ?? '';
    }

    private function formater($paiements): array
    {
        return $paiements->map(fn($b) => [
            'id'    => $b->id,
            'mois'  => $b->mois,
            'annee' => $b->annee,
            'net'   => (float) $b->salaire_net,
            'badge' => $b->statut_badge,
        ])->all();
    }

    public function rechercherMois()
    {
        $paiements = PaiementSalaire::where('user_id', auth()->id())
            ->where('annee', $this->anneeUn)->where('mois', $this->moisUn)
            ->orderByDesc('annee')->orderByDesc('mois')->get();

        $this->results = $this->formater($paiements);
        $this->downloadUrl = route('bulletin.periode', [
            'annee_debut' => $this->anneeUn, 'annee_fin' => $this->anneeUn,
            'mois_debut' => $this->moisUn, 'mois_fin' => $this->moisUn,
        ]);
        $this->resultLabel = $this->moisNom($this->moisUn) . ' ' . $this->anneeUn;
    }

    public function rechercherPlage()
    {
        $start = $this->anneeDebut * 12 + $this->moisDebut;
        $end   = $this->anneeFin * 12 + $this->moisFin;
        if ($start > $end) [$start, $end] = [$end, $start];

        $paiements = PaiementSalaire::where('user_id', auth()->id())
            ->whereRaw('(annee * 12 + mois) between ? and ?', [$start, $end])
            ->orderByDesc('annee')->orderByDesc('mois')->get();

        $this->results = $this->formater($paiements);
        $this->downloadUrl = route('bulletin.periode', [
            'annee_debut' => $this->anneeDebut, 'mois_debut' => $this->moisDebut,
            'annee_fin' => $this->anneeFin, 'mois_fin' => $this->moisFin,
        ]);
        $this->resultLabel = 'De ' . $this->moisNom($this->moisDebut) . ' ' . $this->anneeDebut
            . ' à ' . $this->moisNom($this->moisFin) . ' ' . $this->anneeFin;
    }
};
?>

<x-layouts.app header="true">
    @volt
        @php
            $moisNoms = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
            $anneesOptions = $annees ?: [(int) date('Y')];
        @endphp
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Mon Bulletin de Paie</h1>
                    <p class="text-gray-500">Recherchez une période, vos bulletins s'affichent à droite.</p>
                </div>
                <a href="/personnel" class="text-gray-400 hover:text-emerald-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    
                    
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

                {{-- ══════════ FORMULAIRE (gauche) ══════════ --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Option 1 : un mois --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold flex items-center justify-center">1</span>
                            <h2 class="text-base font-bold text-gray-800">Rechercher un mois</h2>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Mois</label>
                                <select wire:model="moisUn" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-400 bg-white">
                                    @foreach ($moisNoms as $n => $nom)<option value="{{ $n }}">{{ $nom }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Année</label>
                                <select wire:model="anneeUn" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-400 bg-white">
                                    @foreach ($anneesOptions as $a)<option value="{{ $a }}">{{ $a }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                        <button wire:click="rechercherMois" wire:loading.attr="disabled" wire:target="rechercherMois"
                                class="mt-4 w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 px-4 rounded-lg transition disabled:opacity-60 disabled:cursor-wait">
                            <svg wire:loading.remove wire:target="rechercherMois" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/></svg>
                            <svg wire:loading wire:target="rechercherMois" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            <span wire:loading.remove wire:target="rechercherMois">Rechercher</span>
                            <span wire:loading wire:target="rechercherMois">Recherche…</span>
                        </button>
                    </div>

                    {{-- Option 2 : une plage --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-sm font-bold flex items-center justify-center">2</span>
                            <h2 class="text-base font-bold text-gray-800">Rechercher une plage</h2>
                        </div>

                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Du</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <select wire:model="moisDebut" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 bg-white">
                                @foreach ($moisNoms as $n => $nom)<option value="{{ $n }}">{{ $nom }}</option>@endforeach
                            </select>
                            <select wire:model="anneeDebut" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 bg-white">
                                @foreach ($anneesOptions as $a)<option value="{{ $a }}">{{ $a }}</option>@endforeach
                            </select>
                        </div>

                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Au</p>
                        <div class="grid grid-cols-2 gap-3">
                            <select wire:model="moisFin" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 bg-white">
                                @foreach ($moisNoms as $n => $nom)<option value="{{ $n }}">{{ $nom }}</option>@endforeach
                            </select>
                            <select wire:model="anneeFin" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 bg-white">
                                @foreach ($anneesOptions as $a)<option value="{{ $a }}">{{ $a }}</option>@endforeach
                            </select>
                        </div>

                        <button wire:click="rechercherPlage" wire:loading.attr="disabled" wire:target="rechercherPlage"
                                class="mt-4 w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 px-4 rounded-lg transition disabled:opacity-60 disabled:cursor-wait">
                            <svg wire:loading.remove wire:target="rechercherPlage" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/></svg>
                            <svg wire:loading wire:target="rechercherPlage" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            <span wire:loading.remove wire:target="rechercherPlage">Rechercher</span>
                            <span wire:loading wire:target="rechercherPlage">Recherche…</span>
                        </button>
                    </div>
                </div>

                {{-- ══════════ RÉSULTATS (droite) ══════════ --}}
                <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden min-h-[420px] relative">

                    {{-- Overlay pending --}}
                    <div wire:loading wire:target="rechercherMois,rechercherPlage"
                         class="absolute inset-0 z-10 bg-white/80 flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-emerald-500 animate-spin mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <p class="text-sm font-semibold text-gray-600">Recherche en cours…</p>
                    </div>

                    <div wire:loading.remove wire:target="rechercherMois,rechercherPlage">
                        @if ($results === null)
                            {{-- État initial : inviter à rechercher --}}
                            <div class="flex flex-col items-center justify-center text-center py-24 px-6">
                                <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-5">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/></svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-700">Aucune recherche effectuée</h3>
                                <p class="text-sm text-gray-400 mt-1 max-w-xs">Choisissez un mois ou une plage à gauche, puis cliquez sur <span class="font-medium text-gray-500">Rechercher</span> pour afficher vos bulletins.</p>
                            </div>
                        @else
                            {{-- En-tête résultats --}}
                            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-700">{{ count($results) }} bulletin(s) — {{ $resultLabel }}</p>
                                </div>
                                @if (count($results) > 0)
                                    <a href="{{ $downloadUrl }}"
                                       class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold py-2 px-3 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Télécharger le PDF
                                    </a>
                                @endif
                            </div>

                            @if (count($results) > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left">
                                        <thead class="bg-emerald-50 text-emerald-800 text-xs uppercase tracking-wide border-b border-emerald-100">
                                            <tr>
                                                <th class="py-3 px-5">Période</th>
                                                <th class="py-3 px-5 text-right">Net à payer</th>
                                                <th class="py-3 px-5 text-center">Statut</th>
                                                <th class="py-3 px-5 text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach ($results as $b)
                                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                                    <td class="py-4 px-5 font-semibold text-gray-800">{{ $moisNoms[$b['mois']] ?? $b['mois'] }} {{ $b['annee'] }}</td>
                                                    <td class="py-4 px-5 text-right font-bold text-gray-900">{{ number_format($b['net'], 0, ',', ' ') }} FCFA</td>
                                                    <td class="py-4 px-5 text-center">
                                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium" style="background: {{ $b['badge']['bg'] }}; color: {{ $b['badge']['color'] }};">{{ $b['badge']['label'] }}</span>
                                                    </td>
                                                    <td class="py-4 px-5">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <a href="{{ route('bulletin.preview', $b['id']) }}" target="_blank"
                                                               class="inline-flex items-center gap-1 text-gray-600 hover:text-indigo-600 text-xs font-medium px-2.5 py-1.5 rounded-lg border border-gray-200 hover:border-indigo-300 transition">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                Aperçu
                                                            </a>
                                                            <a href="{{ route('bulletin.pdf', $b['id']) }}"
                                                               class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                                PDF
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                {{-- Recherche sans résultat --}}
                                <div class="flex flex-col items-center justify-center text-center py-20 px-6">
                                    <div class="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-700">Aucun bulletin trouvé</h3>
                                    <p class="text-sm text-gray-400 mt-1">Aucun bulletin pour « {{ $resultLabel }} ». Essayez une autre période.</p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>
