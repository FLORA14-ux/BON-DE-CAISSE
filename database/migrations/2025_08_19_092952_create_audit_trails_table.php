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
        Schema::create('audit_trail', function (Blueprint $table) {
            $table->id();
            $table->string('table_concernee');
            $table->unsignedBigInteger('enregistrement_id');
            $table->enum('action', ['creation', 'modification', 'suppression', 'consultation']);
            
            // Détails de l'action
            $table->string('champ_modifie')->nullable();
            $table->text('ancienne_valeur')->nullable();
            $table->text('nouvelle_valeur')->nullable();
            $table->text('description')->nullable();
            
            // Utilisateur et contexte
            $table->foreignId('user_id')->constrained('users');
            $table->string('adresse_ip')->nullable();
            $table->text('user_agent')->nullable();
            
            // Timestamp
            $table->timestamp('date_action')->useCurrent();
            $table->timestamps();
            
            // Index pour améliorer les performances de recherche
            $table->index(['table_concernee', 'enregistrement_id']);
            $table->index(['user_id', 'date_action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trail');
    }
};