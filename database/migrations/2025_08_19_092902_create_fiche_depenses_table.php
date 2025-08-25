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
        Schema::create('fiches_depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents');
            $table->string('numero_fiche')->unique();
            
            // Informations de la fiche
            $table->decimal('montant_reel', 12, 2);
            $table->decimal('difference', 12, 2)->nullable();
            $table->text('observations')->nullable();
            
            // Signatures requises (statut de chaque signature)
            $table->boolean('signature_etabli')->default(false);
            $table->boolean('signature_visa_controle')->default(false);
            $table->boolean('signature_visa_chef_financier')->default(false);
            $table->boolean('signature_directeur_financier')->default(false);
            $table->boolean('signature_beneficiaire')->default(false);
            $table->boolean('signature_caissier')->default(false);
            
            // Dates des signatures
            $table->timestamp('date_signature_etabli')->nullable();
            $table->timestamp('date_signature_visa_controle')->nullable();
            $table->timestamp('date_signature_visa_chef_financier')->nullable();
            $table->timestamp('date_signature_directeur_financier')->nullable();
            $table->timestamp('date_signature_beneficiaire')->nullable();
            $table->timestamp('date_signature_caissier')->nullable();
            
            // Statut global de la fiche
            $table->enum('statut', ['cree', 'en_signature', 'complete'])->default('cree');
            
            // Utilisateur qui a créé la fiche
            $table->foreignId('created_by')->constrained('users');
            
            // Timestamps
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiches_depenses');
    }
};