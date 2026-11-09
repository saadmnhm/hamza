<?php

namespace App\Http\Controllers;

use App\Models\DocumentHistory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class DocumentPrintController extends Controller
{
    public function show(DocumentHistory $history): Response
    {
        $this->authorizePrint($history);

        return response($history->html)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function print(DocumentHistory $history): View
    {
        $this->authorizePrint($history);

        // Return a print-ready page with the HTML and a print button
        return view('documents.print', [
            'history' => $history,
            'html' => $history->html,
        ]);
    }

    /**
     * Owners can always print their own documents. Users with the relevant
     * module permission can print documents linked to a client or invoice.
     */
    private function authorizePrint(DocumentHistory $history): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        $isOwner = $user !== null && (int) $history->user_id === (int) $user->getKey();

        $canPrint = $isOwner
            || $user?->hasPermission('documents_view')
            || ($history->client_id !== null && $user?->hasPermission('clients_view'))
            || ($history->invoice_id !== null && $user?->hasPermission('invoices_view'));

        abort_unless($canPrint, 403);
    }
}
