<?php

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\Ue;
use App\Models\Examen;
use App\Models\Note;
use App\Models\User;
use App\Models\Cycle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

name('admin.barbillard');
middleware(['auth', 'verified']);

new class extends Component {

    public $cycle_id       = null;
    public $departement_id = null;
    public $filiere_id     = null;
    public $specialite_id  = null;
    public $examen_id      = null;
    public $cycles       = [];
    public $departements = [];
    public $filieres     = [];
    public $specialites  = [];
    public $examens      = [];

    public $ues       = [];
    public $etudiants = [];
    public $notes     = [];
    public $moyUE     = [];
    public $moyGen    = [];
    public $rangs     = [];
    public $stats     = [];
    public $tableauGenere     = false;
    public string $mode = 'avant'; // 'avant' ou 'apres'

    public function mount()
    {
        $this->cycles       = Cycle::orderBy('name')->get();
        $this->departements = collect();
        $this->examens      = collect();
        $this->filieres     = collect();
        $this->specialites  = collect();
    }

    // ── Cascades ──────────────────────────────────────────────────────────────

    public function updatedCycleId($value)
    {
        // Reset toute la cascade en aval — identique à rattrapage
        $this->examen_id      = null;
        $this->departement_id = null;
        $this->filiere_id     = null;
        $this->specialite_id  = null;
        $this->filieres       = collect();
        $this->specialites    = collect();
        $this->tableauGenere  = false;
        $this->clearTableau();

        if ($value) {
            // Examens du cycle
            $this->examens = Examen::where('cycle_id', $value)
                ->whereIn('statut', ['ouvert', 'en_cours', 'ferme'])
                ->orderBy('titre')
                ->get();

            // Départements du cycle (filtre status = 'Success')
            $this->departements = Departement::where('cycle_id', $value)
                ->where('status', 'Success')
                ->orderBy('nom')
                ->get();
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
        $this->tableauGenere = false;
        $this->clearTableau();

        $this->filieres = $value
            ? Filiere::where('departement_id', $value)
                     ->where('status', 'Success')
                     ->orderBy('name')
                     ->get()
            : collect();
    }

    public function updatedFiliereId($value)
    {
        $this->specialite_id = null;
        $this->tableauGenere = false;
        $this->clearTableau();

        $this->specialites = $value
            ? Specialite::where('filiere_id', $value)
                        ->where('status', 'Success')
                        ->orderBy('name')
                        ->get()
            : collect();
    }

    public function updatedSpecialiteId($value)
    {
        $this->tableauGenere = false;
        $this->clearTableau();
        // Si examen déjà sélectionné → on peut déjà générer (bouton s'active)
    }

    public function updatedExamenId($value)
    {
        $this->tableauGenere = false;
        $this->clearTableau();
        // Si spécialité déjà sélectionnée → on peut déjà générer (bouton s'active)
    }

    public function updatedModeRattrapage()
    {
        // Si tableau déjà généré, on le régénère avec le nouveau mode
        if ($this->tableauGenere && $this->specialite_id && $this->examen_id) {
            $this->chargerDonnees();
        }
    }

    public function updatedMode()
    {
        // Re-générer le tableau si déjà affiché
        if ($this->specialite_id && $this->examen_id) {
            $this->genererTableau();
        }
    }

    // ── Génération ────────────────────────────────────────────────────────────

    public function genererTableau()
    {
        $this->tableauGenere = false;

        if (!$this->specialite_id || !$this->examen_id) {
            session()->flash('erreur_barbillard', 'Veuillez sélectionner une spécialité et un examen.');
            return;
        }

        try {
            $this->chargerDonnees();
            $this->tableauGenere = true;
        } catch (\Exception $e) {
            logger('Erreur barbillard: ' . $e->getMessage());
            $this->tableauGenere = true;
        }
    }

    // ── Chargement centralisé ─────────────────────────────────────────────────

    private function chargerDonnees(): void
    {
        // On filtre aussi les COURS rattachés à l'UE (pas seulement les UE).
        // Sans ça, des cours désactivés ou rattachés à un autre examen
        // remontaient dans le tableau avec un crédit "?" et aucune note.
        $this->ues = Ue::with(['cours' => function ($q) {
                $q->where('status', 'Success')
                  ->where('examen_id', $this->examen_id);
            }])
            ->where('specialite_id', $this->specialite_id)
            ->where('examen_id', $this->examen_id)
            ->where('status', 'Success')
            ->orderBy('code')
            ->get();

        $this->etudiants = User::where('specialite_id', $this->specialite_id)
            ->whereIn('role', ['student', 'etudiant'])
            ->where('status', 'Success')
            ->orderBy('name')
            ->get();

        if ($this->etudiants->isEmpty() || $this->ues->isEmpty()) return;

        $notesDB = Note::where('examen_id', $this->examen_id)
            ->whereIn('etudiant_id', $this->etudiants->pluck('id'))
            ->whereIn('cours_id', $this->ues->flatMap(fn($ue) => $ue->cours->pluck('id')))
            ->get()
            ->groupBy('etudiant_id');

        $modeApres = ($this->mode === 'apres');

        $this->notes = $this->moyUE = $this->moyGen = [];

        foreach ($this->etudiants as $etudiant) {
            $notesEtu     = $notesDB->get($etudiant->id, collect())->keyBy('cours_id');
            $sommeGen     = 0;
            $totalCredits = 0;

            foreach ($this->ues as $ue) {
                $sommeUE = 0; $nbCours = 0;

                foreach ($ue->cours as $cours) {
                    $note       = $notesEtu->get($cours->id);
                    $rattrapage = $note?->rattrapage;

                    // CC ne change jamais — on ne touche pas au CC
                    $cc = $note?->cc;

                    if ($modeApres && $rattrapage !== null) {
                        // Mode après rattrapage : rattrapage remplace uniquement l'Exam
                        $exam = $rattrapage;
                        $moy  = ($cc !== null) ? round(0.3 * $cc + 0.7 * $rattrapage, 2) : null;
                    } else {
                        // Mode avant rattrapage : formule normale 0.3×CC + 0.7×Exam
                        $exam = $note?->exam;
                        $moy  = ($cc !== null && $exam !== null)
                            ? round(0.3 * $cc + 0.7 * $exam, 2)
                            : null;
                    }

                    // Renvoi au rattrapage : on ne juge qu'une fois l'examen saisi.
                    // Tant que l'Exam est vide, l'évaluation est incomplète → pas de renvoi.
                    $renvoye = ($exam !== null)
                        && (($cc !== null && $cc <= 5) || $exam <= 5);

                    $this->notes[$etudiant->id][$cours->id] = [
                        'cc'         => $cc,
                        'exam'       => $exam,
                        'moy'        => $moy,
                        'rattrapage' => $rattrapage,
                        'mode_apres' => $modeApres && $rattrapage !== null,
                        'renvoye'    => $renvoye,
                    ];

                    if ($moy !== null) { $sommeUE += $moy; $nbCours++; }

                    // Si un cours est renvoyé, on marque l'UE entière
                    if ($renvoye && !$modeApres) {
                        $this->moyUE[$etudiant->id][$ue->id . '_renvoye'] = true;
                    }
                }

                $moyUE = $nbCours > 0 ? round($sommeUE / $nbCours, 2) : null;

                // Si au moins un cours de l'UE a une note ≤ 5 (hors mode après),
                // l'apprenant est renvoyé → on n'affiche pas la moyenne UE
                $ueRenvoye = !$modeApres && ($this->moyUE[$etudiant->id][$ue->id . '_renvoye'] ?? false);
                if ($ueRenvoye) { $moyUE = null; }

                $this->moyUE[$etudiant->id][$ue->id] = $moyUE;

                if ($moyUE !== null) {
                    $sommeGen     += $moyUE * ($ue->credits ?? 1);
                    $totalCredits += ($ue->credits ?? 1);
                }
            }

            $this->moyGen[$etudiant->id] = $totalCredits > 0 ? round($sommeGen / $totalCredits, 2) : null;
        }

        // Rangs ex-aequo
        $rankMap = [];
        $sorted  = collect($this->moyGen)->filter(fn($v) => $v !== null)->sortDesc();
        $rank = 1; $prev = null; $cnt = 0;

        foreach ($sorted as $eid => $moy) {
            if ($prev !== null && $moy < $prev) { $rank += $cnt; $cnt = 0; }
            $rankMap[$eid] = $rank; $prev = $moy; $cnt++;
        }

        foreach ($this->etudiants as $e) {
            $this->rangs[$e->id] = isset($rankMap[$e->id])
                ? $rankMap[$e->id] . ' / ' . count($rankMap)
                : '—';
        }

        // Stats
        $moyennes = collect($this->moyGen)->filter(fn($v) => $v !== null);
        $admis    = $moyennes->filter(fn($v) => $v >= 10)->count();

        $this->stats = [
            'total'    => $this->etudiants->count(),
            'admis'    => $admis,
            'ajournes' => $moyennes->filter(fn($v) => $v < 10)->count(),
            'avg'      => $moyennes->isNotEmpty() ? round($moyennes->avg(), 2) : '—',
            'max'      => $moyennes->isNotEmpty() ? $moyennes->max() : '—',
            'min'      => $moyennes->isNotEmpty() ? $moyennes->min() : '—',
            'taux'     => $moyennes->isNotEmpty() ? round($admis / $moyennes->count() * 100) : 0,
        ];
    }

    // ── Export Excel ──────────────────────────────────────────────────────────

    public function exportExcel()
    {
        if (!$this->tableauGenere || empty($this->etudiants)) return;

        $examen     = Examen::find($this->examen_id);
        $specialite = Specialite::find($this->specialite_id);
        $filename   = 'barbillard_' . str($specialite?->name ?? 'export')->slug() . '_' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($examen, $specialite) {

            $spreadsheet = new Spreadsheet();
            $ws          = $spreadsheet->getActiveSheet()->setTitle('Barbillard');

            $thin   = ['borderStyle' => Border::BORDER_THIN,   'color' => ['rgb' => 'C7D2FE']];
            $medium = ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '818CF8']];
            $center = ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true];
            $left   = ['horizontal' => Alignment::HORIZONTAL_LEFT,   'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true];

            // Ligne 1 : Titre
            $titre = 'BARBILLARD — ' . strtoupper($examen?->titre ?? '') . ' — ' . strtoupper($specialite?->name ?? '') . ' — ' . now()->format('d/m/Y');
            $ws->setCellValue('A1', $titre);
            $ws->getStyle('A1')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('1E1B4B');
            $ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEF2FF');
            $ws->getRowDimension(1)->setRowHeight(18);

            // Ligne 2 : Stats
            $statsVals = [
                'Effectif : '   . ($this->stats['total']    ?? 0),
                'Admis : '      . ($this->stats['admis']    ?? 0),
                'Ajournés : '   . ($this->stats['ajournes'] ?? 0),
                'Taux : '       . ($this->stats['taux']     ?? 0) . '%',
                'Moy. promo : ' . ($this->stats['avg']      ?? '—') . '/20',
                'Meilleure : '  . ($this->stats['max']      ?? '—') . '/20',
            ];
            foreach ($statsVals as $i => $val) {
                $col = Coordinate::stringFromColumnIndex($i + 1);
                $ws->setCellValue($col . '2', $val);
                $ws->getStyle($col . '2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '312E81']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E7FF']],
                ]);
            }
            $ws->getRowDimension(2)->setRowHeight(14);

            // En-têtes (lignes 4-7)
            $ROW = 4; $col = 2;

            $ws->mergeCells('A' . $ROW . ':A' . ($ROW + 3));
            $ws->setCellValue('A' . $ROW, 'NOM & PRÉNOM / MATRICULE');
            $ws->getStyle('A' . $ROW . ':A' . ($ROW + 3))->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E1B4B']],
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'alignment' => $center,
                'borders'   => ['allBorders' => $thin],
            ]);

            foreach ($this->ues as $ue) {
                $nbCols   = $ue->cours->count() * 4 + 1;
                $colStart = Coordinate::stringFromColumnIndex($col);
                $colEnd   = Coordinate::stringFromColumnIndex($col + $nbCols - 1);

                $ws->mergeCells($colStart . $ROW . ':' . $colEnd . $ROW);
                $ws->setCellValue($colStart . $ROW, $ue->code . ($ue->name ? ' — ' . $ue->name : '') . ' (' . ($ue->credits ?? '?') . ' cr.)');
                $ws->getStyle($colStart . $ROW . ':' . $colEnd . $ROW)->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E1B4B']],
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                    'alignment' => $center,
                    'borders'   => ['allBorders' => $thin],
                ]);

                $cCol = $col;
                foreach ($ue->cours as $cours) {
                    $cS = Coordinate::stringFromColumnIndex($cCol);
                    $cE = Coordinate::stringFromColumnIndex($cCol + 3);

                    $ws->mergeCells($cS . ($ROW + 1) . ':' . $cE . ($ROW + 1));
                    $ws->setCellValue($cS . ($ROW + 1), $cours->name);
                    $ws->getStyle($cS . ($ROW + 1))->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '312E81']],
                        'font'      => ['color' => ['rgb' => 'FFFFFF'], 'size' => 8],
                        'alignment' => $center,
                        'borders'   => ['allBorders' => $thin],
                    ]);

                    $ws->mergeCells($cS . ($ROW + 2) . ':' . $cE . ($ROW + 2));
                    $ws->setCellValue($cS . ($ROW + 2), ($cours->credit ?? $cours->credits ?? '?') . ' cr.');
                    $ws->getStyle($cS . ($ROW + 2))->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3730A3']],
                        'font'      => ['bold' => true, 'color' => ['rgb' => 'FDE68A'], 'size' => 8],
                        'alignment' => $center,
                        'borders'   => ['allBorders' => $thin],
                    ]);

                    foreach ([['Crédit', 'FDE68A'], ['CC', 'C7D2FE'], ['Exam', 'C7D2FE'], ['Moy', 'FDE68A']] as $idx => [$lbl, $fg]) {
                        $sc = Coordinate::stringFromColumnIndex($cCol + $idx);
                        $ws->setCellValue($sc . ($ROW + 3), $lbl);
                        $ws->getStyle($sc . ($ROW + 3))->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4338CA']],
                            'font'      => ['bold' => true, 'color' => ['rgb' => $fg], 'size' => 8],
                            'alignment' => $center,
                            'borders'   => ['allBorders' => $thin],
                        ]);
                    }
                    $cCol += 4;
                }

                $mUEcol = Coordinate::stringFromColumnIndex($cCol);
                $ws->mergeCells($mUEcol . ($ROW + 1) . ':' . $mUEcol . ($ROW + 3));
                $ws->setCellValue($mUEcol . ($ROW + 1), 'Moy UE');
                $ws->getStyle($mUEcol . ($ROW + 1) . ':' . $mUEcol . ($ROW + 3))->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3730A3']],
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FDE68A'], 'size' => 9],
                    'alignment' => $center,
                    'borders'   => ['allBorders' => $thin],
                ]);

                $col += $nbCols;
            }

            foreach (['Moy. Générale', 'Rang'] as $lbl) {
                $ltr = Coordinate::stringFromColumnIndex($col);
                $ws->mergeCells($ltr . $ROW . ':' . $ltr . ($ROW + 3));
                $ws->setCellValue($ltr . $ROW, $lbl);
                $ws->getStyle($ltr . $ROW . ':' . $ltr . ($ROW + 3))->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '312E81']],
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                    'alignment' => $center,
                    'borders'   => ['allBorders' => $thin],
                ]);
                $col++;
            }

            // Données apprenants
            $noteBg = fn($n) => match(true) {
                $n === null => null,
                $n >= 16    => 'DCFCE7',
                $n >= 12    => 'DBEAFE',
                $n >= 10    => 'FEF9C3',
                default     => 'FEE2E2',
            };
            $noteFg = fn($n) => match(true) {
                $n === null => '9CA3AF',
                $n >= 16    => '166534',
                $n >= 12    => '1E40AF',
                $n >= 10    => '92400E',
                default     => '991B1B',
            };

            $dataRow = $ROW + 4;

            foreach ($this->etudiants as $i => $etudiant) {
                $rowBg = $i % 2 === 0 ? 'FFFFFF' : 'F5F3FF';
                $ws->getRowDimension($dataRow)->setRowHeight(26);

                $ws->setCellValue('A' . $dataRow, strtoupper($etudiant->name) . ' ' . $etudiant->lastname . "\n" . ($etudiant->matricule ?? ''));
                $ws->getStyle('A' . $dataRow)->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                    'font'      => ['bold' => true, 'size' => 9],
                    'alignment' => $left,
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E7FF']]],
                ]);

                $dataCol = 2;

                foreach ($this->ues as $ue) {
                    foreach ($ue->cours as $cours) {
                        $n   = $this->notes[$etudiant->id][$cours->id] ?? ['cc' => null, 'exam' => null, 'moy' => null];
                        $moy = $n['moy'];

                        foreach ([
                            [$cours->credit ?? $cours->credits ?? '?', 'EEF2FF', '4338CA', true],
                            [$n['cc']   !== null ? number_format($n['cc'],   2) : '—', $rowBg, '374151', false],
                            [$n['exam'] !== null ? number_format($n['exam'], 2) : '—', $rowBg, '374151', false],
                            [$moy       !== null ? number_format($moy,       2) : '—', $noteBg($moy) ?? $rowBg, $noteFg($moy), true],
                        ] as $idx => [$val, $bg, $fg, $bold]) {
                            $c = Coordinate::stringFromColumnIndex($dataCol + $idx);
                            $ws->setCellValue($c . $dataRow, $val);
                            $ws->getStyle($c . $dataRow)->applyFromArray([
                                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                                'font'      => ['bold' => $bold, 'color' => ['rgb' => $fg], 'size' => 9],
                                'alignment' => $center,
                                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E7FF']]],
                            ]);
                        }
                        $dataCol += 4;
                    }

                    $mUE = $this->moyUE[$etudiant->id][$ue->id] ?? null;
                    $c   = Coordinate::stringFromColumnIndex($dataCol);
                    $ws->setCellValue($c . $dataRow, $mUE !== null ? number_format($mUE, 2) : '—');
                    $ws->getStyle($c . $dataRow)->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $noteBg($mUE) ?? $rowBg]],
                        'font'      => ['bold' => true, 'color' => ['rgb' => $noteFg($mUE)], 'size' => 10],
                        'alignment' => $center,
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'C7D2FE']]],
                    ]);
                    $dataCol++;
                }

                $mg = $this->moyGen[$etudiant->id] ?? null;
                $c  = Coordinate::stringFromColumnIndex($dataCol);
                $ws->setCellValue($c . $dataRow, $mg !== null ? number_format($mg, 2) : '—');
                $ws->getStyle($c . $dataRow)->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $noteBg($mg) ?? $rowBg]],
                    'font'      => ['bold' => true, 'color' => ['rgb' => $noteFg($mg)], 'size' => 11],
                    'alignment' => $center,
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '818CF8']]],
                ]);

                $c = Coordinate::stringFromColumnIndex($dataCol + 1);
                $ws->setCellValue($c . $dataRow, $this->rangs[$etudiant->id] ?? '—');
                $ws->getStyle($c . $dataRow)->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '374151']],
                    'alignment' => $center,
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E7FF']]],
                ]);

                $dataRow++;
            }

            $ws->getColumnDimension('A')->setWidth(30);
            for ($c = 2; $c <= $col + 1; $c++) {
                $ws->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(10);
            }
            $ws->freezePane('B' . ($ROW + 4));

            (new Xlsx($spreadsheet))->save('php://output');

        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ── Export PDF ────────────────────────────────────────────────────────────

    public function exportPdf()
    {
        if (!$this->tableauGenere || empty($this->etudiants)) return;

        $examen     = Examen::find($this->examen_id);
        $specialite = Specialite::find($this->specialite_id);
        $filename   = 'barbillard_' . str($specialite?->name ?? 'export')->slug() . '_' . now()->format('Ymd') . '.pdf';

        $ues       = $this->ues;
        $etudiants = $this->etudiants;
        $notes     = $this->notes;
        $moyUE     = $this->moyUE;
        $moyGen    = $this->moyGen;
        $rangs     = $this->rangs;
        $stats     = $this->stats;

        $noteBg = fn($n) => match(true) {
            $n === null => '#FFFFFF',
            $n >= 16    => '#DCFCE7',
            $n >= 12    => '#DBEAFE',
            $n >= 10    => '#FEF9C3',
            default     => '#FEE2E2',
        };
        $noteFg = fn($n) => match(true) {
            $n === null => '#9CA3AF',
            $n >= 16    => '#166534',
            $n >= 12    => '#1E40AF',
            $n >= 10    => '#92400E',
            default     => '#991B1B',
        };

        // Calcul dynamique de la largeur de page en mm
        $nbCoursTotal = $ues->sum(fn($ue) => $ue->cours->count());
        $nbUes        = $ues->count();
        $largeurMm    = max(297, min(900,
            44 + ($nbCoursTotal * 36) + ($nbUes * 13) + 28 + 14
        ));

        $mode      = $this->mode;
        $modeApres = ($this->mode === 'apres');

        $html = view('exports.barbillard-pdf', compact(
            'ues', 'etudiants', 'notes', 'moyUE', 'moyGen', 'rangs', 'stats',
            'examen', 'specialite', 'noteBg', 'noteFg', 'largeurMm',
            'mode', 'modeApres'
        ))->render();

        return response()->streamDownload(function () use ($html) {
            echo \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setPaper('a3', 'landscape')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => false,
                    'defaultFont'          => 'sans-serif',
                    'dpi'                  => 150,
                ])
                ->output();
        }, $filename, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function clearTableau()
    {
        $this->ues = $this->etudiants = $this->notes = $this->moyUE = $this->moyGen = $this->rangs = $this->stats = [];
    }

    public function couleurNote($note): string
    {
        if ($note === null) return 'text-gray-400';
        if ($note >= 16)   return 'text-green-700 font-bold';
        if ($note >= 12)   return 'text-blue-700 font-semibold';
        if ($note >= 10)   return 'text-yellow-700 font-semibold';
        return 'text-red-700 font-bold';
    }

    public function bgNote($note): string
    {
        if ($note === null) return '';
        if ($note >= 16)   return 'bg-green-50';
        if ($note >= 12)   return 'bg-blue-50';
        if ($note >= 10)   return 'bg-yellow-50';
        return 'bg-red-50';
    }
};
?>

