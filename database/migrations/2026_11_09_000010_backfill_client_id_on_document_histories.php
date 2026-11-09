<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Link existing invoice documents to their client so they appear in the
     * client interface as well as the invoice interface.
     */
    public function up(): void
    {
        DB::table('document_histories')
            ->whereNotNull('invoice_id')
            ->whereNull('client_id')
            ->update([
                'client_id' => DB::raw('(SELECT client_id FROM invoices WHERE invoices.id = document_histories.invoice_id)'),
            ]);
    }

    public function down(): void
    {
        // The backfill is non-destructive; there is nothing to revert.
    }
};
