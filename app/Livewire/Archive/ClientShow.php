<?php

namespace App\Livewire\Archive;

use App\Models\Client;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Archive - Client')]
class ClientShow extends Component
{
    public Client $client;

    public function mount(string $token): void
    {
        $this->client = Client::query()
            ->where('public_token', $token)
            ->firstOrFail();
    }

    public function render(): View
    {
        $this->client->load([
            'invoices',
            'demandes' => fn ($query) => $query->with(['invoices', 'documentHistories'])->latest('date'),
            'documentHistories' => fn ($query) => $query->with('invoice')->latest(),
        ]);

        return view('livewire.archive.client-show', [
            'client' => $this->client,
        ]);
    }
}
