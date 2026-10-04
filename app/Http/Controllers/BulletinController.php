<?php

namespace App\Http\Controllers;

use App\Models\PaiementSalaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulletinController extends Controller
{
    /**
     * T�l�charger le bulletin PDF d'un paiement
     */
    public function telecharger(int $id)
    {
        $paiement = PaiementSalaire::with([
            'employe',
            'profil.indemnites',
            'profil.retenues',
            'echelon',
            'validePar',
            'payePar',
        ])->findOrFail($id);

        // Seul l'admin ou l'employ� concern� peut t�l�charger
        $user = Auth::user();
        if (! $user->aLeRole('admin') && $user->id !== $paiement->user_id) {
            abort(403);
        }

        $moisNoms = [
            1 => 'Janvier', 2 => 'F�vrier',   3 => 'Mars',
            4 => 'Avril',   5 => 'Mai',        6 => 'Juin',
            7 => 'Juillet', 8 => 'Ao�t',       9 => 'Septembre',
            10 => 'Octobre',11 => 'Novembre',  12 => 'D�cembre',
        ];

        $detail     = $paiement->detail_json ?? [];
        $indemnites = $detail['indemnites'] ?? [];
        $retenues   = $detail['retenues']   ?? [];
        $moisNom    = $moisNoms[$paiement->mois] ?? '';

        // Logo en base64
        $logoPath = public_path('images/logo.png');
        $logoB64  = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('pdf.bulletin', compact(
            'paiement', 'indemnites', 'retenues', 'moisNom', 'logoB64'
        ))->setPaper('a4', 'portrait');

        $filename = 'Bulletin_' .
            str_replace(' ', '_', strtoupper($paiement->employe->name)) .
            '_' . $moisNom . '_' . $paiement->annee . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Télécharger, en un seul PDF, tous les bulletins de l'employé connecté
     * pour une période (année + plage de mois).
     */
    public function telechargerPeriode(Request $request)
    {
        $user = Auth::user();

        // Plage possible sur plusieurs années : (année, mois) début → fin.
        $anneeDebut = (int) $request->query('annee_debut', (int) date('Y'));
        $anneeFin   = (int) $request->query('annee_fin', $anneeDebut);
        $moisDebut  = max(1, min(12, (int) $request->query('mois_debut', 1)));
        $moisFin    = max(1, min(12, (int) $request->query('mois_fin', 12)));

        // Index chronologique (année*12 + mois) pour comparer facilement.
        $start = $anneeDebut * 12 + $moisDebut;
        $end   = $anneeFin * 12 + $moisFin;
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $paiements = PaiementSalaire::with(['employe', 'profil', 'echelon', 'validePar', 'payePar'])
            ->where('user_id', $user->id)
            ->whereRaw('(annee * 12 + mois) between ? and ?', [$start, $end])
            ->orderBy('annee')
            ->orderBy('mois')
            ->get();

        if ($paiements->isEmpty()) {
            return back()->with('error', 'Aucun bulletin disponible pour cette période.');
        }

        $moisNoms = [
            1 => 'Janvier', 2 => 'Février',  3 => 'Mars',
            4 => 'Avril',   5 => 'Mai',       6 => 'Juin',
            7 => 'Juillet', 8 => 'Août',      9 => 'Septembre',
            10 => 'Octobre',11 => 'Novembre', 12 => 'Décembre',
        ];

        $bulletins = $paiements->map(function ($p) use ($moisNoms) {
            $detail = $p->detail_json ?? [];
            return [
                'paiement'   => $p,
                'indemnites' => $detail['indemnites'] ?? [],
                'retenues'   => $detail['retenues']   ?? [],
                'moisNom'    => $moisNoms[$p->mois] ?? '',
            ];
        });

        $pdf = Pdf::loadView('pdf.bulletins-periode', ['bulletins' => $bulletins])
            ->setPaper('a4', 'portrait');

        $suffixe  = $paiements->count() === 1
            ? $paiements->first()->annee . '-' . str_pad($paiements->first()->mois, 2, '0', STR_PAD_LEFT)
            : $anneeDebut . '_' . $anneeFin;
        $filename = 'Bulletins_' . str_replace(' ', '_', strtoupper($user->name)) . '_' . $suffixe . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * T�l�charger plusieurs bulletins en un seul PDF (batch)
     */
    /**
     * Pr�visualiser le bulletin dans le navigateur (stream inline)
     */
    public function previsualiser(int $id)
    {
        $paiement = PaiementSalaire::with([
            'employe', 'profil', 'echelon', 'validePar', 'payePar',
        ])->findOrFail($id);

        $user = Auth::user();
        if (! $user->aLeRole('admin') && $user->id !== $paiement->user_id) {
            abort(403);
        }

        $moisNoms = [
            1=>'Janvier',2=>'F�vrier',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
            7=>'Juillet',8=>'Ao�t',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'D�cembre',
        ];

        $detail     = $paiement->detail_json ?? [];
        $indemnites = $detail['indemnites'] ?? [];
        $retenues   = $detail['retenues']   ?? [];
        $moisNom    = $moisNoms[$paiement->mois] ?? '';
        $logoB64    = null;

        $pdf = Pdf::loadView('pdf.bulletin', compact(
            'paiement', 'indemnites', 'retenues', 'moisNom', 'logoB64'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('bulletin_preview.pdf');
    }

    public function batch(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) abort(400);

        $paiements = PaiementSalaire::with([
            'employe', 'profil', 'echelon', 'validePar', 'payePar',
        ])->whereIn('id', $ids)->orderBy('user_id')->get();

        $moisNoms = [
            1=>'Janvier',2=>'F�vrier',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
            7=>'Juillet',8=>'Ao�t',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'D�cembre',
        ];

        $logoPath = public_path('images/logo.png');
        $logoB64  = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('pdf.bulletins-batch', compact('paiements', 'moisNoms', 'logoB64'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Bulletins_' . now()->format('Y-m') . '.pdf');
    }
}