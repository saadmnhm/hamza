<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Détail de la facture')]
class InvoiceShow extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $this->authorizeAction('invoices_view');

        $this->invoice = $invoice;
    }

    public function generateDocument(): void
    {
        $this->authorizeAction('invoices_view');

        $history = $this->invoice->generateDocument((int) auth()->id());

        $this->redirectRoute('documents.print', ['history' => $history->id], navigate: true);
    }

    public function deleteInvoice(): void
    {
        $this->authorizeAction('invoices_delete');

        $number = $this->invoice->number;
        $this->invoice->delete();

        session()->flash('status', 'La facture '.$number.' a été supprimée.');

        $this->redirectRoute('invoices.index', navigate: true);
    }

    public function render(): View
    {
        $this->invoice->load(['client', 'items', 'documentHistories']);

        return view('livewire.invoices.show', [
            'invoice' => $this->invoice,
        ]);
    }

    private function authorizeAction(string $permission): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->hasPermission($permission), 403);
    }
}
