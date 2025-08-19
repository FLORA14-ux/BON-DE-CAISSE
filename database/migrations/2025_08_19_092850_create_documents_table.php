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
            $table->string('numero', 50)->unique();
            $table->enum('type_document', ['bon_menues_depenses', 'lettre_commande']);
            $table->string('fournisseur', 200);
            $table->text('objet_achat');
            $table->decimal('montant', 15, 2);
            $table->string('facture_proforma_numero', 100)->nullable();
            $table->string('facture_definitive_numero', 100)->nullable();
            $table->decimal('montant_reel', 15, 2)->nullable();
            $table->decimal('ecart_montant', 15, 2)->nullable();
            $table->enum('statut', ['cree', 'en_signature', 'signe', 'fonds_retires', 'regularise', 'annule'])->default('cree');
            $table->datetime('date_signature')->nullable();
            $table->datetime('date_retrait_fonds')->nullable();
            $table->datetime('date_regularisation')->nullable();
            $table->integer('delai_regularisation_jours')->default(30);
            $table->boolean('alerte_envoyee')->default(false);
            $table->foreignId('demandeur_id')->constrained('demandeurs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('adresse_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('date_action')->useCurrent();

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
