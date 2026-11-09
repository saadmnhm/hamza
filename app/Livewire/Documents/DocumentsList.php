<?php

namespace App\Livewire\Documents;

use App\Models\DocumentHistory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Documents')]
class DocumentsList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->hasPermission('documents_view'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $histories = DocumentHistory::query()
            ->with(['client', 'invoice'])
            ->when($this->typeFilter !== '', fn ($query) => $query->where('document_type', $this->typeFilter))
            ->when($this->search !== '', function ($query): void {
                $search = '%'.$this->search.'%';

                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', $search)
                        ->orWhere('document_type', 'like', $search);
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.documents.list', [
            'documentTypes' => DocumentType::cases(),
            'generationTypes' => [DocumentType::EMPLOYMENT_CONTRACT, DocumentType::SALARY_SLIP],
            'histories' => $histories,
        ]);
    }
}
