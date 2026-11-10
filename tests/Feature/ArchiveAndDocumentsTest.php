<?php

use App\Livewire\Archive\Archive;
use App\Livewire\Documents\DocumentsList;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

function archiveAdmin(): User
{
    return User::factory()->create([
        'role_id' => Role::query()->where('slug', 'admin')->value('id'),
    ]);
}

function archiveInvoice(Client $client): Invoice
{
    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'type' => Invoice::TYPE_INVOICE,
        'demande_id' => null,
    ]);

    $invoice->items()->create([
        'position' => 0,
        'product' => 'Prestation de test',
        'description' => 'Ligne de test',
        'quantity' => 2,
        'unit_price' => 500,
        'tva_rate' => 20,
    ]);

    $invoice->recalculateTotals();

    return $invoice->refresh();
}

it('renders every document and archive page for an administrator', function () {
    $user = archiveAdmin();
    $client = Client::factory()->create();
    $invoice = archiveInvoice($client);
    $document = $invoice->generateDocument($user->id);

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertOk();
    $this->get(route('clients.index'))->assertOk();
    $this->get(route('clients.show', $client))->assertOk();
    $this->get(route('clients.documents.create', ['client' => $client, 'type' => Invoice::TYPE_INVOICE]))->assertOk();
    $this->get(route('clients.documents.create', ['client' => $client, 'type' => Invoice::TYPE_DELIVERY_NOTE]))->assertOk();
    $this->get(route('clients.documents.create', ['client' => $client, 'type' => Invoice::TYPE_PURCHASE_ORDER]))->assertOk();
    $this->get(route('documents'))->assertOk();
    $this->get(route('documents.employment_contract'))->assertOk();
    $this->get(route('documents.salary_slip'))->assertOk();
    $this->get(route('documents.invoice'))->assertOk();
    $this->get(route('documents.show', $invoice))->assertOk();
    $this->get(route('documents.edit', $invoice))->assertOk();
    $this->get(route('documents.print', $document))->assertOk();
    $this->get(route('archive'))->assertOk();
    $this->get(route('archive.documents.show', $document->public_token))->assertOk();
    $this->get(route('archive.clients.show', $client->public_token))->assertOk();
});

it('creates a commercial document from the documents page', function () {
    $user = archiveAdmin();
    $client = Client::factory()->create();

    Livewire::actingAs($user)
        ->test(DocumentsList::class)
        ->assertOk()
        ->set('commercialClientId', (string) $client->id)
        ->set('commercialType', Invoice::TYPE_PURCHASE_ORDER)
        ->call('createCommercial')
        ->assertRedirect(route('clients.documents.create', [
            'client' => $client->id,
            'type' => Invoice::TYPE_PURCHASE_ORDER,
        ]));
});

it('filters the archive by document type and client search', function () {
    $user = archiveAdmin();
    $client = Client::factory()->create(['company_name' => 'Atlas Distribution SARL']);
    $invoice = archiveInvoice($client);
    $invoice->generateDocument($user->id);

    Livewire::actingAs($user)
        ->test(Archive::class)
        ->assertOk()
        ->set('typeFilter', 'invoice')
        ->assertSee($invoice->number)
        ->set('tab', 'clients')
        ->set('search', 'Atlas Distribution')
        ->assertSee('Atlas Distribution SARL');
});

it('forbids archive access to users without permissions', function () {
    $user = User::factory()->create(['role_id' => null]);

    $this->actingAs($user)
        ->get(route('archive'))
        ->assertForbidden();
});

it('gives every archived client and document a unique public token', function () {
    $user = archiveAdmin();
    $client = Client::factory()->create();
    $invoice = archiveInvoice($client);
    $document = $invoice->generateDocument($user->id);

    expect($client->public_token)->not->toBeNull()
        ->and($document->public_token)->not->toBeNull()
        ->and($client->public_token)->not->toBe($document->public_token)
        ->and(route('archive.documents.show', $document->public_token))->toContain($document->public_token)
        ->and(route('archive.clients.show', $client->public_token))->toContain($client->public_token);
});
