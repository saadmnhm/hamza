<?php

namespace App\Livewire\Documents;

use App\Models\Client;
use App\Models\DocumentHistory;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Documents')]
class DocumentsList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $commercialClientId = '';

    public string $commercialType = Invoice::TYPE_INVOICE;

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

    /**
     * Send the user to the commercial document form with the selected client and type.
     */
    public function createCommercial(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->hasPermission('invoices_create'), 403);

        $validated = $this->validate([
            'commercialClientId' => ['required', 'integer', Rule::exists('clients', 'id')],
            'commercialType' => ['required', 'string', Rule::in(Invoice::TYPES)],
        ]);

        $this->redirectRoute('clients.documents.create', [
            'client' => (int) $validated['commercialClientId'],
            'type' => $validated['commercialType'],
        ], navigate: true);
    }

    public function render(): View
    {
        $histories = DocumentHistory::query()
            ->with(['client', 'invoice', 'demande'])
            ->when($this->typeFilter !== '', fn ($query) => $query->where('document_type', $this->typeFilter))
            ->when($this->search !== '', function ($query): void {
                $search = '%'.$this->search.'%';

                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', $search)
                        ->orWhere('document_type', 'like', $search)
                        ->orWhereHas('client', function ($query) use ($search): void {
                            $query->where('company_name', 'like', $search)
                                ->orWhere('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search);
                        })
                        ->orWhereHas('invoice', fn ($query) => $query->where('number', 'like', $search));
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.documents.list', [
            'documentTypes' => DocumentType::cases(),
            'generationTypes' => [DocumentType::EMPLOYMENT_CONTRACT, DocumentType::SALARY_SLIP],
            'commercialTypes' => [DocumentType::INVOICE, DocumentType::DELIVERY_NOTE, DocumentType::PURCHASE_ORDER],
            'clients' => Client::query()
                ->orderBy('company_name')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
            'histories' => $histories,
        ]);
    }
}
