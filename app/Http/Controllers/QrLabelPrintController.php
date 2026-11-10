<?php

namespace App\Http\Controllers;

use App\Livewire\Documents\DocumentType;
use App\Models\Client;
use App\Models\DocumentHistory;
use Illuminate\Contracts\View\View;

class QrLabelPrintController extends Controller
{
    /**
     * Printable label for a single archived client QR code.
     */
    public function client(string $token): View
    {
        $this->authorizeAccess();

        $client = Client::query()
            ->where('public_token', $token)
            ->firstOrFail();

        return view('qr-groups.label-print', [
            'title' => $client->displayName(),
            'subtitle' => $client->isCompany() ? 'Client — Entreprise' : 'Client — Particulier',
            'url' => route('archive.clients.show', $client->public_token),
            'meta' => array_values(array_filter([
                $client->email,
                $client->phone,
                $client->city,
            ])),
        ]);
    }

    /**
     * Printable label for a single archived document QR code.
     */
    public function document(string $token): View
    {
        $this->authorizeAccess();

        $document = DocumentHistory::query()
            ->where('public_token', $token)
            ->with(['client', 'invoice'])
            ->firstOrFail();

        return view('qr-groups.label-print', [
            'title' => $document->title,
            'subtitle' => DocumentType::tryFrom($document->document_type)?->title() ?? 'Document',
            'url' => route('archive.documents.show', $document->public_token),
            'meta' => array_values(array_filter([
                $document->client?->displayName() ?? data_get($document->data, 'client.name'),
                data_get($document, 'invoice.number') ?? data_get($document->data, 'invoice_number'),
            ])),
        ]);
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('documents_view')
            || $user?->hasPermission('clients_view')
            || $user?->hasPermission('invoices_view'),
            403,
        );
    }
}
