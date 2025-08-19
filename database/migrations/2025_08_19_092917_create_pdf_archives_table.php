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
        Schema::create('pdf_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained('documents')->onDelete('cascade');
            $table->foreignId('fiche_depense_id')->nullable()->constrained('fiche_depenses')->onDelete('cascade');
            $table->enum('type_pdf', ['bon_menues_depenses', 'lettre_commande', 'fiche_depenses']);
            $table->string('nom_fichier', 255);
            $table->string('chemin_fichier', 500);
            $table->integer('taille_octets')->nullable();
            $table->string('hash_md5', 32)->nullable();
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_archives');
    }
};
