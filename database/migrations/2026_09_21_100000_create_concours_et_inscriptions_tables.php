<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Concours d'entree et inscriptions des apprenants.
 *
 * Les colonnes reprennent les trois documents officiels de public/inscription :
 * la fiche de renseignement (candidat), la fiche d'inscription (apprenant admis)
 * et la liste des candidats par session.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions_concours', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');                       // « Concours du 19 septembre 2026 »
            $table->date('date_concours')->nullable();
            $table->string('annee', 12);                     // année de formation : 2026-2027
            $table->string('lieu')->nullable();
            $table->enum('type', ['concours', 'dossier', 'mixte'])->default('concours');
            $table->enum('statut', ['ouverte', 'fermee'])->default('ouverte');
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->index(['annee', 'date_concours']);
        });

        Schema::create('candidats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('sessions_concours')->cascadeOnDelete();
            $table->date('date_inscription');

            // Identite
            $table->string('nom');
            $table->string('prenoms')->nullable();
            $table->enum('sexe', ['M', 'F'])->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('cni')->nullable();
            $table->string('nationalite')->default('Camerounaise');
            $table->string('profession')->nullable();
            $table->enum('situation_familiale', ['marie', 'celibataire', 'divorce'])->nullable();
            $table->string('profession_conjoint')->nullable();

            // Origine et residence
            $table->string('region_origine')->nullable();
            $table->string('departement_origine')->nullable();
            $table->string('ville')->nullable();
            $table->string('quartier')->nullable();

            // Contacts
            $table->string('telephone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->enum('langue', ['fr', 'en'])->default('fr');

            // Formation demandee : trois choix, par ordre de preference
            $table->enum('admission', ['concours', 'dossier'])->default('concours');
            $table->foreignId('filiere1_id')->nullable()->constrained('filieres')->nullOnDelete();
            $table->foreignId('filiere2_id')->nullable()->constrained('filieres')->nullOnDelete();
            $table->foreignId('filiere3_id')->nullable()->constrained('filieres')->nullOnDelete();

            // Diplome presente
            $table->string('diplome')->nullable();           // diplôme le plus élevé
            $table->date('date_obtention')->nullable();
            $table->string('niveau')->nullable();            // BEPC, Probatoire, BAC…

            // Pieces du dossier (cases de la fiche de renseignement)
            $table->boolean('piece_fiche')->default(false);
            $table->boolean('piece_acte')->default(false);
            $table->boolean('piece_diplome')->default(false);
            $table->boolean('piece_photos')->default(false);
            $table->boolean('piece_enveloppe')->default(false);

            $table->enum('statut', ['inscrit', 'admis', 'refuse', 'absent'])->default('inscrit');
            $table->text('observations')->nullable();

            // Empreinte nom + prenoms + naissance : empeche deux fois le meme
            // candidat dans une session.
            $table->string('cle_doublon', 191);
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['session_id', 'cle_doublon']);
            $table->index('statut');
        });

        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidat_id')->nullable()->constrained('candidats')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('matricule')->unique();
            $table->string('annee', 12);

            $table->enum('civilite', ['Mme', 'Mlle', 'M'])->nullable();
            $table->string('nom');
            $table->string('prenoms')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('nationalite')->default('Camerounaise');
            $table->string('religion')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->enum('regime', ['externe', 'interne'])->default('externe');

            // Admission
            $table->foreignId('filiere_id')->nullable()->constrained('filieres')->nullOnDelete();
            $table->foreignId('specialite_id')->nullable()->constrained('specialites')->nullOnDelete();
            $table->string('conditions_acces')->nullable();
            $table->string('duree_formation')->nullable();
            $table->string('diplome_vise')->nullable();
            $table->string('metiers')->nullable();
            $table->decimal('montant_scolarite', 12, 2)->default(0);

            // Prise en charge medicale
            $table->boolean('assurance_ifpm')->default(false);
            $table->string('compagnie_assurance')->nullable();
            $table->date('date_visite_medicale')->nullable();
            $table->string('code_visite')->nullable();

            $table->enum('statut', ['en_cours', 'solde', 'abandon'])->default('en_cours');
            $table->text('observations')->nullable();
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['annee', 'statut']);
        });

        Schema::create('versements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscription_id')->constrained('inscriptions')->cascadeOnDelete();
            $table->unsignedSmallInteger('rang');            // ordre d'affichage sur la fiche
            $table->string('libelle');                       // Inscription, 1er versement…
            $table->decimal('montant', 12, 2)->default(0);
            $table->date('date_versement')->nullable();
            $table->boolean('vise')->default(false);         // visa du Secrétariat après passage en banque
            $table->decimal('penalite', 12, 2)->nullable();
            $table->timestamps();

            $table->index(['inscription_id', 'rang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versements');
        Schema::dropIfExists('inscriptions');
        Schema::dropIfExists('candidats');
        Schema::dropIfExists('sessions_concours');
    }
};
