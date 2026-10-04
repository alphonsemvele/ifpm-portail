<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\PaiementSalaire;
use Illuminate\Support\Facades\Auth;

name('mes.salaires');
middleware(['auth', 'verified']);

new class extends Component {

    public $paiements = [];
    public ?int $detailId = null;

    public function mount(): void
    {
        $this->paiements = PaiementSalaire::with(['echelon', 'profil', 'validePar'])
            ->where('user_id', Auth::id())
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->get();
    }

    public function voirDetail(int $id): void
    {
        $this->detailId = $this->detailId === $id ? null : $id;
    }

    public function nomMois(int $m): string
    {
        $noms = ['','Janvier','Février','Mars','Avril','Mai','Juin',
                 'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        return $noms[$m] ?? '';
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="min-h-screen bg-gray-50">

    <div class="bg-white border-b border-gray-200 px-6 py-5">
        <div class="max-w-4xl mx-auto flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#059669;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Mes Salaires</h1>
                <p class="text-xs text-gray-500">Historique de vos paiements</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if ($paiements->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-16 text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#f0fdf4;">
                    <svg class="w-7 h-7" style="color:#86efac;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="font-semibold text-gray-600 mb-1">Aucun paiement disponible</p>
                <p class="text-sm text-gray-400">Vos fiches de paie apparaîtront ici une fois générées par l'administration.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($paiements as $p)
                    @php $badge = $p->statut_badge; @endphp
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        {{-- Ligne principale --}}
                        <button wire:click="voirDetail({{ $p->id }})"
                            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition text-left">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f0fdf4;">
                                    <svg class="w-5 h-5" style="color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ $this->nomMois($p->mois) }} {{ $p->annee }}</p>
                                    <p class="text-xs text-gray-400">{{ $p->profil?->nom ?? '—' }} @if($p->echelon) · Éch. {{ $p->echelon->numero }} @endif</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="font-bold text-base" style="color:#059669;">{{ number_format($p->salaire_net, 0, ',', ' ') }} FCFA</p>
                                    <p class="text-xs text-gray-400">Salaire net</p>
                                </div>
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full"
                                      style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};">
                                    {{ $badge['label'] }}
                                </span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform {{ $detailId === $p->id ? 'rotate-180' : '' }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>

                        {{-- Détail dépliable --}}
                        @if ($detailId === $p->id)
                            <div class="border-t border-gray-100 px-6 py-5 space-y-4" style="background:#f8fafc;">

                                {{-- Base --}}
                                <div class="flex justify-between items-center p-3 bg-white rounded-xl border border-gray-200 text-sm">
                                    <span class="font-medium text-gray-700">Salaire de base</span>
                                    <span class="font-bold" style="color:#4f46e5;">{{ number_format($p->salaire_base, 0, ',', ' ') }} FCFA</span>
                                </div>

                                {{-- Indemnités --}}
                                @if (!empty($p->detail_json['indemnites']))
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide mb-2" style="color:#15803d;">+ Indemnités</p>
                                        <div class="space-y-1.5">
                                            @foreach ($p->detail_json['indemnites'] as $ind)
                                                <div class="flex justify-between text-xs px-4 py-2.5 rounded-xl" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                                    <span class="text-gray-700">{{ $ind['libelle'] }} <span class="text-gray-400">({{ $ind['type'] === 'fixe' ? 'fixe' : $ind['valeur'].'%' }})</span></span>
                                                    <span class="font-bold" style="color:#15803d;">+{{ number_format($ind['montant'], 0, ',', ' ') }}</span>
                                                </div>
                                            @endforeach
                                            <div class="flex justify-between px-4 py-1 text-xs font-bold" style="color:#15803d;">
                                                <span>Total indemnités</span>
                                                <span>+{{ number_format($p->total_indemnites, 0, ',', ' ') }} FCFA</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Retenues --}}
                                @if (!empty($p->detail_json['retenues']))
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide mb-2" style="color:#dc2626;">− Retenues</p>
                                        <div class="space-y-1.5">
                                            @foreach ($p->detail_json['retenues'] as $ret)
                                                <div class="flex justify-between text-xs px-4 py-2.5 rounded-xl" style="background:#fff5f5;border:1px solid #fecaca;">
                                                    <span class="text-gray-700">{{ $ret['libelle'] }} <span class="text-gray-400">({{ $ret['type'] === 'fixe' ? 'fixe' : $ret['valeur'].'%' }})</span></span>
                                                    <span class="font-bold" style="color:#dc2626;">-{{ number_format($ret['montant'], 0, ',', ' ') }}</span>
                                                </div>
                                            @endforeach
                                            <div class="flex justify-between px-4 py-1 text-xs font-bold" style="color:#dc2626;">
                                                <span>Total retenues</span>
                                                <span>-{{ number_format($p->total_retenues, 0, ',', ' ') }} FCFA</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Net --}}
                                <div class="flex items-center justify-between p-4 rounded-xl text-white" style="background:#059669;">
                                    <div>
                                        <p class="text-xs font-semibold" style="color:#a7f3d0;">SALAIRE NET À PAYER</p>
                                        <p class="text-2xl font-bold mt-0.5">{{ number_format($p->salaire_net, 0, ',', ' ') }}</p>
                                        <p class="text-xs" style="color:#6ee7b7;">Francs CFA</p>
                                    </div>
                                    @if ($p->paye_le)
                                        <div class="text-right">
                                            <p class="text-xs" style="color:#a7f3d0;">Payé le</p>
                                            <p class="font-bold">{{ $p->paye_le->format('d/m/Y') }}</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Note --}}
                                @if ($p->note)
                                    <div class="px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-xl text-xs text-yellow-800">
                                        <span class="font-bold">Note :</span> {{ $p->note }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endvolt
</x-layouts.app>