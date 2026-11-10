<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
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
        $clientId = $this->invoice->client_id;
        $this->invoice->delete();

        session()->flash('status', 'Le document '.$number.' a été supprimé.');

        if (Client::query()->whereKey($clientId)->exists()) {
            $this->redirectRoute('clients.show', ['client' => $clientId], navigate: true);

            return;
        }

        $this->redirectRoute('documents', navigate: true);
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
