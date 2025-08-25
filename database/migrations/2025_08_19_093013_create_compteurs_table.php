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
        Schema::create('compteurs', function (Blueprint $table) {
            $table->id();
            $table->enum('type_document', ['bon_menues_depenses', 'lettre_commande', 'fiche_depenses']);
            $table->year('annee');
            $table->integer('compteur')->default(0);
            $table->timestamps();
            
            // Contrainte unique sur type_document et annee
            $table->unique(['type_document', 'annee'], 'unique_type_annee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compteurs');
    }
};