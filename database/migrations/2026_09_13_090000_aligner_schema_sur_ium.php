<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Aligne le schema d'une application du groupe (IFPM, GSBM) sur celui d'IUM,
 * dont le code est desormais partage. Ces bases ont ete creees a la main a
 * partir de copies d'epoques differentes : on n'ajoute que ce qui manque, sans
 * jamais supprimer ni renommer. Relancer la migration ne change rien.
 */
return new class extends Migration
{
    /** Tables absentes : definitions reprises de la base IUM. */
    private const TABLES = [
        'categories_rh' => "CREATE TABLE `categories_rh` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT, `libelle` varchar(255) NOT NULL, `description` text NULL,
            `actif` tinyint(1) NOT NULL DEFAULT 1, `created_at` timestamp NULL, `updated_at` timestamp NULL,
            PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'echelons' => "CREATE TABLE `echelons` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT, `categorie_rh_id` bigint unsigned NOT NULL,
            `numero` tinyint unsigned NOT NULL, `libelle` varchar(255) NOT NULL, `salaire` decimal(12,2) NOT NULL DEFAULT 0,
            `anciennete_min` tinyint unsigned NULL, `actif` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL, `updated_at` timestamp NULL, PRIMARY KEY (`id`),
            UNIQUE KEY `uq_categorie_echelon` (`categorie_rh_id`,`numero`),
            CONSTRAINT `fk_echelon_categorie` FOREIGN KEY (`categorie_rh_id`) REFERENCES `categories_rh` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'profil_salaires' => "CREATE TABLE `profil_salaires` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT, `nom` varchar(255) NOT NULL, `description` text NULL,
            `categorie_rh_id` bigint unsigned NULL, `echelon_id` bigint unsigned NULL, `actif` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL, `updated_at` timestamp NULL, PRIMARY KEY (`id`),
            CONSTRAINT `fk_profil_categorie` FOREIGN KEY (`categorie_rh_id`) REFERENCES `categories_rh` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_profil_echelon` FOREIGN KEY (`echelon_id`) REFERENCES `echelons` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'indemnites' => "CREATE TABLE `indemnites` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT, `libelle` varchar(255) NOT NULL, `description` text NULL,
            `actif` tinyint(1) NOT NULL DEFAULT 1, `created_at` timestamp NULL, `updated_at` timestamp NULL,
            PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'retenues' => "CREATE TABLE `retenues` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT, `libelle` varchar(255) NOT NULL, `description` text NULL,
            `actif` tinyint(1) NOT NULL DEFAULT 1, `created_at` timestamp NULL, `updated_at` timestamp NULL,
            PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'profil_salaire_indemnite' => "CREATE TABLE `profil_salaire_indemnite` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT, `profil_salaire_id` bigint unsigned NOT NULL, `indemnite_id` bigint unsigned NOT NULL,
            `type_calcul` enum('fixe','pourcentage') NOT NULL DEFAULT 'fixe', `value` decimal(10,2) NOT NULL DEFAULT 0,
            `created_at` timestamp NULL, `updated_at` timestamp NULL, PRIMARY KEY (`id`),
            UNIQUE KEY `uq_profil_indemnite` (`profil_salaire_id`,`indemnite_id`),
            CONSTRAINT `fk_pi_indemnite` FOREIGN KEY (`indemnite_id`) REFERENCES `indemnites` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_pi_profil` FOREIGN KEY (`profil_salaire_id`) REFERENCES `profil_salaires` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'profil_salaire_retenue' => "CREATE TABLE `profil_salaire_retenue` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT, `profil_salaire_id` bigint unsigned NOT NULL, `retenue_id` bigint unsigned NOT NULL,
            `type_calcul` enum('fixe','pourcentage') NOT NULL DEFAULT 'fixe', `value` decimal(10,2) NOT NULL DEFAULT 0,
            `created_at` timestamp NULL, `updated_at` timestamp NULL, PRIMARY KEY (`id`),
            UNIQUE KEY `uq_profil_retenue` (`profil_salaire_id`,`retenue_id`),
            CONSTRAINT `fk_pr_profil` FOREIGN KEY (`profil_salaire_id`) REFERENCES `profil_salaires` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_pr_retenue` FOREIGN KEY (`retenue_id`) REFERENCES `retenues` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'paiements_salaires' => "CREATE TABLE `paiements_salaires` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT, `user_id` bigint unsigned NOT NULL,
            `profil_salaire_id` bigint unsigned NULL, `echelon_id` bigint unsigned NULL,
            `mois` tinyint unsigned NOT NULL, `annee` smallint unsigned NOT NULL,
            `salaire_base` decimal(12,2) NOT NULL DEFAULT 0, `total_indemnites` decimal(12,2) NOT NULL DEFAULT 0,
            `total_retenues` decimal(12,2) NOT NULL DEFAULT 0, `salaire_net` decimal(12,2) NOT NULL DEFAULT 0,
            `detail_json` longtext NULL, `statut` enum('en_attente','valide','paye') NOT NULL DEFAULT 'en_attente',
            `valide_par` bigint unsigned NULL, `valide_le` timestamp NULL, `paye_par` bigint unsigned NULL, `paye_le` timestamp NULL,
            `note` text NULL, `created_at` timestamp NULL, `updated_at` timestamp NULL, PRIMARY KEY (`id`),
            UNIQUE KEY `uq_paiement_user_mois` (`user_id`,`mois`,`annee`), KEY `fk_paie_profil` (`profil_salaire_id`),
            KEY `fk_paie_echelon` (`echelon_id`), KEY `fk_paie_valide_par` (`valide_par`), KEY `fk_paie_paye_par` (`paye_par`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'ajustements_salaire' => "CREATE TABLE `ajustements_salaire` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT, `user_id` bigint unsigned NOT NULL,
            `mois` tinyint unsigned NOT NULL, `annee` smallint unsigned NOT NULL, `type` enum('bonus','retenue') NOT NULL,
            `mode` enum('fixe','pourcentage') NOT NULL DEFAULT 'fixe', `libelle` varchar(255) NOT NULL,
            `montant` decimal(12,2) NOT NULL DEFAULT 0, `motif` text NULL, `created_by` bigint unsigned NULL,
            `created_at` timestamp NULL, `updated_at` timestamp NULL, PRIMARY KEY (`id`),
            KEY `ajustements_salaire_user_id_mois_annee_index` (`user_id`,`mois`,`annee`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'departements' => "CREATE TABLE `departements` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT, `nom` varchar(255) NOT NULL, `code` varchar(255) NULL,
            `description` text NULL, `responsable_id` bigint unsigned NULL, `status` varchar(255) NOT NULL DEFAULT 'pending',
            `created_at` timestamp NULL, `updated_at` timestamp NULL, `cycle_id` bigint NULL, PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'documents' => "CREATE TABLE `documents` (
            `id` bigint NOT NULL AUTO_INCREMENT, `path` varchar(255) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(), `updated_at` timestamp NULL,
            `article_id` bigint unsigned NOT NULL, PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'images' => "CREATE TABLE `images` (
            `id` bigint NOT NULL AUTO_INCREMENT, `path` varchar(255) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(), `updated_at` timestamp NULL,
            `article_id` bigint unsigned NOT NULL, PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'notes' => "CREATE TABLE `notes` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT, `examen_id` bigint unsigned NOT NULL, `etudiant_id` bigint unsigned NOT NULL,
            `cours_id` bigint unsigned NULL, `cc` decimal(5,2) NULL, `exam` decimal(5,2) NULL, `valeur` decimal(5,2) NULL,
            `commentaire` text NULL, `validee_par` bigint unsigned NULL, `validee_at` timestamp NULL,
            `created_at` timestamp NULL, `updated_at` timestamp NULL, `rattrapage` float(5,2) NULL, PRIMARY KEY (`id`),
            UNIQUE KEY `unique_note` (`examen_id`,`etudiant_id`,`cours_id`), KEY `notes_etudiant_id_foreign` (`etudiant_id`),
            KEY `notes_cours_id_foreign` (`cours_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'rapports' => "CREATE TABLE `rapports` (
            `id` bigint NOT NULL AUTO_INCREMENT, `user_id` bigint NOT NULL, `specialite_id` bigint NOT NULL, `filiere_id` bigint NOT NULL,
            `status` enum('pending','Success','failed') NOT NULL DEFAULT 'pending',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(), `updated_at` timestamp NULL,
            `content` longtext NOT NULL, `title` longtext NOT NULL, PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    /** Colonnes lues ou ecrites par le code actuel. */
    private const COLONNES = [
        ['articles', 'image', 'varchar(255) NULL'],
        ['articles', 'published_at', 'timestamp NULL'],
        ['cours', 'filiere_id', 'bigint unsigned NULL'],
        ['cours', 'specialite_id', 'bigint NULL'],
        ['cours', 'formation_type', 'varchar(255) NULL'],
        ['cours', 'examen_id', 'bigint NULL'],
        ['cycles', 'institution', "enum('ISM','IFPM','GSBM') NOT NULL DEFAULT 'GSBM'"],
        ['examens', 'titre', 'varchar(255) NULL'],
        ['examens', 'statut', "enum('ouvert','en_cours','ferme','annule') NOT NULL DEFAULT 'ouvert'"],
        ['examens', 'description', 'text NULL'],
        ['filieres', 'responsable_id', 'bigint NULL'],
        ['filieres', 'code', 'varchar(255) NULL'],
        ['filieres', 'description', 'longtext NULL'],
        ['filieres', 'departement_id', 'bigint NULL'],
        ['salles', 'filiere_id', 'bigint NULL'],
        ['salles', 'specialite_id', 'bigint NULL'],
        ['specialites', 'responsable_id', 'bigint NULL'],
        ['specialites', 'cycle_id', 'bigint NULL'],
        ['ues', 'specialite_id', 'bigint NULL'],
        ['ues', 'filiere_id', 'bigint NULL'],
        ['ues', 'formation_type', 'varchar(255) NULL'],
        ['ues', 'credits', 'int NULL'],
        ['ues', 'hour_number', 'int NULL'],
        ['ues', 'examen_id', 'bigint NULL'],
        ['users', 'profil_salaire_id', 'bigint unsigned NULL'],
        ['users', 'categorie_rh_id', 'bigint unsigned NULL'],
        ['users', 'echelon_id', 'bigint unsigned NULL'],
        ['users', 'poste', 'varchar(255) NULL'],
        ['users', 'entite', 'varchar(255) NULL'],
        ['users', 'photo', 'varchar(255) NULL'],
        ['users', 'cropped_photo', 'varchar(255) NULL'],
        ['users', 'departement_id', 'bigint NULL'],
        ['notes', 'rattrapage', 'float(5,2) NULL'],
    ];

    /**
     * Colonnes d'anciennes versions, obligatoires et sans valeur par defaut,
     * que le code actuel ne renseigne plus : elles bloqueraient les insertions.
     */
    private const FACULTATIVES = [
        ['articles', 'image_1'], ['cours', 'section_id'], ['cours', 'responsable_id'], ['cours', 'credit'],
        ['cours', 'code'], ['cycles', 'section_id'], ['examens', 'cours_id'], ['examens', 'section_id'],
        ['examens', 'status'], ['examens', 'salle_id'], ['examens', 'heure'], ['examens', 'created_at'],
        ['salles', 'section_id'], ['ues', 'code'], ['ues', 'examen_id'], ['users', 'contact'],
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $definition) {
            if (! Schema::hasTable($table)) {
                DB::statement($definition);
            }
        }

        foreach (self::COLONNES as [$table, $colonne, $definition]) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, $colonne)) {
                DB::statement("ALTER TABLE `$table` ADD `$colonne` $definition");
            }
        }

        foreach (self::FACULTATIVES as [$table, $colonne]) {
            $type = $this->type($table, $colonne);

            if ($type !== null && $type->IS_NULLABLE === 'NO') {
                DB::statement("ALTER TABLE `$table` MODIFY `$colonne` {$type->COLUMN_TYPE} NULL");
            }
        }

        // Le code enregistre des dates avec heure.
        if (($this->type('examens', 'date')?->COLUMN_TYPE) === 'date') {
            DB::statement('ALTER TABLE `examens` MODIFY `date` datetime NULL');
        }

        /*
         * Sur une base reprise d'IUM la colonne existe deja et l'on se
         * contente d'en elargir les valeurs ; sur une base neuve elle n'a
         * jamais ete creee — aucune migration ne s'en charge. Il faut donc
         * savoir l'ajouter, sans quoi la migration echoue sur une
         * installation partie de rien.
         */
        $roles = "enum('student','personnel','filiere','specialite','admin','enseignant','concierge','bibliothecaire','coordonnateur') NOT NULL DEFAULT 'student'";

        DB::statement(Schema::hasColumn('users', 'role')
            ? "ALTER TABLE `users` MODIFY `role` $roles"
            : "ALTER TABLE `users` ADD `role` $roles");
        DB::statement("ALTER TABLE `cycles` MODIFY `institution` enum('ISM','IFPM','GSBM') NOT NULL");
    }

    /** Rien a defaire : la migration n'ajoute que des tables et colonnes facultatives. */
    public function down(): void
    {
    }

    private function type(string $table, string $colonne): ?object
    {
        return DB::selectOne(
            'SELECT COLUMN_TYPE, IS_NULLABLE FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $colonne]
        );
    }
};
