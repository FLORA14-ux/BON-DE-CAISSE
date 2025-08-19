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
        Schema::create('fiche_depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->string('numero_fiche', 50)->unique();
            $table->decimal('montant_reel', 15, 2);
            $table->decimal('difference', 15, 2)->default(0);
            $table->text('observations')->nullable();
            // Signatures (statuts)
            $table->boolean('signature_etabli')->default(false);
            $table->boolean('signature_visa_controle')->default(false);
            $table->boolean('signature_visa_chef_financier')->default(false);
            $table->boolean('signature_directeur_financier')->default(false);
            $table->boolean('signature_beneficiaire')->default(false);
            $table->boolean('signature_caissier')->default(false);
            // Dates des signatures
            $table->datetime('date_signature_etabli')->nullable();
            $table->datetime('date_signature_visa_controle')->nullable();
            $table->datetime('date_signature_visa_chef_financier')->nullable();
            $table->datetime('date_signature_directeur_financier')->nullable();
            $table->datetime('date_signature_beneficiaire')->nullable();
            $table->datetime('date_signature_caissier')->nullable();
            $table->enum('statut', ['cree', 'en_signature', 'complete'])->default('cree');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiche_depenses');
    }
};
