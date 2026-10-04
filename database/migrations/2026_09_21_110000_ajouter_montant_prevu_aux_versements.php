<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La fiche d'inscription distingue ce qui est du de ce qui est paye :
 * la colonne MONTANT ne se remplit qu'au versement, avec sa date et son visa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->decimal('montant_prevu', 12, 2)->default(0)->after('libelle');
        });

        // Les echeanciers deja crees portaient le previsionnel dans « montant ».
        \Illuminate\Support\Facades\DB::statement(
            'update versements set montant_prevu = montant, montant = 0 where date_versement is null'
        );
    }

    public function down(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->dropColumn('montant_prevu');
        });
    }
};
