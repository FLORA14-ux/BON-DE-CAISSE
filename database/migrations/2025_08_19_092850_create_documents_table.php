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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->enum('type_document', ['bon_menues_depenses', 'lettre_commande']);
            $table->foreignId('demandeur_id')->constrained('demandeurs');
            
            // Informations de la facture proforma
            $table->string('fournisseur');
            $table->text('objet_achat');
            $table->decimal('montant_fcfa', 12, 2);
            $table->string('facture_proforma_numero')->nullable();
            
            // Statuts et dates
            $table->enum('statut', ['cree', 'en_signature', 'signe', 'fonds_retires', 'regularise', 'annule'])->default('cree');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_signature')->nullable();
            $table->timestamp('date_retrait_fonds')->nullable();
            $table->timestamp('date_regularisation')->nullable();
            
            // Informations de régularisation
            $table->string('facture_definitive_numero')->nullable();
            $table->decimal('montant_reel', 12, 2)->nullable();
            
            // Suivi des délais
            $table->integer('delai_regularisation_jours')->nullable();
            $table->boolean('alerte_envoyee')->default(false);
            
            // Utilisateur qui a créé le document
            $table->foreignId('created_by')->constrained('users');
            
            // Timestamp de modification
            $table->timestamp('date_modification')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};