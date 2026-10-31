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
        Schema::create('configs', function (Blueprint $table) {
            $table->id();

            $table->string('logo_path')->nullable();
            $table->string('raison_sociale');
            $table->string('nom_commercial')->nullable();
            $table->string('forme_juridique')->nullable();

            // Identifiants Fiscaux & Administratifs (Maroc)
            $table->string('ice', 15);
            $table->string('if_number');
            $table->string('cnss');
            $table->string('rc');
            $table->string('patente');
            $table->string('tribunal_rc');

            // Coordonnées & Siège Social
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_configs');
    }
};
