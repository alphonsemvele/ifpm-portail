<?php

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\Ue;
use App\Models\Examen;
use App\Models\Note;
use App\Models\User;
use App\Models\Cycle;

name('admin.releve');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search      = '';
    public $cycle_id           = null;
    public $examen_id          = null;
    public $departement_id     = null;
    public $filiere_id         = null;
    public $specialite_id      = null;

    public $cycles       = [];
    public $examens      = [];
    public $departements = [];
    public $filieres     = [];
    public $specialites  = [];

    public function mount()
    {
        $this->cycles       = Cycle::orderBy('name')->get();
        $this->examens      = collect();
        $this->departements = collect();
        $this->filieres     = collect();
        $this->specialites  = collect();
    }

    public function updatingSearch()       { $this->resetPage(); }
    public function updatingCycleId()      { $this->resetPage(); }
    public function updatingExamenId()     { $this->resetPage(); }
    public function updatingSpecialiteId() { $this->resetPage(); }

    // ── Cascades ──────────────────────────────────────────────────────────────
    public function updatedCycleId($value)
    {
        $this->examen_id      = null;
        $this->departement_id = null;
        $this->filiere_id     = null;
        $this->specialite_id  = null;
        $this->filieres       = collect();
        $this->specialites    = collect();

        if ($value) {
            $this->examens = Examen::where('cycle_id', $value)
                ->whereIn('statut', ['ouvert', 'en_cours', 'ferme'])
                ->orderBy('titre')->get();

            $this->departements = Departement::where('cycle_id', $value)
                ->where('status', 'Success')
                ->orderBy('nom')->get();
        } else {
            $this->examens      = collect();
            $this->departements = collect();
        }
    }

    public function updatedDepartementId($value)
    {
        $this->filiere_id    = null;
        $this->specialite_id = null;
        $this->specialites   = collect();
        $this->filieres = $value
            ? Filiere::where('departement_id', $value)->where('status', 'Success')->orderBy('name')->get()
            : collect();
    }

    public function updatedFiliereId($value)
    {
        $this->specialite_id = null;
        $this->specialites = $value
            ? Specialite::where('filiere_id', $value)->where('status', 'Success')->orderBy('name')->get()
            : collect();
    }

    // ── Apprenants ─────────────────────────────────────────────────────────────
    public function getEtudiantsProperty()
    {
        if (!$this->search && !$this->cycle_id) return null;

        $q = User::whereIn('role', ['student', 'etudiant'])
            ->where('status', 'Success')
            ->with(['specialite.filiere.departement.cycle']);

        if ($this->search) {
            $s = $this->search;
            $q->where(fn($sub) =>
                $sub->where('name', 'like', "%$s%")
                    ->orWhere('lastname',  'like', "%$s%")
                    ->orWhere('matricule', 'like', "%$s%")
            );
        }

        if ($this->specialite_id) {
            $q->where('specialite_id', $this->specialite_id);
        } elseif ($this->filiere_id) {
            $q->whereIn('specialite_id', Specialite::where('filiere_id', $this->filiere_id)->pluck('id'));
        } elseif ($this->departement_id) {
            $fIds = Filiere::where('departement_id', $this->departement_id)->pluck('id');
            $q->whereIn('specialite_id', Specialite::whereIn('filiere_id', $fIds)->pluck('id'));
        } elseif ($this->cycle_id) {
            $dIds = Departement::where('cycle_id', $this->cycle_id)->pluck('id');
            $fIds = Filiere::whereIn('departement_id', $dIds)->pluck('id');
            $q->whereIn('specialite_id', Specialite::whereIn('filiere_id', $fIds)->pluck('id'));
        }

        return $q->orderBy('name')->paginate(20);
    }

    // ── Export PDF ────────────────────────────────────────────────────────────
    public function exportReleve(int $etudiantId)
    {
        $etudiant = User::with(['specialite.filiere.departement.cycle'])->findOrFail($etudiantId);

        $ues = Ue::with('cours')
            ->where('specialite_id', $etudiant->specialite_id)
            ->when($this->examen_id, fn($q) => $q->where('examen_id', $this->examen_id))
            ->where('status', 'Success')
            ->orderBy('code')
            ->get();

        $notesQ = Note::where('etudiant_id', $etudiantId)
            ->whereIn('cours_id', $ues->flatMap(fn($ue) => $ue->cours->pluck('id')));
        if ($this->examen_id) $notesQ->where('examen_id', $this->examen_id);
        $allNotes = $notesQ->get()->keyBy('cours_id');

        $lignes = [];
        $totalCR = $totalCC = $totalPts = 0;

        foreach ($ues as $ue) {
            $lignesUE = [];
            foreach ($ue->cours as $cours) {
                $note   = $allNotes->get($cours->id);
                $cc     = $note?->cc;
                $exam   = $note?->exam;
                $ratt   = $note?->rattrapage;
                $credit = (int)($cours->credit ?? $cours->credits ?? 0);

                $examFinal = ($ratt !== null && $ratt > ($exam ?? 0)) ? $ratt : $exam;
                $moy = ($cc !== null && $examFinal !== null)
                    ? round(0.3 * $cc + 0.7 * $examFinal, 2) : null;

                $n100  = $moy !== null ? round($moy * 5, 2) : null;
                $cote  = $this->getCote($n100);
                $pts   = $credit * $this->getPointsDecision($cote);
                $cap   = ($moy !== null && $moy >= 10);

                $totalCR += $credit;
                if ($cap) { $totalCC += $credit; $totalPts += $pts; }

                $lignesUE[] = compact('cc', 'exam', 'ratt', 'credit', 'moy', 'n100', 'cote', 'pts', 'cap') + [
                    'code'      => $cours->code ?? $ue->code,
                    'intitule'  => $cours->name,
                    'dec'       => $cap ? 'Cap.' : 'NC',
                    'total_cxp' => $cap ? round($credit * $this->getPointsDecision($cote), 2) : null,
                    'annee_sem' => $note?->annee_sem ?? ($etudiant->annee_academique ?? date('Y') . '-' . (date('Y') + 1) . '/S1'),
                ];
            }
            $lignes[] = ['ue_code' => $ue->code, 'ue_name' => $ue->name, 'type' => $ue->type ?? 'Fondamentale', 'cours' => $lignesUE];
        }

        $pct     = $totalCR > 0 ? round($totalCC / $totalCR * 100, 2) : 0;
        $mgp     = $totalCC  > 0 ? round($totalPts / $totalCC, 2) : 0;
        $coteMGP = $this->getCote($mgp * 20);
        $dec     = $totalCC >= $totalCR ? 'Admis(e)' : 'Ajourné(e)';
        $examen  = $this->examen_id ? Examen::find($this->examen_id) : null;
        $fname   = 'releve_' . str($etudiant->name ?? 'etudiant')->slug() . '_' . now()->format('Ymd') . '.pdf';

        return response()->streamDownload(function () use ($etudiant, $lignes, $totalCR, $totalCC, $totalPts, $pct, $mgp, $coteMGP, $dec, $examen) {
            $html = $this->buildPdf($etudiant, $lignes, $totalCR, $totalCC, $totalPts, $pct, $mgp, $coteMGP, $dec, $examen);
            echo \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => false,
                    'defaultFont'          => 'DejaVu Sans',
                    'dpi'                  => 96,
                    'enable_php'           => false,
                ])
                ->output();
        }, $fname, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="' . $fname . '"']);
    }

    // ── HTML du PDF ───────────────────────────────────────────────────────────
    private function buildPdf($etudiant, $lignes, $totalCR, $totalCC, $totalPts, $pct, $mgp, $coteMGP, $dec, $examen): string
    {
        // Alias local pour htmlspecialchars (évite conflit avec $h() de Laravel)
        $h = fn($v): string => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
        $spe     = $etudiant->specialite;
        $fil     = $spe?->filiere;
        $dep     = $fil?->departement;
        $niveau  = $etudiant->niveau ?? $spe?->niveau ?? 'L1';
        $annee   = $etudiant->annee_academique ?? (date('Y') . '-' . (date('Y') + 1));
        $inst    = config('app.institution_name', 'IUM NDAZOA');
        $fac     = config('app.faculte_name',     'Institut Supérieur de Management');
        $dob     = $etudiant->date_naissance ? date('d/m/Y', strtotime($etudiant->date_naissance)) : '—';
        $nom     = strtoupper($etudiant->name ?? '') . ' ' . ($etudiant->lastname ?? '');
        $mat     = $etudiant->matricule ?? '—';
        $decCol  = $dec === 'Admis(e)' ? '#166534' : '#991b1b';

        $cotes = [
            ['80-100','A','4.00','Capitalisé (Mention Très Bien)'],
            ['75-79','A-','3.70','Capitalisé (Mention Bien)'],
            ['70-74','B+','3.30','Capitalisé (Mention Bien)'],
            ['65-69','B','3.00','Capitalisé (Mention Bien)'],
            ['60-64','B-','2.70','Capitalisé (Mention Assez-Bien)'],
            ['55-59','C+','2.30','Capitalisé (Mention Passable)'],
            ['50-54','C','2.00','Capitalisé (Mention Passable)'],
            ['45-49','C-','1.70','Capitalisé Non Transférable'],
            ['40-44','D+','1.30','Capitalisé Non Transférable'],
            ['33-39','D','1.00','Capitalisé Non Transférable'],
            ['27-32','E','0.00','Echec'],
            ['0-26','F','0.00','Echec'],
        ];

        // ── CSS ──────────────────────────────────────────────────────────────
        $css = '
body   { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #111; margin: 0; padding: 0; }
table  { border-collapse: collapse; }
.w100  { width: 100%; }
.tac   { text-align: center; }
.tar   { text-align: right; }
.tal   { text-align: left; }
.bold  { font-weight: bold; }
.ital  { font-style: italic; }
.small { font-size: 7pt; }
.xsm  { font-size: 6.5pt; }
.gray  { color: #555; }

/* Header */
.hdr td  { padding: 1mm 2mm; vertical-align: top; }
.logo-td { text-align: center; }
.logo-box{ border: 1.5px solid #333; border-radius: 50%; width: 20mm; height: 20mm;
           display: inline-block; text-align: center; padding-top: 4mm;
           font-size: 8pt; font-weight: bold; line-height: 1.3; }
.inst    { font-size: 9.5pt; font-weight: bold; margin-top: 1mm; }
.fac-nm  { font-size: 8pt; font-style: italic; }

/* Titre */
.titre   { text-align: center; font-size: 12pt; font-weight: bold;
           text-transform: uppercase; text-decoration: underline;
           margin: 3mm 0 1mm; letter-spacing: 0.5px; }
.session { text-align: center; font-size: 8pt; font-style: italic; color: #444; margin-bottom: 2mm; }

/* Ligne séparatrice */
.hr      { border-top: 1.5px solid #333; margin: 1.5mm 0; }

/* Infos apprenant */
.info td { padding: 1mm 2mm; vertical-align: bottom; }
.lbl     { font-size: 6.5pt; color: #555; }
.underln { border-bottom: 0.8px solid #555; padding-bottom: 0.3mm; display: block; }

/* Tableau notes */
.notes th { background: #e0e0e0; font-weight: bold; font-size: 7pt;
            border: 0.5px solid #666; padding: 1.2mm 1mm; text-align: center; }
.notes td { border: 0.5px solid #888; padding: 1.2mm 1mm; font-size: 7.5pt; text-align: center; }
.sec-hd td{ background: #c8c8c8; font-weight: bold; font-style: italic;
            font-size: 7.5pt; padding: 1.2mm; text-align: center;
            border: 0.5px solid #888; }
.tot  td  { background: #e0e0e0; font-weight: bold; font-size: 7.5pt;
            border: 0.5px solid #888; padding: 1.2mm 1mm; }
.cap      { color: #166534; font-weight: bold; }
.nc       { color: #991b1b; }

/* Système de notation */
.sn th { background: #e0e0e0; font-weight: bold; font-size: 6pt;
         border: 0.5px solid #aaa; padding: 0.8mm 0.8mm; }
.sn td { border: 0.5px solid #aaa; padding: 0.7mm 0.8mm; font-size: 6pt; text-align: center; }

/* Résumé */
.res td  { border: 0.5px solid #aaa; padding: 1mm 2mm; font-size: 7.5pt; }
.rv      { font-weight: bold; text-align: center; }

/* Signatures */
.sig td  { text-align: center; font-size: 7.5pt; padding: 1mm 3mm; vertical-align: bottom; }
.sig-ln  { border-top: 0.8px solid #333; margin-top: 10mm; padding-top: 0.8mm; }
.dt      { text-align: center; font-size: 8.5pt; font-weight: bold; margin: 2mm 0; }
.nb      { font-size: 6pt; color: #555; font-style: italic; margin-top: 2mm; }
';

        ob_start();
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">';
        echo '<style>' . $css . '</style></head><body>';

        // ── EN-TÊTE ──────────────────────────────────────────────────────────
        echo '<table class="w100 hdr"><tr>';
        echo '<td width="30%">' . $h($inst) . '<br><span class="small">REPUBLIQUE DU CAMEROUN</span><br><span class="xsm ital">Paix-Travail-Patrie</span></td>';
        echo '<td width="40%" class="logo-td">';
        echo '<div class="logo-box">IUM<br>NDAZOA</div><br>';
        echo '<span class="inst">' . $h($fac) . '</span><br>';
        echo '<span class="fac-nm">' . $h($dep?->nom ?? 'Département') . '</span><br>';
        echo '<span class="xsm gray">BP — Yaoundé-Cameroun</span>';
        echo '</td>';
        echo '<td width="30%" class="tar">' . $h($inst) . '<br><span class="small">REPUBLIC OF CAMEROON</span><br><span class="xsm ital">Peace-Work-Fatherland</span></td>';
        echo '</tr></table>';

        echo '<div class="hr"></div>';
        echo '<div class="titre">RELEVÉ DE NOTES / TRANSCRIPT</div>';
        if ($examen) {
            echo '<div class="session">Session : ' . $h($examen->titre) . '</div>';
        }

        // ── INFOS ÉTUDIANT ────────────────────────────────────────────────────
        echo '<table class="w100 info"><tr>';
        echo '<td width="52%"><span class="xsm gray lbl">Nom(s) et Prénom(s) / <em>Surname and name</em> :</span><span class="underln bold">' . $h($nom) . '</span></td>';
        echo '<td width="24%"><span class="xsm gray lbl">Matricule / <em>Registration N°</em> :</span><span class="underln bold">' . $h($mat) . '</span></td>';
        echo '<td width="24%"><span class="xsm gray lbl">Année / <em>Year</em> :</span><span class="underln">' . $h($annee) . '</span></td>';
        echo '</tr><tr>';
        echo '<td><span class="xsm gray lbl">Né(e) le / <em>Born on</em> :</span><span class="underln">' . $dob . '</span></td>';
        echo '<td><span class="xsm gray lbl">Filière / <em>Discipline</em> :</span><span class="underln">' . $h($fil?->name ?? '—') . '</span></td>';
        echo '<td><span class="xsm gray lbl">Niveau / <em>Level</em> :</span><span class="underln">' . $h($niveau) . '</span></td>';
        echo '</tr></table>';

        echo '<div class="hr"></div>';

        // ── TABLEAU DES NOTES ─────────────────────────────────────────────────
        echo '<table class="w100 notes">';
        echo '<thead><tr>';
        echo '<th width="9%"  class="tal" style="padding-left:1.5mm;">Code UE</th>';
        echo '<th width="30%" class="tal" style="padding-left:1.5mm;">Intitulé / <em>Title</em></th>';
        echo '<th width="7%">Crédits<br><em>(c)</em></th>';
        echo '<th width="9%">Note/100</th>';
        echo '<th width="6%">Cote</th>';
        echo '<th width="8%">Points<br><em>(p)</em></th>';
        echo '<th width="6%">Déc</th>';
        echo '<th width="9%">Total<br><em>(cxp)</em></th>';
        echo '<th width="16%">Année/Sem</th>';
        echo '</tr></thead><tbody>';

        $grpActuel = null;
        foreach ($lignes as $ug) {
            if ($grpActuel !== $ug['type']) {
                $grpActuel = $ug['type'];
                $label = (stripos($grpActuel, 'fond') !== false || stripos($grpActuel, 'base') !== false)
                    ? 'Unités d\'Enseignement Fondamentales — <em>Fundamental Courses</em>'
                    : 'Unités d\'Enseignement de Spécialisation — <em>Specialisation Courses</em>';
                echo '<tr class="sec-hd"><td colspan="9">' . $label . '</td></tr>';
            }
            foreach ($ug['cours'] as $l) {
                $cls = $l['cap'] ? 'cap' : 'nc';
                echo '<tr>';
                echo '<td class="tal" style="padding-left:1.5mm;">' . $h($l['code'])     . '</td>';
                echo '<td class="tal" style="padding-left:1.5mm;">' . $h($l['intitule']) . '</td>';
                echo '<td>' . $l['credit']                                               . '</td>';
                echo '<td>' . ($l['n100']     !== null ? number_format($l['n100'], 2) : '—') . '</td>';
                echo '<td>' . $h($l['cote'] ?? '—')                                      . '</td>';
                echo '<td>' . ($l['pts']      !== null ? number_format($l['pts'],  2) : '—') . '</td>';
                echo '<td class="' . $cls . '">' . $l['dec']                            . '</td>';
                echo '<td>' . ($l['total_cxp'] !== null ? number_format($l['total_cxp'], 2) : '—') . '</td>';
                echo '<td>' . $h($l['annee_sem'])                                         . '</td>';
                echo '</tr>';
            }
        }

        echo '<tr class="tot">';
        echo '<td colspan="2" class="tar ital" style="padding-right:2mm;">Totaux / <em>Totals</em></td>';
        echo '<td>' . $totalCR  . '</td><td>—</td><td>—</td>';
        echo '<td>' . number_format($totalPts, 2) . '</td>';
        echo '<td>—</td><td>' . $totalCC . '</td><td>—</td>';
        echo '</tr>';
        echo '</tbody></table>';

        // ── BAS DE PAGE : notation + résumé ──────────────────────────────────
        echo '<table class="w100" style="margin-top:3mm;"><tr>';

        // Colonne gauche : système de notation
        echo '<td width="50%" style="vertical-align:top; padding-right:3mm;">';
        echo '<table class="w100 sn"><thead>';
        echo '<tr><th colspan="4" style="background:#c8c8c8;font-size:6.5pt;">Système de notation</th></tr>';
        echo '<tr><th>Note/100</th><th>Cote</th><th>Pts Décision</th><th class="tal" style="padding-left:1mm;">Mention</th></tr>';
        echo '</thead><tbody>';
        foreach ($cotes as [$rng, $ct, $pd, $mn]) {
            echo '<tr><td>' . $rng . '</td><td>' . $ct . '</td><td>' . $pd . '</td><td class="tal" style="padding-left:1mm;">' . $mn . '</td></tr>';
        }
        echo '</tbody></table></td>';

        // Colonne droite : résumé + signatures
        echo '<td width="50%" style="vertical-align:top;">';

        echo '<table class="w100 res">';
        echo '<tr><td class="ital">Total des crédits requis / <em>Total credits required</em> :</td><td class="rv" width="20%">' . $totalCR . '</td></tr>';
        echo '<tr><td class="ital">Total des crédits capitalisés / <em>Total credits capitalized</em> :</td><td class="rv">' . $totalCC . '</td></tr>';
        echo '<tr><td class="ital">Pourcentage / <em>Percentage</em> :</td><td class="rv">' . number_format($pct, 2) . '%</td></tr>';
        echo '<tr><td class="ital">Moyenne Générale Pondérée / <em>GPA</em> :</td><td class="rv">' . number_format($mgp, 2) . '</td></tr>';
        echo '<tr><td class="ital">Cote / <em>Grade</em> :</td><td class="rv">' . $h($coteMGP) . '</td></tr>';
        echo '<tr style="background:#efefef;"><td class="ital bold">Décision / <em>Decision</em> :</td>';
        echo '<td class="rv" style="color:' . $decCol . '; font-size:9pt;"><strong>' . $dec . '</strong></td></tr>';
        echo '</table>';

        // Date + signatures
        echo '<p class="dt">Yaoundé, le ' . date('d/m/Y') . '</p>';
        echo '<table class="w100 sig"><tr>';
        echo '<td>Le Chef de Département<br><em>The Head of Department</em><div class="sig-ln">Signature</div></td>';
        echo '<td>Le Coordinateur<br><em>The Coordinator</em><div class="sig-ln">Signature</div></td>';
        echo '<td>Le Doyen<br><em>The Dean</em><div class="sig-ln">Signature</div></td>';
        echo '</tr></table>';

        echo '</td></tr></table>';

        echo '<p class="nb">NB : Il n\'est délivré qu\'un seul exemplaire de relevé de notes. Le titulaire peut établir et faire certifier des copies conformes.<br>';
        echo '<em>Only one transcript shall be delivered. It is the owner\'s interest to have certified true copies made.</em></p>';

        echo '</body></html>';
        return ob_get_clean();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function getCote($n100): string
    {
        if ($n100 === null) return '—';
        $n = (float)$n100;
        return match(true) {
            $n >= 80 => 'A',  $n >= 75 => 'A-',
            $n >= 70 => 'B+', $n >= 65 => 'B',  $n >= 60 => 'B-',
            $n >= 55 => 'C+', $n >= 50 => 'C',  $n >= 45 => 'C-',
            $n >= 40 => 'D+', $n >= 33 => 'D',  $n >= 27 => 'E',
            default  => 'F',
        };
    }

    private function getPointsDecision(string $cote): float
    {
        return match($cote) {
            'A'  => 4.00, 'A-' => 3.70,
            'B+' => 3.30, 'B'  => 3.00, 'B-' => 2.70,
            'C+' => 2.30, 'C'  => 2.00, 'C-' => 1.70,
            'D+' => 1.30, 'D'  => 1.00,
            default => 0.00,
        };
    }
};

?>

<x-layouts.app header="true">
    @volt
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- ─── Header ─── --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Relevé de Notes</h1>
                <p class="text-gray-500">Recherchez un apprenant et téléchargez son relevé officiel.</p>
            </div>
            <a href="/admin" class="text-gray-400 hover:text-indigo-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                
                
            </div>
        </div>

        {{-- ─── Recherche directe ─── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5">
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
                Recherche directe — sans filtre
            </p>
            <div class="relative max-w-lg">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg wire:loading.remove wire:target="search" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                    <svg wire:loading wire:target="search" class="w-5 h-5 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.400ms="search"
                    placeholder="Nom, prénom ou matricule…"
                    class="w-full pl-10 pr-10 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl
                           focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400
                           placeholder-gray-400 text-gray-700 transition-all duration-150"/>
                @if ($search)
                    <button wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>
            @if ($search)
                <p class="text-xs text-indigo-500 mt-2 ml-1" wire:loading.remove wire:target="search">
                    @if ($this->etudiants)
                        {{ $this->etudiants->total() }} apprenant(s) pour <span class="font-semibold">"{{ $search }}"</span>
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-2 ml-1" wire:loading wire:target="search">Recherche en cours…</p>
            @endif
        </div>

        {{-- ─── Filtres ─── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filtrer par promotion &amp; session
            </p>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">

                {{-- Cycle --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Cycle</label>
                    <select wire:model.live="cycle_id"
                        class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">— Cycle —</option>
                        @foreach ($cycles as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Examen --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Examen / Session
                        @if (!$cycle_id)<span class="text-gray-300 text-[10px]">(cycle d'abord)</span>@endif
                    </label>
                    <select wire:model.live="examen_id" wire:key="ex-{{ $cycle_id }}"
                        class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$cycle_id)>
                        <option value="">— Examen —</option>
                        @foreach ($examens as $ex)
                            <option value="{{ $ex->id }}">{{ $ex->titre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Département --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Département
                        @if (!$cycle_id)<span class="text-gray-300 text-[10px]">(cycle d'abord)</span>@endif
                    </label>
                    <select wire:model.live="departement_id" wire:key="dep-{{ $cycle_id }}"
                        class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$cycle_id)>
                        <option value="">— Département —</option>
                        @foreach ($departements as $d)
                            <option value="{{ $d->id }}">{{ $d->nom }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filière --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Filière
                        @if (!$departement_id)<span class="text-gray-300 text-[10px]">(département d'abord)</span>@endif
                    </label>
                    <select wire:model.live="filiere_id" wire:key="fil-{{ $departement_id }}"
                        class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$departement_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$departement_id)>
                        <option value="">— Filière —</option>
                        @foreach ($filieres as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Spécialité --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Spécialité
                        @if (!$filiere_id)<span class="text-gray-300 text-[10px]">(filière d'abord)</span>@endif
                    </label>
                    <select wire:model.live="specialite_id" wire:key="spe-{{ $filiere_id }}"
                        class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$filiere_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$filiere_id)>
                        <option value="">— Spécialité —</option>
                        @foreach ($specialites as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- Badge session --}}
            <div class="mt-3">
                @if ($examen_id)
                    @php $exNom = $examens->firstWhere('id', $examen_id)?->titre ?? '—'; @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-semibold rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Session : {{ $exNom }} — notes filtrées par cette session
                    </span>
                @else
                    <span class="text-xs text-amber-600 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        Aucune session — le relevé inclura toutes les notes disponibles
                    </span>
                @endif
            </div>
        </div>

        {{-- ─── Liste apprenants ─── --}}
        @if (!$search && !$cycle_id)
            <div class="bg-white rounded-2xl shadow-sm p-16 text-center text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-base font-medium text-gray-500">Recherchez un apprenant ou sélectionnez un filtre</p>
                <p class="text-sm mt-1">Utilisez la barre de recherche ou les filtres par promotion.</p>
            </div>

        @elseif ($this->etudiants && $this->etudiants->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm p-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="font-medium">Aucun apprenant trouvé.</p>
                <p class="text-sm mt-1">Modifiez votre recherche ou vos filtres.</p>
            </div>

        @elseif ($this->etudiants)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-700">{{ $this->etudiants->total() }} apprenant(s)</p>
                    <p class="text-xs text-gray-400">Cliquez sur « Télécharger » pour générer le relevé PDF</p>
                </div>

                <table class="w-full text-sm text-left">
                    <thead class="bg-indigo-50 text-indigo-700 text-xs uppercase tracking-wide border-b border-indigo-100">
                        <tr>
                            <th class="py-3 px-5">#</th>
                            <th class="py-3 px-5">Apprenant</th>
                            <th class="py-3 px-5">Matricule</th>
                            <th class="py-3 px-5">Filière / Spécialité</th>
                            <th class="py-3 px-5">Niveau</th>
                            <th class="py-3 px-5 text-center">Relevé PDF</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($this->etudiants as $etudiant)
                            <tr class="hover:bg-indigo-50/30 transition-colors" wire:key="e{{ $etudiant->id }}">
                                <td class="py-4 px-5 text-gray-400 text-xs font-medium">
                                    {{ ($this->etudiants->currentPage() - 1) * $this->etudiants->perPage() + $loop->iteration }}
                                </td>
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 text-indigo-700 font-bold text-sm">
                                            {{ strtoupper(substr($etudiant->name ?? 'E', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800">
                                                {{ strtoupper($etudiant->name ?? '') }}
                                                <span class="font-normal text-gray-600">{{ $etudiant->lastname ?? '' }}</span>
                                            </p>
                                            <p class="text-xs text-gray-400">{{ $etudiant->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 font-mono">
                                        {{ $etudiant->matricule ?? '—' }}
                                    </span>
                                </td>
                                <td class="py-4 px-5">
                                    <p class="text-xs font-medium text-gray-700">{{ $etudiant->specialite?->filiere?->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-400">{{ $etudiant->specialite?->name ?? '' }}</p>
                                </td>
                                <td class="py-4 px-5 text-gray-600 text-xs">
                                    {{ $etudiant->niveau ?? $etudiant->specialite?->niveau ?? '—' }}
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <button
                                        wire:click="exportReleve({{ $etudiant->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="exportReleve({{ $etudiant->id }})"
                                        class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700
                                               text-white text-xs font-semibold py-2 px-4 rounded-lg
                                               transition-all duration-150 shadow-sm disabled:opacity-60 disabled:cursor-wait">
                                        <svg wire:loading.remove wire:target="exportReleve({{ $etudiant->id }})"
                                             class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <svg wire:loading wire:target="exportReleve({{ $etudiant->id }})"
                                             class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                        </svg>
                                        <span wire:loading.remove wire:target="exportReleve({{ $etudiant->id }})">Télécharger</span>
                                        <span wire:loading       wire:target="exportReleve({{ $etudiant->id }})">Génération…</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                @if ($this->etudiants->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        <nav class="flex items-center justify-between select-none">
                            <div class="text-sm text-gray-500">
                                <span class="font-semibold text-gray-700">{{ $this->etudiants->firstItem() }}</span>
                                – <span class="font-semibold text-gray-700">{{ $this->etudiants->lastItem() }}</span>
                                sur <span class="font-semibold text-gray-700">{{ $this->etudiants->total() }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                @if ($this->etudiants->onFirstPage())
                                    <span class="inline-flex items-center px-3 py-2 text-sm text-gray-300 bg-white border border-gray-200 rounded-lg cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </span>
                                @else
                                    <button wire:click="previousPage" class="inline-flex items-center px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                @endif
                                @php $cur=$this->etudiants->currentPage(); $lst=$this->etudiants->lastPage(); $st=max(1,$cur-2); $en=min($lst,$cur+2); @endphp
                                @if ($st > 1)<button wire:click="gotoPage(1)" class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all">1</button>@if($st>2)<span class="px-1 text-gray-400">…</span>@endif@endif
                                @for ($p=$st;$p<=$en;$p++)
                                    @if ($p===$cur)<span class="inline-flex items-center justify-center w-9 h-9 text-sm font-bold text-white bg-indigo-600 border border-indigo-600 rounded-lg">{{$p}}</span>
                                    @else<button wire:click="gotoPage({{$p}})" class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all">{{$p}}</button>@endif
                                @endfor
                                @if ($en < $lst)@if($en<$lst-1)<span class="px-1 text-gray-400">…</span>@endif<button wire:click="gotoPage({{$lst}})" class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all">{{$lst}}</button>@endif
                                @if ($this->etudiants->hasMorePages())
                                    <button wire:click="nextPage" class="inline-flex items-center px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                @else
                                    <span class="inline-flex items-center px-3 py-2 text-sm text-gray-300 bg-white border border-gray-200 rounded-lg cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                @endif
                            </div>
                        </nav>
                    </div>
                @endif
            </div>
        @endif

    </div>
    @endvolt
</x-layouts.app>