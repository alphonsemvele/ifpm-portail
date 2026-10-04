<?php

namespace Database\Seeders;

use App\Models\Cycle;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Section;
use App\Models\Specialite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Catalogue de l'Institut de Formation Professionnelle La Majestueuse,
 * tel que publie sur ifpm-ndazoa.com (5 domaines, 20 metiers).
 *
 * Correspondance avec le vocabulaire du logiciel :
 *   Cycle       -> Formation professionnelle qualifiante
 *   Departement -> Domaine de formation
 *   Filiere     -> Metier
 *   Specialite  -> Parcours (metier + duree), la ou l'apprenant s'inscrit
 *
 * La base IFPM avait ete creee depuis une copie d'IUM : les referentiels
 * universitaires herites (BTS, Licence, Master...) sont desactives, pas
 * supprimes. Relancer le seeder ne cree aucun doublon.
 */
class CatalogueIfpmSeeder extends Seeder
{
    private const DOMAINES = [
        'BAT' => ['Bâtiment & Construction', [
            ['Maçonnerie', 12], ['Plomberie & Sanitaire', 12], ['Carrelage Bâtiment', 9],
            ['Électricité Bâtiment', 12], ['Électricité Industrielle', 18], ['Soudure', 9],
            ['Chaudronnerie', 12], ['Staff & Décoration', 9],
        ]],
        'MOD' => ['Mode & Beauté', [
            ['Couture Professionnelle', 12], ['Stylisme & Modélisme', 18],
            ['Coiffure Professionnelle', 9], ['Esthétique & Cosmétique', 12],
        ]],
        'NUM' => ['Informatique & Numérique', [
            ['Maintenance Informatique', 9], ['Maintenance Réseaux', 12],
            ['Infographie', 9], ['Montage Audiovisuel', 9],
        ]],
        'SAN' => ['Santé & Services', [
            ['Auxiliaire de Vie', 9], ['Auxiliaire de Puériculture', 12], ['Aide Chimiste Biologiste', 12],
        ]],
        'LAN' => ['Langues', [
            ['Français', 6], ['Anglais', 6], ['Allemand', 6],
        ]],
        // Metiers attestes par la liste officielle des candidats au concours
        // (public/inscription) mais absents du catalogue en ligne.
        'TER' => ['Tertiaire & Gestion', [
            ['Secrétariat de Direction', 12], ['Secrétariat Médical', 12],
        ]],
        'MEC' => ['Mécanique & Maintenance', [
            ['Mécanique Automobile', 18],
        ]],
        'AGR' => ['Agriculture & Élevage', [
            ['Élevage', 12],
        ]],
        'HOT' => ['Hôtellerie & Restauration', [
            ['Hôtellerie Restauration', 12],
        ]],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->desactiverReferentielHerite();

            Section::updateOrCreate(['code' => 'IFPM'], [
                'name' => 'Institut de Formation Professionnelle La Majestueuse',
                'abbreviation' => 'IFPM',
                'status' => 'Success',
            ]);

            $cycle = Cycle::updateOrCreate(
                ['name' => 'Formation professionnelle qualifiante'],
                ['institution' => 'IFPM', 'status' => 'Success']
            );

            foreach (self::DOMAINES as $code => [$domaine, $metiers]) {
                $departement = Departement::updateOrCreate(['code' => 'IFPM-'.$code], [
                    'nom' => $domaine,
                    'description' => 'Domaine de formation IFPM : '.$domaine.'.',
                    'status' => 'Success',
                    'cycle_id' => $cycle->id,
                ]);

                foreach ($metiers as $rang => [$metier, $mois]) {
                    $filiere = Filiere::updateOrCreate(
                        ['name' => $metier, 'institution' => 'IFPM'],
                        [
                            'code' => sprintf('IFPM-%s-%02d', $code, $rang + 1),
                            'description' => "Métier : $metier. Durée de la formation : $mois mois. "
                                .'Niveau requis : CEP, BEPC, CAP ou équivalent. Formation 100 % pratique avec stage.',
                            'cycle_id' => $cycle->id,
                            'departement_id' => $departement->id,
                            'status' => 'Success',
                        ]
                    );

                    Specialite::updateOrCreate(
                        ['filiere_id' => $filiere->id, 'name' => "$metier — $mois mois"],
                        ['cycle_id' => $cycle->id, 'status' => 'Success', 'price' => 0]
                    );
                }
            }
        });

        $this->command?->info('Catalogue IFPM : 9 domaines, 27 parcours.');
    }

    /** Referentiels universitaires venus de la copie d'IUM : masques, conserves. */
    private function desactiverReferentielHerite(): void
    {
        DB::table('sections')->where('code', '!=', 'IFPM')->update(['status' => 'failed']);
        DB::table('cycles')->where('institution', '!=', 'IFPM')->update(['status' => 'failed']);
        DB::table('filieres')->where('institution', '!=', 'IFPM')->update(['status' => 'failed']);
        DB::table('departements')->where(fn ($q) => $q->whereNull('code')->orWhere('code', 'not like', 'IFPM-%'))->update(['status' => 'failed']);
        DB::table('specialites')->whereNotIn('filiere_id', DB::table('filieres')->where('institution', 'IFPM')->select('id'))->update(['status' => 'failed']);
        DB::table('ues')->whereNotIn('filiere_id', DB::table('filieres')->where('institution', 'IFPM')->select('id'))->update(['status' => 'failed']);
        DB::table('cours')->whereNotIn('filiere_id', DB::table('filieres')->where('institution', 'IFPM')->select('id'))->update(['status' => 'failed']);
    }
}
