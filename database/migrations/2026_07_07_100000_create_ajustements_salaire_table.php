<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ajustements_salaire')) {
            return;
        }

        Schema::create('ajustements_salaire', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Employé concerné');
            $table->unsignedTinyInteger('mois');
            $table->unsignedSmallInteger('annee');
            $table->enum('type', ['bonus', 'retenue']);
            $table->string('libelle');                 // Ex: Retard, Prime de rendement
            $table->decimal('montant', 12, 2)->default(0);
            $table->text('motif')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'mois', 'annee']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustements_salaire');
    }
};
