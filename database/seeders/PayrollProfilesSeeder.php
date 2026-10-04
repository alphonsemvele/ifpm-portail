<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CategorieRh;
use App\Models\Echelon;
use App\Models\Indemnite;
use App\Models\ProfilSalaire;
use App\Models\User;

/**
 * Configure les profils de salaire à partir de la grille de paie
 * "LA MAJESTUEUSE SARL" (mai 2026) et affecte chaque membre du personnel.
 *
 * Modèle : salaire_base = échelon.salaire ; indemnités fixes rattachées au profil.
 * Net (= salaire brut de la grille) = base + Σ indemnités.
 *
 * Idempotent : peut être relancé sans créer de doublons.
 */
class PayrollProfilesSeeder extends Seeder
{
    /** Mot de passe par défaut (identique aux comptes existants). */
    private string $defaultHash = '$2y$12$GCFtgzno1IZqdWrYtmWKVueiDxIWTKhgayc1ewNt7YfazZh2SV8z6';

    public function run(): void
    {
        DB::transaction(function () {
            $indemnites = $this->seedIndemnites();
            $echelons   = $this->seedCategoriesEtEchelons();
            $profils    = $this->seedProfils($indemnites, $echelons);

            $this->affecterPersonnelExistant($profils, $echelons);
            $this->creerPersonnelManquant($profils, $echelons);
        });

        $this->command?->info('Profils de salaire configurés et personnel affecté.');
    }

    /* ---------------------------------------------------------------- */

    private function seedIndemnites(): array
    {
        $libelles = [
            'transport'  => 'Indemnité de transport',
            'logement'   => 'Logement',
            'caisse'     => 'Prime de caisse',
            'technicite' => 'Technicité',
            'anciennete' => 'Ancienneté',
        ];

        $map = [];
        foreach ($libelles as $key => $libelle) {
            $map[$key] = Indemnite::firstOrCreate(
                ['libelle' => $libelle],
                ['actif' => 1]
            );
        }
        return $map;
    }

    /**
     * @return array clé "cat<N>-<lettre>" => Echelon
     */
    private function seedCategoriesEtEchelons(): array
    {
        // [catégorie, numéro, lettre, salaire de base]
        $rows = [
            [1,  1, 'A',            60000],
            [1,  2, 'A (temps partiel)', 40000],
            [3,  1, 'A',            81880],
            [5,  1, 'F',            137820],
            [5,  2, 'C',            126945],
            [6,  1, 'A',            142183],
            [7,  1, 'F',            20000],
            [7,  2, 'A',            167198],
            [9,  1, 'A',            253258],
            [10, 1, 'B',            324508],
            [11, 1, 'A',            351658],
        ];

        // Catégories
        $cats = [];
        foreach (array_unique(array_column($rows, 0)) as $n) {
            $cats[$n] = CategorieRh::firstOrCreate(
                ['libelle' => "Catégorie {$n}"],
                ['actif' => 1]
            );
        }

        // Échelons
        $echelons = [];
        foreach ($rows as [$cat, $numero, $lettre, $salaire]) {
            $ech = Echelon::updateOrCreate(
                ['categorie_rh_id' => $cats[$cat]->id, 'numero' => $numero],
                ['libelle' => $lettre, 'salaire' => $salaire, 'actif' => 1]
            );
            $echelons["cat{$cat}-{$lettre}"] = $ech;
        }

        return ['cats' => $cats, 'echelons' => $echelons];
    }

    /**
     * @return array nom du profil => ProfilSalaire
     */
    private function seedProfils(array $indemnites, array $ctx): array
    {
        $cats     = $ctx['cats'];
        $echelons = $ctx['echelons'];

        // nom, catégorie, échelon (clé), indemnités [clé => montant fixe]
        $defs = [
            'Coordonnateur de filière' => [7, 'cat7-F', [
                'transport' => 183198, 'logement' => 30000, 'technicite' => 30000, 'anciennete' => 7687,
            ]],
            'Directeur / Cadre supérieur' => [9, 'cat9-A', [
                'transport' => 10000, 'logement' => 15000, 'caisse' => 20000, 'technicite' => 25000, 'anciennete' => 9299,
            ]],
            'Comptable' => [7, 'cat7-A', [
                'transport' => 10000, 'logement' => 25000, 'technicite' => 12500, 'anciennete' => 6546,
            ]],
            'Assistant(e) de direction (Cat 6)' => [6, 'cat6-A', [
                'transport' => 10000, 'logement' => 20000, 'technicite' => 10000, 'anciennete' => 7787,
            ]],
            'Gestionnaire de stock' => [3, 'cat3-A', [
                'transport' => 10000, 'logement' => 12800, 'caisse' => 10000, 'technicite' => 6000, 'anciennete' => 7052,
            ]],
            'Directeur (Cat 10)' => [10, 'cat10-B', [
                'transport' => 10000, 'logement' => 30000, 'anciennete' => 7000,
            ]],
            'Directeur ISM (Cat 11)' => [11, 'cat11-A', [
                'transport' => 35000, 'logement' => 25000, 'caisse' => 25000, 'technicite' => 35000, 'anciennete' => 12465,
            ]],
            'Agent de scolarité' => [5, 'cat5-F', [
                'transport' => 10000, 'logement' => 12500, 'technicite' => 6825, 'anciennete' => 7000,
            ]],
            'Assistant(e) de direction (Cat 5)' => [5, 'cat5-C', [
                'transport' => 10000, 'logement' => 12800, 'technicite' => 6825, 'anciennete' => 7000,
            ]],
            'Chauffeur' => [1, 'cat1-A', [
                'transport' => 10000, 'logement' => 10000, 'anciennete' => 5000,
            ]],
            'Chauffeur (temps partiel)' => [1, 'cat1-A (temps partiel)', [
                'transport' => 10000, 'logement' => 12800, 'caisse' => 7135, 'technicite' => 6825, 'anciennete' => 7687,
            ]],
        ];

        $profils = [];
        foreach ($defs as $nom => [$cat, $echKey, $inds]) {
            $echelon = $echelons[$echKey];

            $profil = ProfilSalaire::updateOrCreate(
                ['nom' => $nom],
                [
                    'categorie_rh_id' => $cats[$cat]->id,
                    'echelon_id'      => $echelon->id,
                    'actif'           => 1,
                ]
            );

            // (Ré)attache les indemnités fixes du profil
            $sync = [];
            foreach ($inds as $key => $montant) {
                $sync[$indemnites[$key]->id] = ['type_calcul' => 'fixe', 'value' => $montant];
            }
            $profil->indemnites()->sync($sync);

            $profils[$nom] = $profil;
        }

        return $profils;
    }

