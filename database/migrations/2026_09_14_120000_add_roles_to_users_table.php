<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rôles attribués par le portail. Un employé peut en cumuler plusieurs ;
     * la colonne `role` garde le rôle principal pour le code existant.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'roles')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->json('roles')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('roles');
        });
    }
};
