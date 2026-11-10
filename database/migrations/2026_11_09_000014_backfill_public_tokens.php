<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->backfill('clients');
        $this->backfill('document_histories');
    }

    public function down(): void
    {
        // Tokens are permanent identifiers; rolling back keeps them in place.
    }

    private function backfill(string $table): void
    {
        DB::table($table)
            ->whereNull('public_token')
            ->orderBy('id')
            ->pluck('id')
            ->each(function (mixed $id) use ($table): void {
                DB::table($table)
                    ->where('id', (int) $id)
                    ->update(['public_token' => (string) Str::uuid()]);
            });
    }
};
