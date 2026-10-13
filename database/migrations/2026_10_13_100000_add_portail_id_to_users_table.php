<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Identifiant du compte au portail La Majestueuse.
 *
 * C'est la seule cle de rapprochement stable : le matricule et l'adresse sont
 * corriges par l'administration, le nom change, mais cet identifiant-la ne
 * bouge jamais. Il permet de reconnaitre, a la premiere connexion, le compte
 * que le portail nous avait deja annonce.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'portail_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('portail_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'portail_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['portail_id']);
            $table->dropColumn('portail_id');
        });
    }
};
