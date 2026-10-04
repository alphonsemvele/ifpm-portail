<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\PaiementSalaire;
use App\Models\User;

name('admin.bulletins');
middleware(['auth', 'verified', 'role']);

new class extends Component {

    public string $rechercheNom   = '';
    public string $typeRecherche  = 'mois';
    public int    $mois           = 0;
    public int    $annee          = 0;
    public string $dateDebut      = '';
    public string $dateFin        = '';
    public string $filtreStatut   = '';
    public bool   $tousEmployes   = false;

    public $resultats    = [];
    public bool $cherche = false;
    public ?int  $previewId   = null;
    public bool  $showPreview = false;

    public $suggestions  = [];
    public bool $showSuggestions = false;
    public array $employesSelectionnes = [];

    public function mount(): void
    {
        $this->mois  = (int) date('n');
        $this->annee = (int) date('Y');
    }

    public function updatedRechercheNom(): void
    {
        if (strlen($this->rechercheNom) < 2) {
            $this->suggestions    = [];
            $this->showSuggestions = false;
            return;
        }
        $this->suggestions = User::whereNotIn('role', ['student', 'etudiant'])
            ->where('status', 'Success')
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->rechercheNom}%")
                  ->orWhere('lastname', 'like', "%{$this->rechercheNom}%")
                  ->orWhere('matricule', 'like', "%{$this->rechercheNom}%");
            })
            ->limit(8)->get(['id', 'name', 'lastname', 'matricule'])->toArray();
        $this->showSuggestions = !empty($this->suggestions);
    }

    public function selectionnerEmploye(int $id, string $nom): void
    {
        foreach ($this->employesSelectionnes as $e) {
            if ($e['id'] === $id) { $this->rechercheNom = ''; $this->showSuggestions = false; return; }
        }
        $this->employesSelectionnes[] = ['id' => $id, 'nom' => $nom];
        $this->rechercheNom   = '';
        $this->showSuggestions = false;
    }

    public function retirerEmploye(int $id): void
    {
        $this->employesSelectionnes = array_values(
            array_filter($this->employesSelectionnes, fn($e) => $e['id'] !== $id)
        );
    }

    public function rechercher(): void
    {
        $query = PaiementSalaire::with(['employe','profil','echelon','validePar','payePar']);

        if (!$this->tousEmployes && !empty($this->employesSelectionnes)) {
            $query->whereIn('user_id', array_column($this->employesSelectionnes, 'id'));
        } elseif (!$this->tousEmployes && empty($this->employesSelectionnes)) {
            $this->resultats = collect(); $this->cherche = true; return;
        }

        if ($this->typeRecherche === 'mois') {
            $query->where('mois', $this->mois)->where('annee', $this->annee);
        } else {
            if ($this->dateDebut) {
                $debut = \Carbon\Carbon::parse($this->dateDebut);
                $query->where(fn($q) => $q->where('annee','>',$debut->year)->orWhere(fn($q2)=>$q2->where('annee',$debut->year)->where('mois','>=',$debut->month)));
            }
            if ($this->dateFin) {
                $fin = \Carbon\Carbon::parse($this->dateFin);
                $query->where(fn($q) => $q->where('annee','<',$fin->year)->orWhere(fn($q2)=>$q2->where('annee',$fin->year)->where('mois','<=',$fin->month)));
            }
        }
        if ($this->filtreStatut) { $query->where('statut', $this->filtreStatut); }

        $this->resultats = $query->orderByDesc('annee')->orderByDesc('mois')->orderBy('user_id')->get();
        $this->cherche   = true;
    }

    public function previsualiser(int $id): void
    {
        $this->previewId   = $id;
        $this->showPreview = true;
    }

    public function reinitialiser(): void
    {
        $this->rechercheNom = ''; $this->employesSelectionnes = [];
        $this->tousEmployes = false; $this->typeRecherche = 'mois';
        $this->mois = (int)date('n'); $this->annee = (int)date('Y');
        $this->dateDebut = ''; $this->dateFin = ''; $this->filtreStatut = '';
        $this->resultats = collect(); $this->cherche = false;
    }

    public function nomMois(int $m): string
    {
        $n=['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        return $n[$m] ?? '';
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="min-h-screen bg-gray-50">

    {{-- Barre titre --}}
    <div class="bg-white border-b border-gray-200 px-6 py-5">
        <div class="max-w-7xl mx-auto flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#4f46e5;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Bulletins de Paie</h1>
                <p class="text-xs text-gray-500">Recherchez et consultez les bulletins par employé ou par période</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex gap-6 items-start">

            {{-- ══ GAUCHE : Formulaire ══ --}}
            <div class="w-80 flex-shrink-0 space-y-4 sticky top-24">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-1.5 h-5 rounded-full" style="background:#4f46e5;"></div>
                        <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Recherche</span>
                    </div>
                    <div class="p-5 space-y-5">

                        {{-- Employés --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Employé(s)</label>
                            <label class="flex items-center gap-2.5 cursor-pointer mb-3 p-2.5 bg-gray-50 rounded-xl border border-gray-200">
                                <input wire:model.live="tousEmployes" type="checkbox" class="w-4 h-4 rounded"/>
                                <span class="text-sm font-medium text-gray-700">Tous les employés</span>
                            </label>
                            @if (!$tousEmployes)
                                @if (!empty($employesSelectionnes))
                                    <div class="flex flex-wrap gap-1.5 mb-2">
                                        @foreach ($employesSelectionnes as $emp)
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full" style="background:#e0e7ff;color:#3730a3;">
                                                {{ $emp['nom'] }}
                                                <button wire:click="retirerEmploye({{ $emp['id'] }})">×</button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="relative">
                                    <input wire:model.live="rechercheNom" type="text"
                                        class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                        placeholder="Nom, prénom ou matricule…"/>
                                    @if ($showSuggestions && !empty($suggestions))
                                        <div class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-20 overflow-hidden">
                                            @foreach ($suggestions as $s)
                                                <button wire:click="selectionnerEmploye({{ $s['id'] }}, '{{ addslashes($s['name'].' '.($s['lastname'] ?? '')) }}')"
                                                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-indigo-50 transition flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background:#4f46e5;">
                                                        {{ strtoupper(substr($s['name'], 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-800">{{ $s['name'] }} {{ $s['lastname'] }}</p>
                                                        @if ($s['matricule'])<p class="text-xs text-gray-400">{{ $s['matricule'] }}</p>@endif
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Période --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Période</label>
                            <div class="flex rounded-xl border border-gray-200 overflow-hidden">
                                <button wire:click="$set('typeRecherche','mois')" class="flex-1 py-2 text-xs font-semibold transition"
                                    style="{{ $typeRecherche === 'mois' ? 'background:#4f46e5;color:white;' : 'background:white;color:#6b7280;' }}">Mois précis</button>
                                <button wire:click="$set('typeRecherche','plage')" class="flex-1 py-2 text-xs font-semibold transition"
                                    style="{{ $typeRecherche === 'plage' ? 'background:#4f46e5;color:white;' : 'background:white;color:#6b7280;' }}">Plage</button>
                            </div>
                        </div>

                        @if ($typeRecherche === 'mois')
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Mois</label>
                                    <select wire:model="mois" class="w-full text-sm border border-gray-300 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        @foreach (['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'] as $n => $nom)
                                            @if ($n > 0)<option value="{{ $n }}">{{ $nom }}</option>@endif
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Année</label>
                                    <select wire:model="annee" class="w-full text-sm border border-gray-300 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        @for ($y = date('Y'); $y >= date('Y') - 4; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        @else
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Du</label>
                                    <input wire:model="dateDebut" type="month" class="w-full text-sm border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Au</label>
                                    <input wire:model="dateFin" type="month" class="w-full text-sm border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                                </div>
                            </div>
                        @endif

                        {{-- Statut --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Statut</label>
                            <select wire:model="filtreStatut" class="w-full text-sm border border-gray-300 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="">Tous les statuts</option>
                                <option value="en_attente">En attente</option>
                                <option value="valide">Validé</option>
                                <option value="paye">Payé</option>
                            </select>
                        </div>

                        {{-- Boutons --}}
                        <div class="space-y-2 pt-1">
                            <button wire:click="rechercher" wire:loading.attr="disabled" wire:target="rechercher"
                                class="w-full py-3 text-sm font-bold text-white rounded-xl flex items-center justify-center gap-2 transition disabled:opacity-60"
                                style="background:#4f46e5;">
                                <svg wire:loading.remove wire:target="rechercher" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <svg wire:loading wire:target="rechercher" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span wire:loading.remove wire:target="rechercher">Rechercher</span>
                                <span wire:loading wire:target="rechercher">Recherche…</span>
                            </button>
                            <button wire:click="reinitialiser" class="w-full py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">Réinitialiser</button>
                        </div>
                    </div>
                </div>

                {{-- Stats --}}
                @if ($cherche && $resultats->count() > 0)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Résumé</p>
                        <div class="flex justify-between text-sm"><span class="text-gray-500">Bulletins</span><span class="font-bold text-gray-900">{{ $resultats->count() }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500">Masse salariale</span><span class="font-bold" style="color:#059669;">{{ number_format($resultats->sum('salaire_net'), 0, ',', ' ') }} FCFA</span></div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500">Payés</span><span class="font-bold text-gray-700">{{ $resultats->where('statut','paye')->count() }} / {{ $resultats->count() }}</span></div>
                        <button onclick="window.print()" class="w-full py-2.5 text-xs font-bold text-white rounded-xl flex items-center justify-center gap-2 mt-2" style="background:#374151;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Imprimer tout
                        </button>
                    </div>
                @endif
            </div>

            {{-- ══ DROITE : Résultats ══ --}}
            <div class="flex-1 min-w-0">

                @if (!$cherche)
                    <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-16 text-center">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-5" style="background:#eef2ff;">
                            <svg class="w-8 h-8" style="color:#a5b4fc;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <p class="font-semibold text-gray-600 mb-1">Lancez une recherche</p>
                        <p class="text-sm text-gray-400">Sélectionnez un ou plusieurs employés, choisissez une période et cliquez sur Rechercher.</p>
                    </div>

                @elseif ($resultats->isEmpty())
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-16 text-center">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-5" style="background:#fef3c7;">
                            <svg class="w-8 h-8" style="color:#fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="font-semibold text-gray-600 mb-1">Aucun bulletin trouvé</p>
                        <p class="text-sm text-gray-400">Vérifiez que les salaires ont été générés pour cette période.</p>
                    </div>

                @else
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-5 rounded-full" style="background:#4f46e5;"></div>
                                <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">{{ $resultats->count() }} bulletin(s) trouvé(s)</span>
                            </div>
                            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-semibold rounded-xl" style="background:#374151;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                Imprimer tout
                            </button>
                        </div>

                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs font-bold uppercase tracking-wider text-white" style="background:#111827;">
                                    <th class="px-4 py-3.5">Employé</th>
                                    <th class="px-4 py-3.5">Période</th>
                                    <th class="px-4 py-3.5 text-right">Base</th>
                                    <th class="px-4 py-3.5 text-right">Ind.</th>
                                    <th class="px-4 py-3.5 text-right">Ret.</th>
                                    <th class="px-4 py-3.5 text-right">Net</th>
                                    <th class="px-4 py-3.5 text-center">Statut</th>
                                    <th class="px-4 py-3.5 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody x-data="{ ouvert: null }">
                                @foreach ($resultats as $i => $p)
                                    @php $badge = $p->statut_badge; $detail = $p->detail_json ?? []; $indemnites = $detail['indemnites'] ?? []; $retenues = $detail['retenues'] ?? []; @endphp

                                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-t border-gray-100 hover:bg-indigo-50 transition-colors cursor-pointer"
                                        @click="ouvert = ouvert === {{ $p->id }} ? null : {{ $p->id }}">
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background:#4f46e5;">
                                                    {{ strtoupper(substr($p->employe->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-900">{{ strtoupper($p->employe->name) }} {{ $p->employe->lastname }}</p>
                                                    <p class="text-xs text-gray-400">{{ $p->employe->matricule ?? '—' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <p class="font-semibold text-gray-800">{{ $this->nomMois($p->mois) }} {{ $p->annee }}</p>
                                            @if ($p->profil)<p class="text-xs text-gray-400">{{ $p->profil->nom }}</p>@endif
                                        </td>
                                        <td class="px-4 py-3.5 text-right text-xs text-gray-600">{{ number_format($p->salaire_base, 0, ',', ' ') }}</td>
                                        <td class="px-4 py-3.5 text-right text-xs font-semibold" style="color:#15803d;">+{{ number_format($p->total_indemnites, 0, ',', ' ') }}</td>
                                        <td class="px-4 py-3.5 text-right text-xs font-semibold" style="color:#dc2626;">-{{ number_format($p->total_retenues, 0, ',', ' ') }}</td>
                                        <td class="px-4 py-3.5 text-right font-bold" style="color:#059669;">
                                            {{ number_format($p->salaire_net, 0, ',', ' ') }}<span class="text-xs text-gray-400 font-normal ml-0.5">FCFA</span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full"
                                                  style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};">{{ $badge['label'] }}</span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <div class="flex items-center justify-center gap-1.5" @click.stop>
                                                {{-- Détail --}}
                                                <button @click="ouvert = ouvert === {{ $p->id }} ? null : {{ $p->id }}"
                                                    class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-indigo-100 flex items-center justify-center text-gray-500 hover:text-indigo-700 transition" title="Détail">
                                                    <svg class="w-4 h-4 transition-transform" :class="ouvert === {{ $p->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                {{-- Prévisualiser --}}
                                                <button wire:click="previsualiser({{ $p->id }})"
                                                    class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-purple-100 flex items-center justify-center text-gray-500 hover:text-purple-700 transition" title="Prévisualiser A4">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </button>
                                                {{-- Imprimer --}}
                                                <button onclick="imprimerBulletin({{ $p->id }})"
                                                    class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition" title="Imprimer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                </button>
                                                {{-- Télécharger PDF --}}
                                                <a href="{{ route('bulletin.pdf', $p->id) }}" target="_blank"
                                                    class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-green-100 flex items-center justify-center text-gray-500 hover:text-green-700 transition" title="Télécharger PDF">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Ligne détail --}}
                                    <tr x-show="ouvert === {{ $p->id }}" x-transition class="border-t border-indigo-100">
                                        <td colspan="8" class="px-0 py-0">
                                            <div class="px-6 py-5 space-y-3" style="background:#f8fafc;" id="bulletin-{{ $p->id }}">
                                                <div class="grid grid-cols-3 gap-3">
                                                    <div class="flex justify-between items-center px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm">
                                                        <span class="font-medium text-gray-600">Salaire de base</span>
                                                        <span class="font-bold" style="color:#4f46e5;">{{ number_format($p->salaire_base, 0, ',', ' ') }} FCFA</span>
                                                    </div>
                                                    <div class="flex justify-between items-center px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm">
                                                        <span class="font-medium text-gray-600">Salaire brut</span>
                                                        <span class="font-bold text-gray-900">{{ number_format($p->salaire_base + $p->total_indemnites, 0, ',', ' ') }} FCFA</span>
                                                    </div>
                                                    <div class="flex justify-between items-center px-4 py-3 rounded-xl text-white text-sm" style="background:#059669;">
                                                        <span class="font-semibold" style="color:#a7f3d0;">Net à payer</span>
                                                        <span class="font-bold text-lg">{{ number_format($p->salaire_net, 0, ',', ' ') }} FCFA</span>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    @if (!empty($indemnites))
                                                        <div>
                                                            <p class="text-xs font-bold uppercase tracking-wide mb-2" style="color:#15803d;">+ Indemnités (+{{ number_format($p->total_indemnites, 0, ',', ' ') }} FCFA)</p>
                                                            <div class="space-y-1.5">
                                                                @foreach ($indemnites as $ind)
                                                                    <div class="flex justify-between px-3 py-2 rounded-lg text-xs" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                                                        <span class="text-gray-700">{{ $ind['libelle'] }} <span class="text-gray-400">({{ $ind['type'] === 'fixe' ? 'fixe' : $ind['valeur'].'%' }})</span></span>
                                                                        <span class="font-bold" style="color:#15803d;">+{{ number_format($ind['montant'], 0, ',', ' ') }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="flex items-center justify-center px-4 py-6 rounded-xl border-2 border-dashed border-gray-200 text-xs text-gray-400">Aucune indemnité</div>
                                                    @endif
                                                    @if (!empty($retenues))
                                                        <div>
                                                            <p class="text-xs font-bold uppercase tracking-wide mb-2" style="color:#dc2626;">- Retenues (-{{ number_format($p->total_retenues, 0, ',', ' ') }} FCFA)</p>
                                                            <div class="space-y-1.5">
                                                                @foreach ($retenues as $ret)
                                                                    <div class="flex justify-between px-3 py-2 rounded-lg text-xs" style="background:#fff5f5;border:1px solid #fecaca;">
                                                                        <span class="text-gray-700">{{ $ret['libelle'] }} <span class="text-gray-400">({{ $ret['type'] === 'fixe' ? 'fixe' : $ret['valeur'].'%' }})</span></span>
                                                                        <span class="font-bold" style="color:#dc2626;">-{{ number_format($ret['montant'], 0, ',', ' ') }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="flex items-center justify-center px-4 py-6 rounded-xl border-2 border-dashed border-gray-200 text-xs text-gray-400">Aucune retenue</div>
                                                    @endif
                                                </div>
                                                @if ($p->note)
                                                    <div class="px-4 py-3 rounded-xl text-xs" style="background:#fef9c3;border:1px solid #fde68a;color:#92400e;"><strong>Note :</strong> {{ $p->note }}</div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200" style="background:#f8fafc;">
                                    <td colspan="2" class="px-4 py-3 text-sm font-bold text-gray-700 text-right">Masse salariale totale :</td>
                                    <td class="px-4 py-3 text-right text-xs font-semibold text-gray-600">{{ number_format($resultats->sum('salaire_base'), 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right text-xs font-semibold" style="color:#15803d;">+{{ number_format($resultats->sum('total_indemnites'), 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right text-xs font-semibold" style="color:#dc2626;">-{{ number_format($resultats->sum('total_retenues'), 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-base" style="color:#059669;">{{ number_format($resultats->sum('salaire_net'), 0, ',', ' ') }} <span class="text-xs text-gray-400 font-normal">FCFA</span></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ════ MODAL PRÉVISUALISATION A4 (iframe) ════ --}}
    <div class="fixed inset-0 z-50" style="{{ $showPreview ? 'display:flex;background:rgba(0,0,0,0.75);' : 'display:none;' }}">
        <div class="flex flex-col w-full h-full">

            {{-- Barre outils --}}
            <div class="flex items-center justify-between px-6 py-3 flex-shrink-0" style="background:#1e293b;">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#4f46e5;">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-sm font-bold text-white">Prévisualisation bulletin</p>
                </div>
                <div class="flex items-center gap-2">
                    @if ($showPreview && $previewId)
                        <a href="{{ route('bulletin.pdf', $previewId ?? 0) }}" target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-xl" style="background:#059669;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Télécharger PDF
                        </a>
                    @endif
                    <button wire:click="$set('showPreview',false)"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background:rgba(255,255,255,0.1);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Iframe PDF --}}
            <div class="flex-1" style="background:#525659;">
                @if ($showPreview && $previewId)
                    <iframe src="{{ route('bulletin.preview', $previewId ?? 0) }}"
                        style="width:100%;height:100%;border:none;"
                        title="Bulletin de paie">
                    </iframe>
                @endif
            </div>

        </div>
    </div>
    <script>
    function imprimerBulletin(id) {
        const el = document.getElementById('bulletin-' + id);
        if (!el) return;
        const win = window.open('', '_blank');
        win.document.write('<!DOCTYPE html><html><head><title>Bulletin<\/title><meta charset="utf-8"><style>*{box-sizing:border-box;}body{font-family:sans-serif;padding:24px;font-size:12px;color:#334155;}<\/style><\/head><body>' + el.innerHTML + '<script>window.onload=()=>window.print()<\/sc'+'ript><\/body><\/html>');
        win.document.close();
    }
    </script>



</div>
@endvolt
</x-layouts.app>