<x-layouts.app header="true">
    @volt
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- ─── Header ─── --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Barbillard — Relevé de Notes</h1>
                <p class="text-gray-500">Tableau récapitulatif des notes CC &amp; Examen par UE et par cours</p>
            </div>
            @if ($tableauGenere)
                <div class="mt-3 inline-flex items-center gap-2">
                    @if ($mode === 'avant')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-semibold rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 inline-block"></span>
                            Avant rattrapage
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-50 border border-orange-200 text-orange-700 text-xs font-semibold rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500 inline-block"></span>
                            Après rattrapage
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- ─── Filtres ─── --}}
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            {{-- Toggle Avant / Après Rattrapage --}}
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Mode :</span>
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-xs font-semibold">
                    <button wire:click="$set('mode', 'avant')"
                        class="px-4 py-1.5 transition
                               {{ $mode === 'avant'
                                   ? 'bg-indigo-600 text-white'
                                   : 'bg-white text-gray-500 hover:bg-gray-50' }}">
                        Avant rattrapage
                    </button>
                    <button wire:click="$set('mode', 'apres')"
                        class="px-4 py-1.5 transition border-l border-gray-200
                               {{ $mode === 'apres'
                                   ? 'bg-orange-500 text-white'
                                   : 'bg-white text-gray-500 hover:bg-gray-50' }}">
                        Après rattrapage
                    </button>
                </div>
                @if ($mode === 'apres')
                    <span class="text-[10px] text-orange-500 font-medium">
                        ↳ La note de rattrapage remplace uniquement l'Exam (CC inchangé)
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

                {{-- 1. Cycle --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Cycle</label>
                    <select wire:model.live="cycle_id"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">— Cycle —</option>
                        @foreach ($cycles as $cycle)
                            <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Examen (dépend du Cycle) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Examen
                        @if (!$cycle_id)
                            <span class="text-gray-400 text-[10px]">(cycle d'abord)</span>
                        @endif
                    </label>
                    <select wire:model.live="examen_id"
                        wire:key="examen-{{ $cycle_id }}"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$cycle_id)>
                        <option value="">— Examen —</option>
                        @foreach ($examens as $examen)
                            <option value="{{ $examen->id }}">{{ $examen->titre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Département (dépend du Cycle) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Département
                        @if (!$cycle_id)
                            <span class="text-gray-400 text-[10px]">(cycle d'abord)</span>
                        @endif
                    </label>
                    <select wire:model.live="departement_id"
                        wire:key="departement-{{ $cycle_id }}"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$cycle_id)>
                        <option value="">— Département —</option>
                        @foreach ($departements as $dep)
                            <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Filière (dépend du Département) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Filière
                        @if (!$departement_id)
                            <span class="text-gray-400 text-[10px]">(département d'abord)</span>
                        @endif
                    </label>
                    <select wire:model.live="filiere_id"
                        wire:key="filiere-{{ $departement_id }}"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$departement_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$departement_id)>
                        <option value="">— Filière —</option>
                        @foreach ($filieres as $filiere)
                            <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 5. Spécialité (dépend de la Filière) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Spécialité
                        @if (!$filiere_id)
                            <span class="text-gray-400 text-[10px]">(filière d'abord)</span>
                        @endif
                    </label>
                    <select wire:model.live="specialite_id"
                        wire:key="specialite-{{ $filiere_id }}"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$filiere_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$filiere_id)>
                        <option value="">— Spécialité —</option>
                        @foreach ($specialites as $specialite)
                            <option value="{{ $specialite->id }}">{{ $specialite->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 6. Boutons --}}
                <div class="flex items-end gap-2 flex-wrap">

                    {{-- Générer --}}
                    <button wire:click="genererTableau"
                        wire:loading.attr="disabled"
                        wire:target="genererTableau"
                        @disabled(!$specialite_id || !$examen_id)
                        class="inline-flex items-center gap-2 px-5 py-1.5 rounded-lg font-semibold text-sm shadow
                               bg-indigo-600 hover:bg-indigo-700 text-white transition
                               disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="genererTableau" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <svg wire:loading wire:target="genererTableau" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="genererTableau">Générer</span>
                        <span wire:loading wire:target="genererTableau">Génération…</span>
                    </button>

                    @if ($tableauGenere && !empty($etudiants))

                        {{-- Export Excel --}}
                        <button wire:click="exportExcel"
                            wire:loading.attr="disabled"
                            wire:target="exportExcel"
                            class="inline-flex items-center gap-2 px-5 py-1.5 rounded-lg font-semibold text-sm shadow
                                   bg-green-600 hover:bg-green-700 text-white transition">
                            <svg wire:loading.remove wire:target="exportExcel" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 6.414V19a2 2 0 01-2 2h-2M9 17v-5a1 1 0 011-1h4a1 1 0 011 1v5M9 17h6"/>
                            </svg>
                            <svg wire:loading wire:target="exportExcel" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="exportExcel">Excel (.xlsx)</span>
                            <span wire:loading wire:target="exportExcel">Export…</span>
                        </button>

                        {{-- Export PDF --}}
                        <button wire:click="exportPdf"
                            wire:loading.attr="disabled"
                            wire:target="exportPdf"
                            class="inline-flex items-center gap-2 px-5 py-1.5 rounded-lg font-semibold text-sm shadow
                                   bg-blue-600 hover:bg-blue-700 text-white transition">
                            <svg wire:loading.remove wire:target="exportPdf" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <svg wire:loading wire:target="exportPdf" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="exportPdf">PDF</span>
                            <span wire:loading wire:target="exportPdf">Export…</span>
                        </button>

                    @endif
                </div>

            </div>

            @if (session('erreur_barbillard'))
                <div class="mt-3 px-3 py-2 bg-red-50 border border-red-200 rounded-lg text-xs text-red-600">
                    ⚠️ {{ session('erreur_barbillard') }}
                </div>
            @endif
        </div>

        {{-- ─── Skeleton chargement ─── --}}
        <div wire:loading wire:target="genererTableau" class="mb-8">
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                @for ($i = 0; $i < 6; $i++)
                    <div class="bg-white rounded-xl shadow p-4 border-t-4 border-gray-200 animate-pulse">
                        <div class="h-2 bg-gray-200 rounded w-3/4 mx-auto mb-3"></div>
                        <div class="h-6 bg-gray-200 rounded w-1/2 mx-auto"></div>
                    </div>
                @endfor
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-6 animate-pulse">
                <div class="flex items-center justify-center gap-4 mb-6">
                    <div class="w-8 h-8 rounded-full border-4 border-indigo-200 border-t-indigo-600 animate-spin"></div>
                    <div>
                        <p class="text-indigo-600 font-semibold text-base">Génération du barbillard en cours…</p>
                        <p class="text-gray-400 text-sm">Calcul des moyennes et récupération des notes</p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="h-10 bg-indigo-100 rounded-lg w-full"></div>
                    <div class="h-8 bg-indigo-50 rounded-lg w-full"></div>
                    <div class="h-6 bg-gray-100 rounded-lg w-full"></div>
                    @for ($i = 0; $i < 8; $i++)
                        <div class="flex gap-2">
                            <div class="h-10 bg-gray-100 rounded w-44 shrink-0"></div>
                            <div class="h-10 bg-gray-50 rounded flex-1"></div>
                            <div class="h-10 bg-gray-50 rounded flex-1"></div>
                            <div class="h-10 bg-gray-50 rounded flex-1"></div>
                            <div class="h-10 bg-indigo-50 rounded w-20 shrink-0"></div>
                            <div class="h-10 bg-green-50 rounded w-20 shrink-0"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- ─── Contenu principal ─── --}}
        <div wire:loading.remove wire:target="genererTableau">
            @if ($tableauGenere)

                {{-- Statistiques --}}
                @if (!empty($stats))
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
                        @foreach ([
                            ['label' => 'Effectif',      'val' => $stats['total'],          'color' => 'indigo'],
                            ['label' => 'Admis',         'val' => $stats['admis'],          'color' => 'green'],
                            ['label' => 'Ajournés',      'val' => $stats['ajournes'],       'color' => 'red'],
                            ['label' => 'Taux réussite', 'val' => $stats['taux'] . ' %',   'color' => 'purple'],
                            ['label' => 'Moy. promo',    'val' => $stats['avg'] . ' / 20', 'color' => 'blue'],
                            ['label' => 'Meilleure',     'val' => $stats['max'] . ' / 20', 'color' => 'yellow'],
                        ] as $stat)
                            <div class="bg-white rounded-xl shadow p-4 text-center border-t-4 border-{{ $stat['color'] }}-500">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ $stat['label'] }}</p>
                                <p class="text-2xl font-bold text-{{ $stat['color'] }}-600">{{ $stat['val'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Tableau --}}
                @if (!empty($etudiants) && count($etudiants) > 0 && !empty($ues))
                    <div class="bg-white rounded-2xl shadow-lg p-4 overflow-x-auto">

                        {{-- Légende --}}
                        {{-- Badge mode actif --}}
                        @if ($mode === 'apres')
                            <div class="mb-3 flex items-center gap-2 px-3 py-2 bg-orange-50 border border-orange-200 rounded-lg text-xs font-semibold text-orange-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Mode APRÈS RATTRAPAGE — La note de rattrapage remplace uniquement l'Exam (CC conservé)
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-4 mb-4 text-xs text-gray-600 items-center">
                            <span class="font-medium text-gray-700">Légende :</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> ≥ 16 Très Bien</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> ≥ 12 Bien / AB</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span> ≥ 10 Passable</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> &lt; 10 Insuffisant</span>
                            @if ($mode === 'apres')
                                <span class="flex items-center gap-1 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded-full text-orange-600 font-semibold">
                                    <span class="font-bold">R</span> = Note de rattrapage appliquée
                                </span>
                            @endif
                            <span class="ml-auto italic text-gray-400">
                                @if ($mode === 'avant')
                                    Formule : 0.3 × CC + 0.7 × Exam — Moy. pondérée par crédits
                                @else
                                    Formule : 0.3 × CC + 0.7 × Rattrapage — CC inchangé
                                @endif
                            </span>
                        </div>

                        <table class="w-full border-collapse text-xs text-center">
                            <thead>
                                {{-- Ligne 1 : UE --}}
                                <tr class="bg-indigo-900 text-white">
                                    <th rowspan="4" class="p-3 text-left text-sm font-semibold border border-indigo-700 min-w-[180px] sticky left-0 bg-indigo-900 z-10">
                                        Nom &amp; Prénom
                                    </th>
                                    @foreach ($ues as $ue)
                                        <th colspan="{{ $ue->cours->count() * 4 + 1 }}" class="p-2 border border-indigo-700 font-bold text-sm">
                                            <div class="flex flex-col items-center gap-1">
                                                <span>{{ $ue->code }}@if($ue->name) — <span class="font-normal text-indigo-200">{{ $ue->name }}</span>@endif</span>
                                                <span class="inline-flex items-center gap-1 bg-yellow-400 text-yellow-900 text-[11px] font-extrabold px-3 py-0.5 rounded-full shadow">
                                                    ★ {{ $ue->credits ?? '?' }} crédits
                                                </span>
                                            </div>
                                        </th>
                                    @endforeach
                                    <th rowspan="4" class="p-2 border border-indigo-700 bg-indigo-800 min-w-[80px]">Moy.<br>Générale</th>
                                    <th rowspan="4" class="p-2 border border-indigo-700 bg-indigo-800 min-w-[70px]">Rang</th>
                                </tr>

                                {{-- Ligne 2 : Cours --}}
                                <tr class="bg-indigo-800 text-white">
                                    @foreach ($ues as $ue)
                                        @foreach ($ue->cours as $cours)
                                            <th colspan="4" class="p-2 border border-indigo-700 font-medium">{{ $cours->name }}</th>
                                        @endforeach
                                        <th class="p-2 border border-indigo-600 bg-indigo-700 font-bold text-yellow-300">Moy UE</th>
                                    @endforeach
                                </tr>

                                {{-- Ligne 3 : Crédits cours --}}
                                <tr class="text-white" style="background-color:#3730a3;">
                                    @foreach ($ues as $ue)
                                        @foreach ($ue->cours as $cours)
                                            <th colspan="4" class="p-1 border border-indigo-600">
                                                <span class="inline-flex items-center bg-indigo-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                    {{ $cours->credit ?? $cours->credits ?? '?' }} cr.
                                                </span>
                                            </th>
                                        @endforeach
                                        <th class="p-1 border border-indigo-600"></th>
                                    @endforeach
                                </tr>

                                {{-- Ligne 4 : Sous-colonnes --}}
                                <tr class="bg-indigo-700 text-indigo-200 text-[10px] uppercase tracking-wider">
                                    @foreach ($ues as $ue)
                                        @foreach ($ue->cours as $cours)
                                            <th class="p-2 border border-indigo-600 min-w-[45px] text-yellow-200">Crédit</th>
                                            <th class="p-2 border border-indigo-600 min-w-[50px]">CC</th>
                                            <th class="p-2 border border-indigo-600 min-w-[50px]">Exam</th>
                                            <th class="p-2 border border-indigo-600 min-w-[50px] text-yellow-300">Moy</th>
                                        @endforeach
                                        <th class="p-2 border border-indigo-600 min-w-[60px]"></th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($etudiants as $i => $etudiant)
                                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-indigo-50 transition">

                                        {{-- Nom --}}
                                        <td class="p-3 text-left border border-gray-200 sticky left-0 {{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} z-10">
                                            <p class="font-semibold text-gray-800">
                                                {{ strtoupper($etudiant->name) }},
                                                <span class="font-normal text-gray-600">{{ $etudiant->lastname }}</span>
                                            </p>
                                            <p class="text-[10px] font-mono text-gray-400">{{ $etudiant->matricule }}</p>
                                        </td>

                                        {{-- Notes par UE --}}
                                        @foreach ($ues as $ue)
                                            @foreach ($ue->cours as $cours)
                                                @php
                                                    $n         = $notes[$etudiant->id][$cours->id] ?? ['cc' => null, 'exam' => null, 'moy' => null, 'rattrapage' => null, 'mode_apres' => false];
                                                    $moy       = $n['moy'];
                                                    $modeApres = $n['mode_apres'] ?? false;
                                                @endphp
                                                <td class="p-2 border border-gray-200 font-bold text-indigo-600 bg-indigo-50">
                                                    {{ $cours->credit ?? $cours->credits ?? '?' }}
                                                </td>
                                                {{-- CC — jamais modifié --}}
                                                <td class="p-2 border border-gray-200 font-mono text-gray-700">
                                                    {{ $n['cc'] !== null ? number_format($n['cc'], 2) : '—' }}
                                                </td>
                                                {{-- Exam — remplacé par rattrapage si mode après --}}
                                                <td class="p-2 border border-gray-200 font-mono
                                                    {{ ($n['mode_apres'] ?? false) ? 'text-orange-600 bg-orange-50 font-semibold' : 'text-gray-700' }}">
                                                    @if ($n['exam'] !== null)
                                                        {{ number_format($n['exam'], 2) }}
                                                        @if ($n['mode_apres'] ?? false)
                                                            <span class="text-[9px] text-orange-400 ml-0.5">R</span>
                                                        @endif
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                {{-- Moyenne --}}
                                                <td class="p-2 border border-gray-200 font-mono {{ $this->couleurNote($moy) }} {{ $this->bgNote($moy) }}">
                                                    {{ $moy !== null ? number_format($moy, 2) : '—' }}
                                                    @if ($n['mode_apres'] ?? false)
                                                        <span class="text-[9px] text-orange-400 ml-0.5">R</span>
                                                    @endif
                                                </td>
                                            @endforeach

                                            @php
                                                $mUE      = $moyUE[$etudiant->id][$ue->id] ?? null;
                                                $ueRenvoy = false;
                                                foreach ($ue->cours as $_c) {
                                                    $nd = $notes[$etudiant->id][$_c->id] ?? [];
                                                    if (($nd['renvoye'] ?? false) && !($nd['mode_apres'] ?? false)) {
                                                        $ueRenvoy = true; break;
                                                    }
                                                }
                                            @endphp
                                            <td class="p-2 border-2 border-indigo-200 font-bold font-mono text-sm
                                                {{ $ueRenvoy ? 'bg-red-100 text-red-700' : ($this->couleurNote($mUE) . ' ' . $this->bgNote($mUE)) }}">
                                                @if ($ueRenvoy)
                                                    <span class="text-[10px] font-bold">RENVOYÉ</span>
                                                @else
                                                    {{ $mUE !== null ? number_format($mUE, 2) : '—' }}
                                                @endif
                                            </td>
                                        @endforeach

                                        {{-- Moy Générale --}}
                                        @php $mg = $moyGen[$etudiant->id] ?? null; @endphp
                                        <td class="p-2 border-2 border-indigo-300 font-bold font-mono text-sm {{ $this->couleurNote($mg) }} {{ $this->bgNote($mg) }}">
                                            {{ $mg !== null ? number_format($mg, 2) : '—' }}
                                        </td>

                                        {{-- Rang --}}
                                        <td class="p-2 border border-gray-200 font-semibold text-gray-700">
                                            {{ $rangs[$etudiant->id] ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @else
                    <div class="bg-white rounded-2xl shadow p-12 text-center text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-lg font-medium">Aucune donnée trouvée.</p>
                        <p class="text-sm mt-1">Vérifiez que des notes ont été saisies pour cette spécialité et cet examen.</p>
                    </div>
                @endif

            @else
                <div class="bg-white rounded-2xl shadow p-16 text-center text-gray-400">
                    <svg class="w-20 h-20 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-xl font-medium mb-2 text-gray-500">Le barbillard apparaîtra ici</p>
                    <p class="text-sm">Remplissez les filtres puis cliquez sur <strong class="text-indigo-600">Générer le barbillard</strong></p>
                </div>
            @endif
        </div>

    </div>
    @endvolt
</x-layouts.app>