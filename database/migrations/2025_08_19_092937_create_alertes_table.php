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
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents');
            $table->enum('type_alerte', ['regularisation_retard', 'document_non_signe', 'autre']);
            
            // Contenu de l'alerte
            $table->string('titre');
            $table->text('message');
            $table->enum('niveau', ['info', 'warning', 'error'])->default('warning');
            
            // Statut
            $table->enum('statut', ['active', 'resolue', 'ignoree'])->default('active');
            $table->timestamp('date_resolution')->nullable();
            
            // Timestamps
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['document_id', 'statut']);
            $table->index(['type_alerte', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertes');
    }
};