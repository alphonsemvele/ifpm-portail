<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'agent figure-t-il aux effectifs de l'institut ?
 *
 * Le portail distingue ses employes de ses comptes techniques : un
 * administrateur du portail ouvre l'application sans etre pour autant un
 * employe. Il entre, mais il n'apparait pas dans le personnel.
 *
 * Vrai par defaut : les comptes locaux d'avant le raccordement gardent leur
 * place, et c'est la liste du portail qui tranche ensuite.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'dans_le_personnel')) {
            return;
        }

        // On se place apres l'interrupteur de connexion quand il existe ;
        // l'ordre des colonnes n'a pas d'importance, mais une colonne absente
        // ferait echouer la migration.
        $apres = Schema::hasColumn('users', 'peut_se_connecter') ? 'peut_se_connecter' : 'status';

        Schema::table('users', function (Blueprint $table) use ($apres) {
            $table->boolean('dans_le_personnel')->default(true)->after($apres);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'dans_le_personnel')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('dans_le_personnel');
        });
    }
};
