<?php

namespace App\Livewire\Archive;

use App\Livewire\Documents\DocumentType;
use App\Models\Client;
use App\Models\DocumentHistory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Archive')]
class Archive extends Component
{
    use WithPagination;

    public string $tab = 'documents';

    public string $search = '';

    public string $typeFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('documents_view')
            || $user?->hasPermission('clients_view')
            || $user?->hasPermission('invoices_view'),
            403,
        );
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['documents', 'clients'], true) ? $tab : 'documents';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        if ($this->tab === 'clients') {
            $clients = Client::query()
                ->withCount('documentHistories')
                ->when($this->search !== '', function ($query): void {
                    $search = '%'.$this->search.'%';

                    $query->where(function ($query) use ($search): void {
                        $query->where('company_name', 'like', $search)
                            ->orWhere('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('email', 'like', $search)
                            ->orWhere('phone', 'like', $search)
                            ->orWhere('ice', 'like', $search)
                            ->orWhere('cin', 'like', $search);
                    });
                })
                ->latest()
                ->paginate(15);

            return view('livewire.archive.index', [
                'tab' => $this->tab,
                'clients' => $clients,
                'documents' => null,
                'documentTypes' => DocumentType::cases(),
            ]);
        }

        $documents = DocumentHistory::query()
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
            ->when($this->dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(15);

        return view('livewire.archive.index', [
            'tab' => $this->tab,
            'clients' => null,
            'documents' => $documents,
            'documentTypes' => DocumentType::cases(),
        ]);
    }
}
