<?php

/*
 * Identite de l'etablissement, lue par le gabarit (menu, titres, exports PDF)
 * et par les fiches imprimees (inscription, renseignement, liste des candidats).
 *
 * Les valeurs officielles viennent des documents de public/inscription.
 */
return [
    'sigle' => env('ETABLISSEMENT_SIGLE', 'IFPM NDAZOA'),
    'nom' => env('ETABLISSEMENT_NOM', 'Institut de Formation Professionnelle La Majestueuse Ndazoa'),
    'slogan' => env('ETABLISSEMENT_SLOGAN', 'Développer les talents, réaliser les rêves.'),
    'adresse' => env('ETABLISSEMENT_ADRESSE', 'BP 67 Mbankomo, Cameroun'),
    'telephone' => env('ETABLISSEMENT_TELEPHONE', '+237 6 95 04 50 57 / +237 6 72 97 53 94'),
    'email' => env('ETABLISSEMENT_EMAIL', 'ndazoaformation@gmail.com'),
    'domaine' => env('ETABLISSEMENT_DOMAINE', 'ifpm-ndazoa.com'),
    'logo' => env('ETABLISSEMENT_LOGO', 'ifpm.png'),

    // Mentions legales reprises en pied des fiches officielles.
    'autorisation' => env('ETABLISSEMENT_AUTORISATION', '000325/MINEFOP/SG/DFOP/SDGSF/CSACD/CEBAC du 10 juin 2025'),
    'niu' => env('ETABLISSEMENT_NIU', 'M07251796195D'),
    'compte_bancaire' => env('ETABLISSEMENT_COMPTE', '10034-00050-00038614401-96 AFGBANK CMSCB'),

    /*
     * Vocabulaire : l'IFPM parle d'apprenants et de filieres, la ou
     * l'universite parle d'etudiants.
     */
    'libelles' => [
        'eleve' => 'Apprenant',
        'eleves' => 'Apprenants',
        'filiere' => 'Filière',
        'filieres' => 'Filières',
        'specialite' => 'Spécialité',
        'specialites' => 'Spécialités',
    ],

    'modules' => [
        // Concours d'entree et inscriptions des apprenants.
        'inscriptions' => (bool) env('ETABLISSEMENT_MODULE_INSCRIPTIONS', true),
    ],
];
