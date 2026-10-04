<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ajustements_salaire', 'mode')) {
            Schema::table('ajustements_salaire', function (Blueprint $table) {
                // 'fixe' : montant en FCFA ; 'pourcentage' : montant = % du salaire de base
                $table->enum('mode', ['fixe', 'pourcentage'])->default('fixe')->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ajustements_salaire', 'mode')) {
            Schema::table('ajustements_salaire', function (Blueprint $table) {
                $table->dropColumn('mode');
            });
        }
    }
};
