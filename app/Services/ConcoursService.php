<?php

namespace App\Services;

use App\Models\Candidat;
use App\Models\Inscription;
use App\Models\SessionConcours;
use App\Models\Versement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Regles metier du concours d'entree et des inscriptions.
 *
 * Tout vient des documents officiels de public/inscription : niveaux exiges,
 * pieces du dossier, et echeancier de la fiche d'inscription.
 */
class ConcoursService
{
    /** Niveaux acceptes a l'entree, du plus courant au plus eleve. */
    public const NIVEAUX = ['CEP', '3ème', 'BEPC', 'CAP', 'Probatoire', 'BAC', 'BTS', 'Licence', 'Autre'];

    /** Pieces exigees, dans l'ordre de la fiche de renseignement. */
    public const PIECES = [
        'piece_fiche' => 'Fiche d’inscription',
        'piece_acte' => 'Photocopie de l’acte de naissance ou de la CNI',
        'piece_diplome' => 'Photocopie du diplôme le plus élevé',
        'piece_photos' => 'Deux photos 4×4 et un certificat médical',
        'piece_enveloppe' => 'Une enveloppe A4 et une chemise cartonnée',
    ];

    /**
     * Echeancier par defaut, repris de la fiche d'inscription : les trois
     * lignes fixes, puis les huit versements de scolarite.
     *
     * @return array<int, array{rang: int, libelle: string, montant: float}>
     */
    public function echeancierParDefaut(float $scolarite = 0): array
    {
        $lignes = [
            ['rang' => 1, 'libelle' => 'Inscription (non remboursable)', 'montant' => 0.0],
            ['rang' => 2, 'libelle' => 'Associations étudiantes', 'montant' => 5000.0],
            ['rang' => 3, 'libelle' => 'Visite médicale (infirmerie)', 'montant' => 4000.0],
        ];

        // La scolarite se repartit sur huit versements, arrondis a la centaine.
        $part = $scolarite > 0 ? round($scolarite / 8, -2) : 0.0;

        for ($i = 1; $i <= 8; $i++) {
            $lignes[] = [
                'rang' => 3 + $i,
                'libelle' => $i === 1 ? '1er versement' : $i.'ème versement',
                'montant' => $part,
            ];
        }

        return $lignes;
    }

    public function anneeCourante(): string
    {
        // L'annee de formation bascule en aout, comme la rentree.
        $debut = now()->month >= 8 ? now()->year : now()->year - 1;

        return $debut.'-'.($debut + 1);
    }

    /**
     * Empreinte d'un candidat : deux fiches au meme nom et a la meme date de
     * naissance designent la meme personne.
     */
    public function cleDoublon(string $nom, ?string $prenoms, ?string $naissance): string
    {
        $base = Str::of($nom.' '.$prenoms)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->squish();

        return Str::limit($base.'|'.($naissance ?: '?'), 180, '');
    }

    /**
     * Matricule d'apprenant : IFPM-000123, numerotation continue.
     */
    public function genererMatricule(): string
    {
        $dernier = Inscription::where('matricule', 'like', 'IFPM-%')
            ->orderByRaw('CAST(SUBSTRING(matricule, 6) AS UNSIGNED) DESC')
            ->value('matricule');

        $numero = $dernier ? ((int) Str::after($dernier, 'IFPM-')) + 1 : 1;

        return 'IFPM-'.str_pad((string) $numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Enregistre une candidature. Refuse un doublon dans la meme session.
     *
     * @param  array<string, mixed>  $donnees
     */
    public function enregistrerCandidat(array $donnees, ?int $saisiPar = null): Candidat
    {
        $session = SessionConcours::findOrFail($donnees['session_id']);

        if (! $session->estOuverte()) {
            throw new RuntimeException('Cette session de concours est fermée : les candidatures n’y sont plus acceptées.');
        }

        $cle = $this->cleDoublon(
            $donnees['nom'] ?? '',
            $donnees['prenoms'] ?? null,
            $donnees['date_naissance'] ?? null,
        );

        $existant = Candidat::where('session_id', $session->id)->where('cle_doublon', $cle)->first();

        if ($existant) {
            throw new RuntimeException(
                'Ce candidat est déjà inscrit à cette session (fiche n° '.$existant->id.', enregistrée le '
                .$existant->date_inscription->format('d/m/Y').').'
            );
        }

        return Candidat::create($donnees + [
            'cle_doublon' => $cle,
            'date_inscription' => $donnees['date_inscription'] ?? now()->toDateString(),
            'saisi_par' => $saisiPar,
        ]);
    }

    /**
     * Inscrit un apprenant et cree son echeancier.
     *
     * @param  array<string, mixed>  $donnees
     * @param  array<int, array<string, mixed>>|null  $echeancier
     */
    public function inscrire(array $donnees, ?array $echeancier = null, ?int $saisiPar = null): Inscription
    {
        return DB::transaction(function () use ($donnees, $echeancier, $saisiPar) {
            $donnees['matricule'] = $donnees['matricule'] ?: $this->genererMatricule();
            $donnees['annee'] = $donnees['annee'] ?: $this->anneeCourante();

            if (Inscription::where('matricule', $donnees['matricule'])->exists()) {
                throw new RuntimeException('Le matricule '.$donnees['matricule'].' est déjà attribué à un apprenant.');
            }

            // Un candidat admis ne s'inscrit qu'une fois.
            if (! empty($donnees['candidat_id'])
                && Inscription::where('candidat_id', $donnees['candidat_id'])->exists()) {
                throw new RuntimeException('Ce candidat a déjà une fiche d’inscription.');
            }

            $inscription = Inscription::create($donnees + ['saisi_par' => $saisiPar]);

            foreach ($echeancier ?? $this->echeancierParDefaut((float) ($donnees['montant_scolarite'] ?? 0)) as $ligne) {
                $inscription->versements()->create([
                    'rang' => $ligne['rang'],
                    'libelle' => $ligne['libelle'],
                    // Ce qui est dû ; « montant » ne se remplit qu'au paiement.
                    'montant_prevu' => $ligne['montant'] ?? 0,
                    'montant' => $ligne['montant_verse'] ?? 0,
                    'date_versement' => $ligne['date_versement'] ?? null,
                    'vise' => $ligne['vise'] ?? false,
                    'penalite' => $ligne['penalite'] ?? null,
                ]);
            }

            // Le candidat admis passe au statut correspondant.
            if ($inscription->candidat) {
                $inscription->candidat->update(['statut' => 'admis']);
            }

            return $inscription;
        });
    }

    /**
     * Enregistre un versement sur une ligne de l'echeancier.
     */
    public function enregistrerVersement(Versement $versement, float $montant, ?string $date = null, bool $vise = false): Versement
    {
        $versement->update([
            'montant' => $montant,
            'date_versement' => $date ?: now()->toDateString(),
            'vise' => $vise,
        ]);

        $inscription = $versement->inscription;

        if ($inscription->resteAPayer() <= 0 && $inscription->montant_scolarite > 0) {
            $inscription->update(['statut' => 'solde']);
        }

        return $versement;
    }
}
