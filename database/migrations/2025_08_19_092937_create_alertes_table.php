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
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->enum('type_alerte', ['regularisation_retard', 'document_non_signe', 'autre']);
            $table->string('titre', 200);
            $table->text('message');
            $table->enum('niveau', ['info', 'warning', 'error'])->default('warning');
            $table->enum('statut', ['active', 'resolue', 'ignoree'])->default('active');
            $table->datetime('date_resolution')->nullable();
            $table->timestamps();
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
