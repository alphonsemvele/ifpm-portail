<?php

/*
 * Menu lateral de chaque espace. Un lien n'est affiche que si l'utilisateur
 * a le droit d'ouvrir la page (User::allowedPrefixes) ; un groupe vide
 * disparait. Icones disponibles : voir components/layouts/app.blade.php.
 */
/*
 * Ressources humaines & paie : gérées désormais dans le portail La
 * Majestueuse, module « Personnel & paie ». Un agent qui travaille dans
 * plusieurs instituts y a un seul dossier, et chaque employeur garde sa
 * propre paie. Les menus locaux sont conservés en commentaire ci-dessous :
 * les pages et les données existent toujours, seul l'accès par le menu est
 * retiré, le temps que la reprise soit terminée.
 */
$portailPersonnel = rtrim((string) env('PORTAIL_URL', 'http://127.0.0.1:8000'), '/').'/personnel';

return [

    'admin' => [
        ['titre' => 'Tableau de bord', 'lien' => '/admin', 'icone' => 'accueil'],

        ['groupe' => 'Concours & inscriptions', 'module' => 'inscriptions', 'liens' => [
            ['titre' => 'Sessions de concours', 'lien' => '/admin/concours', 'icone' => 'evaluation'],
            ['titre' => 'Candidats', 'lien' => '/admin/concours/candidats', 'icone' => 'eleves'],
            ['titre' => 'Nouvelle candidature', 'lien' => '/admin/concours/candidats/nouvelle', 'icone' => 'ajouter'],
            ['titre' => 'Inscriptions', 'lien' => '/admin/inscriptions', 'icone' => 'document'],
            ['titre' => 'Nouvelle inscription', 'lien' => '/admin/inscriptions/nouvelle', 'icone' => 'reinscrire'],
        ]],

        ['groupe' => 'Gestion académique', 'liens' => [
            ['titre' => 'Cycles', 'lien' => '/admin/cycle', 'icone' => 'cycle'],
            ['titre' => 'Départements', 'lien' => '/admin/departement', 'icone' => 'sections'],
            ['titre' => 'Filières', 'lien' => '/admin/filiere', 'icone' => 'niveaux'],
            ['titre' => 'Spécialités', 'lien' => '/admin/specialite', 'icone' => 'classe'],
            ['titre' => 'Unités d’enseignement', 'lien' => '/admin/ue', 'icone' => 'domaines'],
            ['titre' => 'Matières', 'lien' => '/admin/cours', 'icone' => 'matieres'],
            ['titre' => 'CC & examens', 'lien' => '/admin/notes', 'icone' => 'notes'],
            ['titre' => 'Rattrapage', 'lien' => '/admin/rattrapage', 'icone' => 'reinscrire'],
            ['titre' => 'Examens', 'lien' => '/admin/examen', 'icone' => 'evaluation'],
            ['titre' => 'Barbillard', 'lien' => '/admin/barbillard', 'icone' => 'barbillard'],
            ['titre' => 'Relevés de notes', 'lien' => '/admin/releve', 'icone' => 'importer'],
        ]],

        // --- Repris par le portail : module « Personnel & paie » ---------
        ['groupe' => 'Ressources humaines & paie', 'liens' => [
            ['titre' => 'Ouvrir dans le portail', 'lien' => $portailPersonnel, 'icone' => 'personnel', 'externe' => true],
        ]],

        // ['groupe' => 'Ressources humaines & paie', 'liens' => [
        //     ['titre' => 'Payer les salaires', 'lien' => '/admin/paie', 'icone' => 'argent'],
        //     ['titre' => 'Bulletins de paie', 'lien' => '/admin/bulletins-paie', 'icone' => 'document'],
        //     ['titre' => 'Profils salaires', 'lien' => '/admin/rh/profils', 'icone' => 'profil'],
        //     ['titre' => 'Catégories & échelons', 'lien' => '/admin/rh/categories', 'icone' => 'etiquette'],
        //     ['titre' => 'Indemnités', 'lien' => '/admin/rh/indemnites', 'icone' => 'plus'],
        //     ['titre' => 'Retenues', 'lien' => '/admin/rh/retenues', 'icone' => 'moins'],
        // ]],

        ['groupe' => 'Utilisateurs', 'liens' => [
            ['titre' => 'Apprenants', 'lien' => '/admin/etudiant', 'icone' => 'eleves'],
            ['titre' => 'Personnel', 'lien' => '/admin/personnel', 'icone' => 'personnel'],
            ['titre' => 'Préinscriptions', 'lien' => '/admin/preinscription', 'icone' => 'ajouter'],
            ['titre' => 'Présences', 'lien' => '/admin/presence', 'icone' => 'valider'],
        ]],

        ['groupe' => 'Vie de l’institut', 'liens' => [
            ['titre' => 'Restaurant', 'lien' => '/admin/menu', 'icone' => 'cantine'],
            ['titre' => 'Bibliothèque', 'lien' => '/admin/bibliotheque', 'icone' => 'livre'],
            ['titre' => 'Support', 'lien' => '/admin/support', 'icone' => 'support'],
        ]],

        ['groupe' => 'Communication', 'liens' => [
            ['titre' => 'Annonces', 'lien' => '/admin/annonce', 'icone' => 'annonce'],
            ['titre' => 'Notifications', 'lien' => '/admin/notification', 'icone' => 'cloche'],
            ['titre' => 'Articles', 'lien' => '/admin/article', 'icone' => 'document'],
        ]],

        ['groupe' => 'Infrastructure & administration', 'liens' => [
            ['titre' => 'Salles', 'lien' => '/admin/salle', 'icone' => 'salle'],
            ['titre' => 'Sections', 'lien' => '/admin/section', 'icone' => 'sections'],
            ['titre' => 'Paramètres', 'lien' => '/admin/configuration', 'icone' => 'etiquette'],
            ['titre' => 'Finances', 'lien' => '/admin/finance', 'icone' => 'argent'],
        ]],
    ],

    'finance' => [
        ['titre' => 'Espace finance', 'lien' => '/finance', 'icone' => 'accueil'],
        // --- Repris par le portail : module « Personnel & paie » ---------
        ['groupe' => 'Paie', 'liens' => [
            ['titre' => 'Ouvrir dans le portail', 'lien' => $portailPersonnel, 'icone' => 'argent', 'externe' => true],
        ]],

        // ['groupe' => 'Paie', 'liens' => [
        //     ['titre' => 'Payer les salaires', 'lien' => '/admin/paie', 'icone' => 'argent'],
        //     ['titre' => 'Bulletins de paie', 'lien' => '/admin/bulletins-paie', 'icone' => 'document'],
        // ]],
        // ['groupe' => 'Ressources humaines', 'liens' => [
        //     ['titre' => 'Vue d’ensemble RH', 'lien' => '/admin/rh', 'icone' => 'personnel'],
        //     ['titre' => 'Profils salaires', 'lien' => '/admin/rh/profils', 'icone' => 'profil'],
        //     ['titre' => 'Catégories & échelons', 'lien' => '/admin/rh/categories', 'icone' => 'etiquette'],
        //     ['titre' => 'Indemnités', 'lien' => '/admin/rh/indemnites', 'icone' => 'plus'],
        //     ['titre' => 'Retenues', 'lien' => '/admin/rh/retenues', 'icone' => 'moins'],
        // ]],
    ],

    'personnel' => [
        ['titre' => 'Mon espace', 'lien' => '/personnel', 'icone' => 'accueil'],
        ['groupe' => 'Ma filière', 'liens' => [
            ['titre' => 'Vue d’ensemble', 'lien' => '/filiere', 'icone' => 'niveaux'],
            ['titre' => 'Spécialités', 'lien' => '/filiere/specialite', 'icone' => 'classe'],
            ['titre' => 'Unités d’enseignement', 'lien' => '/filiere/ue', 'icone' => 'domaines'],
            ['titre' => 'Cours', 'lien' => '/filiere/cours', 'icone' => 'matieres'],
            ['titre' => 'Apprenants', 'lien' => '/filiere/etudiant', 'icone' => 'eleves'],
            ['titre' => 'Personnel', 'lien' => '/filiere/personnel', 'icone' => 'personnel'],
            ['titre' => 'Examens', 'lien' => '/filiere/examens', 'icone' => 'evaluation'],
            ['titre' => 'Rapports', 'lien' => '/filiere/rapport', 'icone' => 'document'],
        ]],
        ['groupe' => 'Ma spécialité', 'liens' => [
            ['titre' => 'Vue d’ensemble', 'lien' => '/specialite', 'icone' => 'classe'],
            ['titre' => 'Apprenants', 'lien' => '/specialite/etudiant', 'icone' => 'eleves'],
            ['titre' => 'Cours', 'lien' => '/specialite/cours', 'icone' => 'matieres'],
            ['titre' => 'Unités d’enseignement', 'lien' => '/specialite/ue', 'icone' => 'domaines'],
            ['titre' => 'Examens & notes', 'lien' => '/specialite/examens', 'icone' => 'notes'],
            ['titre' => 'Rapports', 'lien' => '/specialite/rapport', 'icone' => 'document'],
        ]],
        ['groupe' => 'Moi', 'liens' => [
            ['titre' => 'Mes bulletins de paie', 'lien' => '/personnel/bulletins', 'icone' => 'argent'],
            ['titre' => 'Mon profil', 'lien' => '/personnel/profil', 'icone' => 'profil'],
        ]],
    ],

    'eleve' => [
        ['titre' => 'Accueil', 'lien' => '/dashboard', 'icone' => 'accueil'],
        ['groupe' => 'Scolarité', 'liens' => [
            ['titre' => 'Mes notes', 'lien' => '/dashboard/notes', 'icone' => 'notes'],
            ['titre' => 'Barbillard', 'lien' => '/dashboard/barbillard', 'icone' => 'barbillard'],
            ['titre' => 'Mes examens', 'lien' => '/dashboard/examen', 'icone' => 'evaluation'],
            ['titre' => 'Mes cours', 'lien' => '/dashboard/cours', 'icone' => 'matieres'],
            ['titre' => 'Ma filière', 'lien' => '/dashboard/filiere', 'icone' => 'niveaux'],
        ]],
        ['groupe' => 'Vie de l’institut', 'liens' => [
            ['titre' => 'Restaurant', 'lien' => '/dashboard/menu', 'icone' => 'cantine'],
            ['titre' => 'Bibliothèque', 'lien' => '/dashboard/bibliotheque', 'icone' => 'livre'],
            ['titre' => 'Support', 'lien' => '/dashboard/support', 'icone' => 'support'],
            ['titre' => 'Mon compte', 'lien' => '/dashboard/compte', 'icone' => 'profil'],
        ]],
    ],
];
