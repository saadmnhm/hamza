<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_groups', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_token')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('description', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('qr_group_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_group_id')->constrained('qr_groups')->cascadeOnDelete();
            $table->string('item_type', 20);
            $table->unsignedBigInteger('item_id');
            $table->uuid('public_token');
            $table->string('label');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['qr_group_id', 'item_type', 'item_id']);
            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_group_items');
        Schema::dropIfExists('qr_groups');
    }
};