    private function affecterPersonnelExistant(array $profils, array $ctx): void
    {
        $echelons = $ctx['echelons'];

        // user_id => [nom du profil, clé échelon, poste éventuel à définir si vide]
        $affectations = [
            10 => ['Coordonnateur de filière',        'cat7-F'],
            8  => ['Coordonnateur de filière',        'cat7-F'],
            9  => ['Coordonnateur de filière',        'cat7-F'],
            24 => ['Coordonnateur de filière',        'cat7-F'],
            11 => ['Coordonnateur de filière',        'cat7-F'],
            19 => ['Coordonnateur de filière',        'cat7-F'],
            18 => ['Coordonnateur de filière',        'cat7-F'],
            71 => ['Coordonnateur de filière',        'cat7-F'],
            4  => ['Directeur / Cadre supérieur',     'cat9-A'],
            20 => ['Directeur / Cadre supérieur',     'cat9-A'],
            7  => ['Directeur / Cadre supérieur',     'cat9-A'],
            17 => ['Directeur / Cadre supérieur',     'cat9-A'],
            13 => ['Comptable',                       'cat7-A'],
            23 => ['Assistant(e) de direction (Cat 6)','cat6-A'],
            21 => ['Gestionnaire de stock',           'cat3-A'],
            16 => ['Directeur (Cat 10)',              'cat10-B'],
            3  => ['Directeur ISM (Cat 11)',          'cat11-A'],
            15 => ['Agent de scolarité',              'cat5-F'],
            14 => ['Assistant(e) de direction (Cat 5)','cat5-C'],
        ];

        foreach ($affectations as $userId => [$profilNom, $echKey]) {
            $user = User::find($userId);
            if (!$user) continue;

            $profil  = $profils[$profilNom];
            $echelon = $echelons[$echKey];

            $user->update([
                'profil_salaire_id' => $profil->id,
                'categorie_rh_id'   => $profil->categorie_rh_id,
                'echelon_id'        => $echelon->id,
            ]);
        }
    }

    private function creerPersonnelManquant(array $profils, array $ctx): void
    {
        $echelons = $ctx['echelons'];

        // matricule, name, lastname, email, poste, profil, échelon
        $nouveaux = [
            ['IUM25-CHF01', 'EDOUDA',       'Théodore',        'edouda.theodore@ium-ndazoa.com',  'chauffeur',           'Chauffeur',                 'cat1-A'],
            ['IUM25-CHF02', 'ETOGA OTTOU',  'Séraphin',        'etoga.ottou@ium-ndazoa.com',      'chauffeur',           'Chauffeur',                 'cat1-A'],
            ['IUM25-CHF03', 'TSHOUNGUI',    '',                'tshoungui@ium-ndazoa.com',        'chauffeur',           'Chauffeur',                 'cat1-A'],
            ['IUM25-CRR01', 'ZOA',          'Victor Bertrand', 'zoa.victor@ium-ndazoa.com',       'responsable_courrier','Chauffeur',                 'cat1-A'],
            ['IUM25-CHF04', 'MEZATSAB',     'Akim',            'mezatsab.akim@ium-ndazoa.com',    'chauffeur',           'Chauffeur (temps partiel)', 'cat1-A (temps partiel)'],
        ];

        foreach ($nouveaux as [$matricule, $name, $lastname, $email, $poste, $profilNom, $echKey]) {
            $profil  = $profils[$profilNom];
            $echelon = $echelons[$echKey];

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name'              => $name,
                    'lastname'          => $lastname,
                    'password'          => $this->defaultHash,
                    'role'              => 'personnel',
                    'status'            => 'Success',
                    'matricule'         => $matricule,
                    'poste'             => $poste,
                    'entite'            => 'IUM',
                    'profil_salaire_id' => $profil->id,
                    'categorie_rh_id'   => $profil->categorie_rh_id,
                    'echelon_id'        => $echelon->id,
                ]
            );
        }
    }
}
