<?php

namespace App\Livewire\Archive;

use App\Models\DocumentHistory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Archive - Document')]
class DocumentShow extends Component
{
    public DocumentHistory $document;

    public function mount(string $token): void
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('documents_view')
            || $user?->hasPermission('clients_view')
            || $user?->hasPermission('invoices_view'),
            403,
        );

        $this->document = DocumentHistory::query()
            ->where('public_token', $token)
            ->firstOrFail();
    }

    public function render(): View
    {
        $this->document->load(['client', 'invoice', 'demande']);

        return view('livewire.archive.document-show', [
            'document' => $this->document,
        ]);
    }
}
