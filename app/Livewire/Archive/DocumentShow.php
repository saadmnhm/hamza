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
