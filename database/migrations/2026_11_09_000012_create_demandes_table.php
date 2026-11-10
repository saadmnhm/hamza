<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->index(['client_id', 'date']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('demande_id')
                ->nullable()
                ->after('client_id')
                ->constrained('demandes')
                ->nullOnDelete();
        });

        Schema::table('document_histories', function (Blueprint $table) {
            $table->foreignId('demande_id')
                ->nullable()
                ->after('client_id')
                ->constrained('demandes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('document_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('demande_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('demande_id');
        });

        Schema::dropIfExists('demandes');
    }
};
