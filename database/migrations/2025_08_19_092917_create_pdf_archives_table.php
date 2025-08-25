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
        Schema::create('pdfs_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained('documents');
            $table->foreignId('fiche_depense_id')->nullable()->constrained('fiches_depenses');
            $table->enum('type_pdf', ['bon_menues_depenses', 'lettre_commande', 'fiche_depenses']);
            
            // Informations du fichier
            $table->string('nom_fichier', 255);
            $table->string('chemin_fichier', 500);
            
            // Métadonnées
            $table->foreignId('generated_by')->constrained('users');
            $table->timestamp('date_generation')->useCurrent();
            $table->timestamps();
            
            // Au moins une des deux clés étrangères doit être renseignée
            $table->check('document_id IS NOT NULL OR fiche_depense_id IS NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdfs_archives');
    }
};