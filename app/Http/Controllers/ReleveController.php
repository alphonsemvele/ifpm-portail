<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReleveController extends Controller
{
    /**
     * Télécharge le relevé de notes de l'étudiant connecté (par filière / session).
     */
    public function telecharger()
    {
        $user = Auth::user()->load(['filiere', 'specialite', 'cycle']);

        $notes = Note::where('etudiant_id', $user->id)
            ->with(['cours.ue', 'examen'])
            ->get();

        $sessions = [];
        $sumMoy = 0; $countMoy = 0; $totCredits = 0; $totValides = 0;

        foreach ($notes as $note) {
            $cours = $note->cours;
            if (!$cours) continue;

            $examFinal = ($note->rattrapage !== null && $note->rattrapage > ($note->exam ?? 0))
                ? $note->rattrapage : $note->exam;
            $moy = ($note->cc !== null && $examFinal !== null)
                ? round(0.3 * $note->cc + 0.7 * $examFinal, 2)
                : ($note->valeur !== null ? (float) $note->valeur : null);

            $credit = (int) ($cours->credit ?? 0);
            $valide = $moy !== null && $moy >= 10;

            $sk = $note->examen->id ?? 0;
            $st = $note->examen->titre ?? 'Session non définie';
            if (!isset($sessions[$sk])) {
                $sessions[$sk] = ['titre' => $st, 'ues' => [], 'credits' => 0, 'valides' => 0];
            }

            $uk = $cours->ue->id ?? 0;
            $un = $cours->ue->name ?? 'Autres matières';
            $uc = $cours->ue->code ?? '';
            if (!isset($sessions[$sk]['ues'][$uk])) {
                $sessions[$sk]['ues'][$uk] = ['name' => $un, 'code' => $uc, 'lignes' => []];
            }

            $sessions[$sk]['ues'][$uk]['lignes'][] = [
                'nom' => $cours->name, 'cc' => $note->cc, 'exam' => $note->exam,
                'ratt' => $note->rattrapage, 'moy' => $moy, 'cote' => $this->cote($moy !== null ? $moy * 5 : null),
                'credit' => $credit, 'valide' => $valide,
            ];
            $sessions[$sk]['credits'] += $credit;
            if ($valide) $sessions[$sk]['valides'] += $credit;

            if ($moy !== null) { $sumMoy += $moy; $countMoy++; }
            $totCredits += $credit;
            if ($valide) $totValides += $credit;
        }

        $globalStats = [
            'moy' => $countMoy ? round($sumMoy / $countMoy, 2) : null,
            'credits' => $totCredits, 'valides' => $totValides, 'matieres' => $countMoy,
        ];

        $filiere = $user->filiere?->name ?? $user->specialite?->name;

        $pdf = Pdf::loadView('pdf.releve-etudiant', compact('user', 'sessions', 'globalStats', 'filiere'))
            ->setPaper('a4', 'portrait');

        $filename = 'Releve_' . str_replace(' ', '_', strtoupper($user->name ?? 'etudiant')) . '.pdf';

        return $pdf->download($filename);
    }

    private function cote($n100): string
    {
        if ($n100 === null) return '—';
        $n = (float) $n100;
        return match (true) {
            $n >= 80 => 'A',  $n >= 75 => 'A-', $n >= 70 => 'B+', $n >= 65 => 'B', $n >= 60 => 'B-',
            $n >= 55 => 'C+', $n >= 50 => 'C',  $n >= 45 => 'C-', $n >= 40 => 'D+', $n >= 33 => 'D',
            $n >= 27 => 'E',  default => 'F',
        };
    }
}
