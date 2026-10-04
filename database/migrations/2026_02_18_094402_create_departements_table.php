<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('departements', function (Blueprint $table) {
    $table->id();
    $table->string('nom')->unique();
    $table->string('code')->unique();
    $table->text('description')->nullable();
    $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
    // Un departement peut n'avoir aucun responsable, et en perdre un sans
    // disparaitre : « set null » exige donc une colonne qui accepte le vide.
    // Sans cela MySQL refuse la contrainte (errno 150) — ce que MyISAM
    // masquait en ignorant purement et simplement les cles etrangeres.
    $table->string('status')->default('pending'); // pending, Success, completed, failed
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departements');
    }
};
