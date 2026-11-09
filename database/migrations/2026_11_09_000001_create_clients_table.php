<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('company')->index();

            // Personne morale
            $table->string('company_name')->nullable();
            $table->string('ice', 15)->nullable();
            $table->string('if_number')->nullable();
            $table->string('rc')->nullable();
            $table->string('cnss')->nullable();

            // Personne physique
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('cin', 20)->nullable();

            // Coordonnées
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['company_name', 'first_name', 'last_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
