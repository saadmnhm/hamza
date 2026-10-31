<?php

namespace App\Livewire\Documents;

use Livewire\Component;

class DocumentsList extends Component
{
    public $selectedDocument = null;

    public $showModal = false;

    public function openModal(string $documentType): void
    {
        $this->selectedDocument = DocumentType::tryFrom($documentType);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->selectedDocument = null;
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.documents.list', [
            'documentTypes' => DocumentType::cases(),
        ]);
    }
}
