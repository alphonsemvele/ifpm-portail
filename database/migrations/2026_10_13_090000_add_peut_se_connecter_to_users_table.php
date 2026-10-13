<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Interrupteur local de connexion.
 *
 * L'institut garde le dernier mot sur qui entre chez lui : le portail peut
 * fermer une porte, il ne rouvre pas celle que l'institut a fermee. La
 * connexion SSO verifiait deja cette colonne — elle n'existait pas encore ici.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'peut_se_connecter')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('peut_se_connecter')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'peut_se_connecter')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('peut_se_connecter');
        });
    }
};
