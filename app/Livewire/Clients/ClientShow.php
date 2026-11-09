<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Détail du client')]
class ClientShow extends Component
{
    public Client $client;

    public function mount(Client $client): void
    {
        $this->authorizeAction('clients_view');

        $this->client = $client;
    }

    public function deleteClient(): void
    {
        $this->authorizeAction('clients_delete');

        $client = Client::query()
            ->withCount(['invoices', 'documentHistories'])
            ->findOrFail($this->client->id);

        if ($client->invoices_count > 0) {
            session()->flash('error', 'Ce client est lié à '.$client->invoices_count.' facture(s). Supprimez ou réattribuez ces factures avant de continuer.');

            $this->redirectRoute('clients.index', navigate: true);

            return;
        }

        if ($client->document_histories_count > 0) {
            session()->flash('error', 'Ce client est lié à '.$client->document_histories_count.' document(s). Supprimez ces documents avant de continuer.');

            $this->redirectRoute('clients.index', navigate: true);

            return;
        }

        $client->delete();

        session()->flash('status', 'Le client a été supprimé.');

        $this->redirectRoute('clients.index', navigate: true);
    }

    public function render(): View
    {
        $this->client->load([
            'invoices',
            'Demandes' => fn ($query) => $query->with(['invoices', 'documentHistories'])->latest('date'),
            'documentHistories' => fn ($query) => $query->with('invoice')->latest(),
        ]);

        return view('livewire.clients.show', [
            'client' => $this->client,
        ]);
    }

    private function authorizeAction(string $permission): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user?->hasPermission($permission), 403);
    }
}
