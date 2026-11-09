<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Factures')]
class InvoicesList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $clientFilter = '';

    public function mount(): void
    {
        $this->authorizeAction('invoices_view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedClientFilter(): void
    {
        $this->resetPage();
    }

    public function deleteInvoice(int $invoiceId): void
    {
        $this->authorizeAction('invoices_delete');

        $invoice = Invoice::query()->findOrFail($invoiceId);
        $invoice->delete();

        session()->flash('status', 'La facture '.$invoice->number.' a été supprimée.');
    }

    public function generateAndPrint(int $invoiceId): void
    {
        $this->authorizeAction('invoices_view');

        $invoice = Invoice::query()->findOrFail($invoiceId);
        $history = $invoice->generateDocument((int) auth()->id());

        $this->redirectRoute('documents.print', ['history' => $history->id], navigate: true);
    }

    public function render(): View
    {
        $invoices = Invoice::query()
            ->with('client')
            ->withCount('items')
            ->when($this->search !== '', function ($query): void {
                $search = '%'.$this->search.'%';

                $query->where(function ($query) use ($search): void {
                    $query->where('number', 'like', $search)
                        ->orWhereHas('client', function ($query) use ($search): void {
                            $query->where('company_name', 'like', $search)
                                ->orWhere('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search);
                        });
                });
            })
            ->when($this->clientFilter !== '', fn ($query) => $query->where('client_id', $this->clientFilter))
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(10);

        return view('livewire.invoices.index', [
            'invoices' => $invoices,
            'clients' => Client::query()->orderBy('company_name')->orderBy('last_name')->get(),
        ]);
    }

    private function authorizeAction(string $permission): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->hasPermission($permission), 403);
    }
}
