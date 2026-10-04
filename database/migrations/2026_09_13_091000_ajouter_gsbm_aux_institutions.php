<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Le code partage rattache cycles et filieres a un etablissement : GSBM
 * rejoint ISM et IFPM dans la liste. Aucune valeur existante n'est modifiee.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['cycles', 'filieres'] as $table) {
            if (Schema::hasColumn($table, 'institution')) {
                DB::statement("ALTER TABLE `$table` MODIFY `institution` enum('ISM','IFPM','GSBM') NOT NULL");
            }
        }
    }

    public function down(): void
    {
    }
};
