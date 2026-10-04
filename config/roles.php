<?php

/*
|--------------------------------------------------------------------------
| Catalogue des rôles de l'application
|--------------------------------------------------------------------------
|
| C'est l'application qui définit ses rôles. Le portail les récupère
| (bouton « Synchroniser » ou `php artisan portail:synchroniser`) et
| l'administrateur les attribue au personnel. Un employé peut en cumuler
| plusieurs : ses accès et son menu sont alors l'union de ceux de ses rôles.
|
| L'ordre compte : le premier rôle détenu décide de l'espace d'accueil.
|
| - libelle / description : affichés dans l'administration du portail ;
| - role_local : valeur de la colonne users.role (enum) pour ce rôle ;
| - espace : menu affiché (config/navigation.php) ;
| - acces : chemins ouverts en plus de l'espace personnel.
|
*/

return [

    'admin' => [
        'libelle' => 'Administrateur',
        'description' => "Accès complet à l'administration de l'institut.",
        'role_local' => 'admin',
        'espace' => 'admin',
    ],

    'finance' => [
        'libelle' => 'Finance & RH',
        'description' => 'Paie, bulletins de paie, profils salaires et ressources humaines.',
        'role_local' => 'personnel',
        'espace' => 'finance',
        'acces' => [
            'finance', 'finance/*',
            'admin/rh', 'admin/rh/*',
            'admin/paie', 'admin/paie/*',
            'admin/bulletins-paie', 'admin/bulletins-paie/*',
            'admin/salaires', 'admin/salaires/*',
        ],
    ],

    'coordonnateur' => [
        'libelle' => 'Coordonnateur',
        'description' => 'Pilote une filière : spécialités, cours, étudiants, examens et rapports.',
        'role_local' => 'coordonnateur',
        'espace' => 'personnel',
        'acces' => ['filiere', 'filiere/*'],
    ],

    'filiere' => [
        'libelle' => 'Responsable de filière',
        'description' => 'Espace « Ma filière ».',
        'role_local' => 'filiere',
        'espace' => 'personnel',
        'acces' => ['filiere', 'filiere/*'],
    ],

    'specialite' => [
        'libelle' => 'Responsable de spécialité',
        'description' => 'Espace « Ma spécialité » : étudiants, cours, UE, examens et notes.',
        'role_local' => 'specialite',
        'espace' => 'personnel',
        'acces' => ['specialite', 'specialite/*'],
    ],

    'enseignant' => [
        'libelle' => 'Enseignant',
        'description' => 'Ses cours, ses étudiants et la saisie des notes.',
        'role_local' => 'enseignant',
        'espace' => 'personnel',
        'acces' => ['specialite', 'specialite/*'],
    ],

    'bibliothecaire' => [
        'libelle' => 'Bibliothécaire',
        'description' => 'Gestion de la bibliothèque : ouvrages et emprunts.',
        'role_local' => 'bibliothecaire',
        'espace' => 'personnel',
        'acces' => ['admin/bibliotheque', 'admin/bibliotheque/*'],
    ],

    'concierge' => [
        'libelle' => 'Concierge',
        'description' => 'Espace personnel : profil et bulletins de paie.',
        'role_local' => 'concierge',
        'espace' => 'personnel',
    ],

    'personnel' => [
        'libelle' => 'Personnel',
        'description' => 'Espace personnel : profil et bulletins de paie.',
        'role_local' => 'personnel',
        'espace' => 'personnel',
    ],

];
