<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\PaiementSalaire;
use App\Models\User;
use App\Models\Echelon;
use App\Models\AjustementSalaire;
use Illuminate\Support\Facades\Auth;

name('admin.paie');
middleware(['auth', 'verified', 'role']);

new class extends Component {

    public int    $mois  = 0;
    public int    $annee = 0;

    public $paiements    = [];
    public $employes     = []; // employés sans fiche ce mois

    public bool   $showNotification    = false;
    public string $notificationMessage = '';
    public string $notificationType    = 'success';

    public bool   $showConfirmAll  = false;
    public string $actionConfirm   = ''; // 'valider' | 'payer'
    public bool   $showNoteModal   = false;
    public ?int   $noteId          = null;
    public string $noteTexte       = '';

    public bool   $showPreview    = false;
    public ?int   $previewId      = null;

    // ── Ajustements (bonus / retenues ponctuels) ──────────────────
    public bool   $showAjustModal = false;
    public ?int   $ajustUserId    = null;
    public string $ajustUserName  = '';
    public string $ajustType      = 'bonus';   // bonus | retenue
    public string $ajustMode      = 'fixe';    // fixe (FCFA) | pourcentage (% du salaire de base)
    public string $ajustLibelle   = '';
    public        $ajustMontant   = '';
    public string $ajustMotif     = '';
    // Portée : ce mois uniquement, ou une plage de mois
    public string $ajustPortee    = 'mois';    // mois | plage
    public        $ajustMoisDebut, $ajustAnneeDebut, $ajustMoisFin, $ajustAnneeFin;

    public function mount(): void
    {
        $this->mois  = (int) date('n');
        $this->annee = (int) date('Y');
        $this->charger();
    }

    public function updatedMois():  void { $this->charger(); }
    public function updatedAnnee(): void { $this->charger(); }

    public function charger(): void
    {
        $this->paiements = PaiementSalaire::with(['employe', 'echelon', 'profil', 'validePar', 'payePar'])
            ->where('mois',  $this->mois)
            ->where('annee', $this->annee)
            ->orderBy('created_at')
            ->get();

        // Employés avec profil mais sans fiche ce mois
        $dejaIds = $this->paiements->pluck('user_id')->toArray();
        $this->employes = User::with(['profilSalaire.indemnites', 'profilSalaire.retenues', 'echelon'])
            ->whereNotIn('role', ['student', 'etudiant'])
            ->where('status', 'Success')
            ->whereNotNull('profil_salaire_id')
            ->whereNotIn('id', $dejaIds)
            ->orderBy('name')
            ->get();
    }

    // ── Générer les fiches du mois ────────────────────────────────
    public function generer(): void
    {
        if ($this->employes->isEmpty()) {
            $this->toast('Tous les employés ont déjà une fiche ce mois.', 'error');
            return;
        }

        $count = 0;
        foreach ($this->employes as $emp) {
            $c = $this->calculer($emp);

            PaiementSalaire::create([
                'user_id'           => $emp->id,
                'profil_salaire_id' => $c['profil']?->id,
                'echelon_id'        => $c['echelon']?->id,
                'mois'              => $this->mois,
                'annee'             => $this->annee,
                'salaire_base'      => $c['base'],
                'total_indemnites'  => $c['totalInd'],
                'total_retenues'    => $c['totalRet'],
                'salaire_net'       => $c['net'],
                'detail_json'       => ['indemnites' => $c['indemnites'], 'retenues' => $c['retenues']],
                'statut'            => 'en_attente',
            ]);
            $count++;
        }

        $this->charger();
        $this->toast("{$count} fiche(s) générée(s) pour " . $this->nomMois() . " {$this->annee}.");
    }

    /**
     * Calcule le détail de paie d'un employé pour le mois courant :
     * base (échelon) + indemnités/retenues du profil + ajustements ponctuels.
     */
    private function calculer($emp): array
    {
        $profil  = $emp->profilSalaire;
        $echelon = $emp->echelon ?? $profil?->echelon;
        $base    = $echelon ? (float) $echelon->salaire : 0;

        $indemnites = []; $retenues = []; $totalInd = 0; $totalRet = 0;

        if ($profil) {
            foreach ($profil->indemnites as $ind) {
                $montant = $ind->pivot->type_calcul === 'fixe' ? $ind->pivot->value : round($base * $ind->pivot->value / 100);
                $totalInd += $montant;
                $indemnites[] = ['libelle' => $ind->libelle, 'type' => $ind->pivot->type_calcul, 'valeur' => $ind->pivot->value, 'montant' => $montant];
            }
            foreach ($profil->retenues as $ret) {
                $montant = $ret->pivot->type_calcul === 'fixe' ? $ret->pivot->value : round($base * $ret->pivot->value / 100);
                $totalRet += $montant;
                $retenues[] = ['libelle' => $ret->libelle, 'type' => $ret->pivot->type_calcul, 'valeur' => $ret->pivot->value, 'montant' => $montant];
            }
        }

        // Ajustements du mois (bonus / retenues ponctuels : retard, prime…)
        foreach (AjustementSalaire::where('user_id', $emp->id)->periode($this->mois, $this->annee)->get() as $aj) {
            $ligne = $this->ligneAjustement($aj, $base);
            if ($aj->type === 'bonus') { $totalInd += $ligne['montant']; $indemnites[] = $ligne; }
            else { $totalRet += $ligne['montant']; $retenues[] = $ligne; }
        }

        return [
            'echelon' => $echelon, 'profil' => $profil, 'base' => $base,
            'indemnites' => $indemnites, 'retenues' => $retenues,
            'totalInd' => $totalInd, 'totalRet' => $totalRet, 'net' => $base + $totalInd - $totalRet,
        ];
    }

    /** Construit la ligne d'un ajustement (fixe en FCFA ou % du salaire de base). */
    private function ligneAjustement($aj, float $base): array
    {
        $pct     = $aj->mode === 'pourcentage';
        $montant = $pct ? round($base * $aj->montant / 100) : (float) $aj->montant;
        return [
            'libelle' => $aj->libelle,
            'type'    => $pct ? 'ajustement_pct' : 'ajustement',
            'valeur'  => $aj->montant,   // % ou FCFA saisi
            'montant' => $montant,       // montant effectif en FCFA
        ];
    }

    /**
     * Met à jour le bulletin (s'il existe et n'est pas payé) en préservant le
     * "snapshot" du profil : on ne touche pas à la base ni aux indemnités/retenues
     * du profil, on ne fait que (ré)appliquer les lignes d'AJUSTEMENT du mois.
     */
    private function recalculerPourUser(int $userId, ?int $mois = null, ?int $annee = null): void
    {
        $mois  = $mois  ?? $this->mois;
        $annee = $annee ?? $this->annee;

        $p = PaiementSalaire::where('user_id', $userId)
            ->where('mois', $mois)->where('annee', $annee)->first();
        if (!$p || $p->statut === 'paye') return;

        $detail = $p->detail_json ?? ['indemnites' => [], 'retenues' => []];

        // On garde uniquement les lignes du profil (figées à la génération).
        $estAjustement = fn($x) => in_array($x['type'] ?? '', ['ajustement', 'ajustement_pct'], true);
        $ind = array_values(array_filter($detail['indemnites'] ?? [], fn($x) => !$estAjustement($x)));
        $ret = array_values(array_filter($detail['retenues'] ?? [], fn($x) => !$estAjustement($x)));

        $base     = (float) $p->salaire_base;
        $totalInd = array_sum(array_column($ind, 'montant'));
        $totalRet = array_sum(array_column($ret, 'montant'));

        // On (ré)ajoute les ajustements ponctuels du mois (le % est calculé sur la base figée).
        foreach (AjustementSalaire::where('user_id', $userId)->periode($mois, $annee)->get() as $aj) {
            $ligne = $this->ligneAjustement($aj, $base);
            if ($aj->type === 'bonus') { $totalInd += $ligne['montant']; $ind[] = $ligne; }
            else { $totalRet += $ligne['montant']; $ret[] = $ligne; }
        }

        $p->update([
            'total_indemnites' => $totalInd,
            'total_retenues'   => $totalRet,
            'salaire_net'      => $base + $totalInd - $totalRet,
            'detail_json'      => ['indemnites' => $ind, 'retenues' => $ret],
        ]);
    }

    // ── Gestion des ajustements ───────────────────────────────────
    public function ouvrirAjustements(int $userId): void
    {
        $u = User::find($userId);
        $this->ajustUserId   = $userId;
        $this->ajustUserName = $u ? trim(strtoupper($u->name) . ' ' . $u->lastname) : '';
        $this->ajustType     = 'bonus';
        $this->ajustMode     = 'fixe';
        $this->ajustPortee   = 'mois';
        $this->ajustMoisDebut  = $this->mois;
        $this->ajustAnneeDebut = $this->annee;
        $this->ajustMoisFin    = $this->mois;
        $this->ajustAnneeFin   = $this->annee;
        $this->reset(['ajustLibelle', 'ajustMontant', 'ajustMotif']);
        $this->showAjustModal = true;
    }

    public function getAjustementsProperty()
    {
        return $this->ajustUserId
            ? AjustementSalaire::where('user_id', $this->ajustUserId)->periode($this->mois, $this->annee)->orderByDesc('id')->get()
            : collect();
    }

    public function ajouterAjustement(): void
    {
        $estPct = $this->ajustMode === 'pourcentage';
        $this->validate([
            'ajustType'    => 'required|in:bonus,retenue',
            'ajustMode'    => 'required|in:fixe,pourcentage',
            'ajustPortee'  => 'required|in:mois,plage',
            'ajustLibelle' => 'required|string|max:255',
            'ajustMontant' => $estPct ? 'required|numeric|min:0.01|max:100' : 'required|numeric|min:1',
        ], [], ['ajustLibelle' => 'libellé', 'ajustMontant' => $estPct ? 'pourcentage' : 'montant']);

        // Détermine les mois concernés (ce mois, ou une plage sur un/plusieurs an(s)).
        if ($this->ajustPortee === 'plage') {
            $start = (int) $this->ajustAnneeDebut * 12 + (int) $this->ajustMoisDebut;
            $end   = (int) $this->ajustAnneeFin   * 12 + (int) $this->ajustMoisFin;
            if ($start > $end) [$start, $end] = [$end, $start];
            $periodes = [];
            for ($i = $start; $i <= $end; $i++) {
                $periodes[] = [(($i - 1) % 12) + 1, intdiv($i - 1, 12)];
            }
        } else {
            $periodes = [[$this->mois, $this->annee]];
        }

        foreach ($periodes as [$m, $a]) {
            AjustementSalaire::create([
                'user_id'    => $this->ajustUserId,
                'mois'       => $m,
                'annee'      => $a,
                'type'       => $this->ajustType,
                'mode'       => $this->ajustMode,
                'libelle'    => $this->ajustLibelle,
                'montant'    => $this->ajustMontant,
                'motif'      => $this->ajustMotif ?: null,
                'created_by' => Auth::id(),
            ]);
            $this->recalculerPourUser($this->ajustUserId, $m, $a);
        }

        $this->reset(['ajustLibelle', 'ajustMontant', 'ajustMotif']);
        $this->ajustType = 'bonus';
        $this->ajustMode = 'fixe';
        $this->ajustPortee = 'mois';
        $this->charger();
        $this->toast(count($periodes) > 1 ? count($periodes) . ' mois ajustés.' : 'Ajustement appliqué.');
    }

    public function supprimerAjustement(int $id): void
    {
        $aj = AjustementSalaire::find($id);
        if (!$aj) return;
        $userId = $aj->user_id;
        $aj->delete();
        $this->recalculerPourUser($userId);
        $this->charger();
        $this->toast('Ajustement supprimé.');
    }

    // ── Valider un seul ───────────────────────────────────────────
    public function validerUn(int $id): void
    {
        $p = PaiementSalaire::find($id);
        if (!$p || $p->statut === 'paye') return;
        $p->update(['statut' => 'valide', 'valide_par' => Auth::id(), 'valide_le' => now()]);
        $this->charger();
        $this->toast('Paiement validé.');
    }

    // ── Marquer payé un seul ──────────────────────────────────────
    public function payerUn(int $id): void
    {
        $p = PaiementSalaire::find($id);
        if (!$p || $p->statut !== 'valide') return;
        $p->update(['statut' => 'paye', 'paye_par' => Auth::id(), 'paye_le' => now()]);
        $this->charger();
        $this->toast('Paiement marqué comme payé.');
    }

    // ── Valider tout ──────────────────────────────────────────────
    public function validerTout(): void
    {
        PaiementSalaire::where('mois', $this->mois)->where('annee', $this->annee)
            ->where('statut', 'en_attente')
            ->update(['statut' => 'valide', 'valide_par' => Auth::id(), 'valide_le' => now()]);
        $this->showConfirmAll = false;
        $this->charger();
        $this->toast('Tous les paiements ont été validés.');
    }

    // ── Payer tout ────────────────────────────────────────────────
    public function payerTout(): void
    {
        PaiementSalaire::where('mois', $this->mois)->where('annee', $this->annee)
            ->where('statut', 'valide')
            ->update(['statut' => 'paye', 'paye_par' => Auth::id(), 'paye_le' => now()]);
        $this->showConfirmAll = false;
        $this->charger();
        $this->toast('Tous les paiements ont été marqués comme payés.');
    }

    // ── Note ──────────────────────────────────────────────────────
    public function ouvrirNote(int $id): void
    {
        $p = PaiementSalaire::find($id);
        if (!$p) return;
        $this->noteId    = $id;
        $this->noteTexte = $p->note ?? '';
        $this->showNoteModal = true;
    }

    public function sauvegarderNote(): void
    {
        PaiementSalaire::find($this->noteId)?->update(['note' => $this->noteTexte ?: null]);
        $this->showNoteModal = false;
        $this->charger();
        $this->toast('Note enregistrée.');
    }

    // ── Annuler une fiche ─────────────────────────────────────────
    public function annuler(int $id): void
    {
        $p = PaiementSalaire::find($id);
        if (!$p || $p->statut === 'paye') return;
        $p->delete();
        $this->charger();
        $this->toast('Fiche supprimée.');
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function nomMois(): string
    {
        $noms = ['','Janvier','Février','Mars','Avril','Mai','Juin',
                 'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        return $noms[$this->mois] ?? '';
    }

    public function totalNet(): float
    {
        return (float) $this->paiements->sum('salaire_net');
    }

    public function countParStatut(string $statut): int
    {
        return $this->paiements->where('statut', $statut)->count();
    }

    public function previsualiser(int $id): void
    {
        $this->previewId   = $id;
        $this->showPreview = true;
    }

    private function toast(string $msg, string $type = 'success'): void
    {
        $this->notificationMessage = $msg;
        $this->notificationType    = $type;
        $this->showNotification    = true;
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="min-h-screen bg-gray-50">

    {{-- ── Barre titre ── --}}
    <div class="bg-white border-b border-gray-200 px-6 py-5">
        <div class="max-w-7xl mx-auto flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#d97706;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Paiement des Salaires</h1>
                    <p class="text-xs text-gray-500">Générez, validez et payez les salaires du mois</p>
                </div>
            </div>

            {{-- Sélecteur mois/année --}}
            <div class="flex items-center gap-3">
                <select wire:model.live="mois"
                    class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @foreach (['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'] as $n => $nom)
                        @if ($n > 0)
                            <option value="{{ $n }}">{{ $nom }}</option>
                        @endif
                    @endforeach
                </select>
                <select wire:model.live="annee"
                    class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @for ($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- ── Stats ── --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fef3c7;">
                    <svg class="w-5 h-5" style="color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div><p class="text-xs text-gray-500">Total fiches</p><p class="text-xl font-bold text-gray-900">{{ $paiements->count() }}</p></div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fef9c3;">
                    <svg class="w-5 h-5" style="color:#ca8a04;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><p class="text-xs text-gray-500">En attente</p><p class="text-xl font-bold text-gray-900">{{ $this->countParStatut('en_attente') }}</p></div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#dbeafe;">
                    <svg class="w-5 h-5" style="color:#1d4ed8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><p class="text-xs text-gray-500">Validés</p><p class="text-xl font-bold text-gray-900">{{ $this->countParStatut('valide') }}</p></div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#dcfce7;">
                    <svg class="w-5 h-5" style="color:#15803d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div><p class="text-xs text-gray-500">Payés</p><p class="text-xl font-bold text-gray-900">{{ $this->countParStatut('paye') }}</p></div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f0fdf4;">
                    <svg class="w-5 h-5" style="color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><p class="text-xs text-gray-500">Total net</p><p class="text-base font-bold" style="color:#059669;">{{ number_format($this->totalNet(), 0, ',', ' ') }} FCFA</p></div>
            </div>
        </div>

        {{-- ── Actions globales ── --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-bold text-gray-900 text-sm">{{ $this->nomMois() }} {{ $annee }}</p>
                <p class="text-xs text-gray-500">
                    {{ $paiements->count() }} fiche(s) générée(s) ·
                    {{ $employes->count() }} employé(s) sans fiche
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($employes->count() > 0)
                    <button wire:click="generer"
                        class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl shadow"
                        style="background:#d97706;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Générer les fiches ({{ $employes->count() }})
                    </button>
                @endif
                @if ($this->countParStatut('en_attente') > 0)
                    <button wire:click="$set('actionConfirm','valider'); $set('showConfirmAll',true)"
                        class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl shadow"
                        style="background:#1d4ed8;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Valider tout ({{ $this->countParStatut('en_attente') }})
                    </button>
                @endif
                @if ($this->countParStatut('valide') > 0)
                    <button wire:click="$set('actionConfirm','payer'); $set('showConfirmAll',true)"
                        class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl shadow"
                        style="background:#059669;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Payer tout ({{ $this->countParStatut('valide') }})
                    </button>
                @endif
            </div>
        </div>

        {{-- ── Table ── --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-1.5 h-5 rounded-full" style="background:#d97706;"></div>
                <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">Fiches de paie — {{ $this->nomMois() }} {{ $annee }}</span>
            </div>

            @if ($paiements->isEmpty())
                <div class="py-20 text-center">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#fef3c7;">
                        <svg class="w-8 h-8" style="color:#fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="font-semibold text-gray-700 mb-1">Aucune fiche générée pour ce mois</p>
                    <p class="text-sm text-gray-400 mb-4">{{ $employes->count() }} employé(s) avec profil disponibles</p>
                    @if ($employes->count() > 0)
                        <button wire:click="generer" class="inline-flex items-center gap-2 px-5 py-2.5 text-white text-sm font-semibold rounded-xl shadow" style="background:#d97706;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Générer maintenant
                        </button>
                    @else
                        <p class="text-sm text-gray-400">Aucun employé avec un profil salaire assigné.</p>
                    @endif
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-bold uppercase tracking-wider text-white" style="background:#111827;">
                            <th class="px-6 py-4">Employé</th>
                            <th class="px-6 py-4">Profil / Échelon</th>
                            <th class="px-6 py-4 text-right">Base</th>
                            <th class="px-6 py-4 text-right">Ind.</th>
                            <th class="px-6 py-4 text-right">Ret.</th>
                            <th class="px-6 py-4 text-right">Net</th>
                            <th class="px-6 py-4 text-center">Statut</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($paiements as $i => $p)
                            @php $badge = $p->statut_badge; @endphp
                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-t border-gray-100 hover:bg-amber-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background:#d97706;">
                                            {{ strtoupper(substr($p->employe->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ strtoupper($p->employe->name) }} {{ $p->employe->lastname }}</p>
                                            <p class="text-xs text-gray-400">{{ $p->employe->matricule ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-semibold text-gray-700">{{ $p->profil?->nom ?? '—' }}</p>
                                    @if ($p->echelon)
                                        <p class="text-xs text-gray-400">Éch. {{ $p->echelon->numero }} — {{ $p->echelon->libelle }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-xs font-medium text-gray-700">
                                    {{ number_format($p->salaire_base, 0, ',', ' ') }}
                                </td>
                                <td class="px-6 py-4 text-right text-xs font-semibold" style="color:#15803d;">
                                    +{{ number_format($p->total_indemnites, 0, ',', ' ') }}
                                </td>
                                <td class="px-6 py-4 text-right text-xs font-semibold" style="color:#dc2626;">
                                    -{{ number_format($p->total_retenues, 0, ',', ' ') }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold" style="color:#059669;">
                                    {{ number_format($p->salaire_net, 0, ',', ' ') }}
                                    <span class="text-xs text-gray-400 font-normal ml-0.5">FCFA</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full"
                                          style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};">
                                        @if ($p->statut === 'en_attente')
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @elseif ($p->statut === 'valide')
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                        {{ $badge['label'] }}
                                    </span>
                                    @if ($p->statut === 'paye' && $p->paye_le)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $p->paye_le->format('d/m/Y') }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Valider --}}
                                        @if ($p->statut === 'en_attente')
                                            <button wire:click="validerUn({{ $p->id }})"
                                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg text-white"
                                                style="background:#1d4ed8;" title="Valider">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                                Valider
                                            </button>
                                        @endif
                                        {{-- Payer --}}
                                        @if ($p->statut === 'valide')
                                            <button wire:click="payerUn({{ $p->id }})"
                                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg text-white"
                                                style="background:#059669;" title="Marquer payé">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Payer
                                            </button>
                                        @endif
                                        {{-- Prévisualiser --}}
                                        <button wire:click="previsualiser({{ $p->id }})"
                                            class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-indigo-100 flex items-center justify-center text-gray-500 hover:text-indigo-700 transition"
                                            title="Prévisualiser bulletin A4">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        {{-- Ajustements (bonus / retenue) --}}
                                        @if ($p->statut !== 'paye')
                                            <button wire:click="ouvrirAjustements({{ $p->employe->id }})"
                                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg text-white"
                                                style="background:#7c3aed;" title="Bonus / Retenue">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                                Ajust.
                                            </button>
                                        @endif
                                        {{-- Note --}}
                                        <button wire:click="ouvrirNote({{ $p->id }})"
                                            class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-yellow-100 flex items-center justify-center text-gray-500 hover:text-yellow-600 transition"
                                            title="Note">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                        </button>
                                        {{-- Annuler (seulement si pas payé) --}}
                                        @if ($p->statut !== 'paye')
                                            <button wire:click="annuler({{ $p->id }})"
                                                class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-red-100 flex items-center justify-center text-gray-500 hover:text-red-600 transition"
                                                title="Supprimer la fiche">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 border-t-2 border-gray-200">
                            <td colspan="5" class="px-6 py-3 text-right text-sm font-bold text-gray-700">Total masse salariale :</td>
                            <td class="px-6 py-3 text-right text-base font-bold" style="color:#059669;">
                                {{ number_format($this->totalNet(), 0, ',', ' ') }} FCFA
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

    {{-- ══ Modal confirmation tout ══ --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showConfirmAll ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
                @if ($actionConfirm === 'valider')
                    <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#dbeafe;">
                        <svg class="w-7 h-7" style="color:#1d4ed8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Valider tous les paiements ?</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ $this->countParStatut('en_attente') }} fiche(s) en attente seront validées.</p>
                    <div class="flex gap-3">
                        <button wire:click="$set('showConfirmAll',false)" class="flex-1 py-2.5 text-sm font-semibold text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50">Annuler</button>
                        <button wire:click="validerTout" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#1d4ed8;">Valider</button>
                    </div>
                @else
                    <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#dcfce7;">
                        <svg class="w-7 h-7" style="color:#15803d;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Marquer tout comme payé ?</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ $this->countParStatut('valide') }} fiche(s) validées seront marquées payées.</p>
                    <div class="flex gap-3">
                        <button wire:click="$set('showConfirmAll',false)" class="flex-1 py-2.5 text-sm font-semibold text-gray-700 border border-gray-300 rounded-xl hover:bg-gray-50">Annuler</button>
                        <button wire:click="payerTout" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#059669;">Confirmer</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ Modal note ══ --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showNoteModal ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-5 rounded-t-2xl flex items-center justify-between" style="background:#374151;">
                    <h3 class="font-bold text-white">Ajouter une note</h3>
                    <button wire:click="$set('showNoteModal',false)" class="text-white opacity-80 hover:opacity-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <textarea wire:model="noteTexte" rows="4"
                        class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-400 resize-none"
                        placeholder="Note optionnelle sur ce paiement…"></textarea>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button wire:click="$set('showNoteModal',false)" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Annuler</button>
                    <button wire:click="sauvegarderNote" class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl" style="background:#374151;">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ MODAL PRÉVISUALISATION A4 (iframe) ════════ --}}
    <div class="fixed inset-0 z-50" style="{{ $showPreview ? 'display:flex;background:rgba(0,0,0,0.75);' : 'display:none;' }}">
        <div class="flex flex-col w-full h-full">

            {{-- Barre outils --}}
            <div class="flex items-center justify-between px-6 py-3 flex-shrink-0" style="background:#1e293b;">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#d97706;">
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


    {{-- ══ Modal AJUSTEMENTS (bonus / retenue) ══ --}}
    <div class="fixed inset-0 z-50 overflow-y-auto" style="{{ $showAjustModal ? 'background:rgba(0,0,0,0.6);' : 'display:none;' }}">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="px-6 py-5 rounded-t-2xl flex items-center justify-between" style="background:#7c3aed;">
                    <div>
                        <h3 class="font-bold text-white">Ajustements de salaire</h3>
                        <p class="text-xs text-white/80">{{ $ajustUserName }} — {{ $this->nomMois() }} {{ $annee }}</p>
                    </div>
                    <button wire:click="$set('showAjustModal', false)" class="text-white opacity-80 hover:opacity-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Formulaire d'ajout --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2 flex gap-2">
                            <button type="button" wire:click="$set('ajustType','bonus')"
                                class="flex-1 py-2 rounded-lg text-sm font-semibold border {{ $ajustType === 'bonus' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-600 border-gray-300' }}">
                                + Bonus
                            </button>
                            <button type="button" wire:click="$set('ajustType','retenue')"
                                class="flex-1 py-2 rounded-lg text-sm font-semibold border {{ $ajustType === 'retenue' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-600 border-gray-300' }}">
                                − Retenue
                            </button>
                        </div>
                        <div class="col-span-2 flex gap-2">
                            <button type="button" wire:click="$set('ajustMode','fixe')"
                                class="flex-1 py-1.5 rounded-lg text-xs font-semibold border {{ $ajustMode === 'fixe' ? 'bg-violet-600 text-white border-violet-600' : 'bg-white text-gray-600 border-gray-300' }}">
                                Montant fixe (FCFA)
                            </button>
                            <button type="button" wire:click="$set('ajustMode','pourcentage')"
                                class="flex-1 py-1.5 rounded-lg text-xs font-semibold border {{ $ajustMode === 'pourcentage' ? 'bg-violet-600 text-white border-violet-600' : 'bg-white text-gray-600 border-gray-300' }}">
                                % du salaire de base
                            </button>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Libellé</label>
                            <input type="text" wire:model="ajustLibelle" placeholder="{{ $ajustType === 'bonus' ? 'Prime de rendement…' : 'Retard, absence…' }}"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-400">
                            @error('ajustLibelle') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">{{ $ajustMode === 'pourcentage' ? 'Pourcentage (%)' : 'Montant (FCFA)' }}</label>
                            <input type="number" wire:model="ajustMontant" min="0" step="{{ $ajustMode === 'pourcentage' ? '0.01' : '1' }}" placeholder="0"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-400">
                            @error('ajustMontant') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Motif (optionnel)</label>
                            <input type="text" wire:model="ajustMotif" placeholder="Précision…"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-400">
                        </div>
                    </div>

                    {{-- Portée : ce mois ou plusieurs mois --}}
                    @php $moisListe = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']; @endphp
                    <div class="mt-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Appliquer à</p>
                        <div class="flex gap-2">
                            <button type="button" wire:click="$set('ajustPortee','mois')"
                                class="flex-1 py-1.5 rounded-lg text-xs font-semibold border {{ $ajustPortee === 'mois' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-300' }}">
                                Ce mois ({{ $this->nomMois() }} {{ $annee }})
                            </button>
                            <button type="button" wire:click="$set('ajustPortee','plage')"
                                class="flex-1 py-1.5 rounded-lg text-xs font-semibold border {{ $ajustPortee === 'plage' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-300' }}">
                                Plusieurs mois
                            </button>
                        </div>
                        @if ($ajustPortee === 'plage')
                            <div class="grid grid-cols-2 gap-3 mt-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Du</label>
                                    <div class="flex gap-2">
                                        <select wire:model="ajustMoisDebut" class="flex-1 px-2 py-1.5 text-xs border border-gray-300 rounded-lg bg-white">
                                            @foreach ($moisListe as $n => $nom) @if ($n > 0)<option value="{{ $n }}">{{ $nom }}</option>@endif @endforeach
                                        </select>
                                        <select wire:model="ajustAnneeDebut" class="w-20 px-2 py-1.5 text-xs border border-gray-300 rounded-lg bg-white">
                                            @for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++)<option value="{{ $y }}">{{ $y }}</option>@endfor
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Au</label>
                                    <div class="flex gap-2">
                                        <select wire:model="ajustMoisFin" class="flex-1 px-2 py-1.5 text-xs border border-gray-300 rounded-lg bg-white">
                                            @foreach ($moisListe as $n => $nom) @if ($n > 0)<option value="{{ $n }}">{{ $nom }}</option>@endif @endforeach
                                        </select>
                                        <select wire:model="ajustAnneeFin" class="w-20 px-2 py-1.5 text-xs border border-gray-300 rounded-lg bg-white">
                                            @for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++)<option value="{{ $y }}">{{ $y }}</option>@endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1.5">Le même {{ $ajustType === 'bonus' ? 'bonus' : 'retenue' }} sera créé pour chaque mois de la plage (bulletins non payés recalculés).</p>
                        @endif
                    </div>

                    <button wire:click="ajouterAjustement"
                        class="mt-4 w-full py-2.5 rounded-lg text-white text-sm font-semibold" style="background:#7c3aed;">
                        Appliquer l'ajustement
                    </button>

                    {{-- Liste des ajustements du mois --}}
                    <div class="mt-6">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Ajustements du mois</p>
                        @forelse ($this->ajustements as $aj)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                <div>
                                    <span class="text-sm font-medium text-gray-800">{{ $aj->libelle }}</span>
                                    @if ($aj->motif)<span class="text-xs text-gray-400"> — {{ $aj->motif }}</span>@endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold {{ $aj->type === 'bonus' ? 'text-emerald-600' : 'text-red-500' }}">
                                        {{ $aj->type === 'bonus' ? '+' : '−' }}{{ $aj->mode === 'pourcentage' ? $aj->montant.' % base' : number_format($aj->montant, 0, ',', ' ').' FCFA' }}
                                    </span>
                                    <button wire:click="supprimerAjustement({{ $aj->id }})" class="text-gray-400 hover:text-red-500" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">Aucun ajustement ce mois.</p>
                        @endforelse
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end">
                    <button wire:click="$set('showAjustModal', false)" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    @if ($showNotification)
        <div class="fixed bottom-6 right-6 z-50"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => { show = false; $wire.set('showNotification', false) }, 3500)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl text-sm font-medium"
                 style="{{ $notificationType === 'success' ? 'background:#111827;color:white;' : 'background:#dc2626;color:white;' }}">
                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
                     style="{{ $notificationType === 'success' ? 'background:#22c55e;' : 'background:#f87171;' }}">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                {{ $notificationMessage }}
            </div>
        </div>
    @endif

</div>
@endvolt
</x-layouts.app>