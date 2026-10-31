<?php

namespace App\Http\Controllers;

use App\Models\DocumentHistory;

class DocumentPrintController extends Controller
{
    public function show(DocumentHistory $history)
    {
        // Ensure the user owns this document
        if ($history->user_id !== auth()->id()) {
            abort(403);
        }

        return response($history->html)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function print(DocumentHistory $history)
    {
        // Ensure the user owns this document
        if ($history->user_id !== auth()->id()) {
            abort(403);
        }

        // Return a print-ready page with the HTML and a print button
        return view('documents.print', [
            'history' => $history,
            'html' => $history->html,
        ]);
    }
}
