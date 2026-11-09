<?php

use App\Livewire\Clients\ClientShow;
use App\Livewire\Documents\DocumentsList;
use App\Livewire\Invoices\InvoiceForm;
use App\Livewire\Invoices\InvoiceShow;
use App\Models\Client;
use App\Models\DocumentHistory;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('administrators can create an invoice with multiple lines', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $client = Client::factory()->create(['company_name' => 'Facture SARL']);

    Livewire::actingAs($admin)
        ->test(InvoiceForm::class)
        ->set('client_id', (string) $client->id)
        ->set('invoice_date', '2026-11-09')
        ->set('due_date', '2026-12-09')
        ->set('items', [
            [
                'uid' => 'line-a',
                'product' => 'Service A',
                'description' => 'Description A',
                'quantity' => 2,
                'unit_price' => 100,
                'tva_rate' => 20,
            ],
            [
                'uid' => 'line-b',
                'product' => 'Service B',
                'description' => 'Description B',
                'quantity' => 1,
                'unit_price' => 50,
                'tva_rate' => 10,
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $invoice = Invoice::query()->latest('id')->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->number)->toMatch('/^FAC-\d{4}-\d{4}$/')
        ->and((float) $invoice->subtotal)->toBe(250.0)
        ->and((float) $invoice->tva_total)->toBe(45.0)
        ->and((float) $invoice->total)->toBe(295.0)
        ->and($invoice->items)->toHaveCount(2)
        ->and((float) $invoice->items[0]->total_ht)->toBe(200.0)
        ->and((float) $invoice->items[0]->tva_amount)->toBe(40.0)
        ->and((float) $invoice->items[1]->total_ttc)->toBe(55.0);
});

test('invoice numbers are generated sequentially per year', function () {
    $client = Client::factory()->create();

    Invoice::factory()->create([
        'client_id' => $client->id,
        'number' => 'FAC-2026-0001',
        'invoice_date' => '2026-01-01',
    ]);

    expect(Invoice::generateNumber('2026-05-01'))->toBe('FAC-2026-0002');
});

test('updating an invoice recalculates all totals', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $client = Client::factory()->create();

    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'number' => 'FAC-2026-0010',
        'invoice_date' => '2026-11-09',
        'due_date' => '2026-12-09',
    ]);

    $invoice->items()->create([
        'position' => 0,
        'product' => 'Ancienne ligne',
        'quantity' => 1,
        'unit_price' => 100,
        'tva_rate' => 20,
    ]);
    $invoice->recalculateTotals();

    Livewire::actingAs($admin)
        ->test(InvoiceForm::class, ['invoice' => $invoice])
        ->set('items', [
            [
                'uid' => 'line-a',
                'product' => 'Ligne mise à jour',
                'description' => '',
                'quantity' => 3,
                'unit_price' => 100,
                'tva_rate' => 14,
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $invoice->refresh();

    expect((float) $invoice->subtotal)->toBe(300.0)
        ->and((float) $invoice->tva_total)->toBe(42.0)
        ->and((float) $invoice->total)->toBe(342.0)
        ->and($invoice->items)->toHaveCount(1)
        ->and($invoice->items[0]->product)->toBe('Ligne mise à jour');
});

test('only the supported tva rates are accepted', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $client = Client::factory()->create();

    Livewire::actingAs($admin)
        ->test(InvoiceForm::class)
        ->set('client_id', (string) $client->id)
        ->set('invoice_date', '2026-11-09')
        ->set('items', [
            [
                'uid' => 'line-a',
                'product' => 'Service',
                'quantity' => 1,
                'unit_price' => 100,
                'tva_rate' => 5,
            ],
        ])
        ->call('save')
        ->assertHasErrors(['items.0.tva_rate']);
});

test('generating an invoice document stores a history row and prints client data', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $client = Client::factory()->individual()->create([
        'first_name' => 'Yassine',
        'last_name' => 'Bennani',
        'cin' => 'AB123456',
    ]);

    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'number' => 'FAC-2026-0020',
        'invoice_date' => '2026-11-09',
    ]);

    $invoice->items()->create([
        'position' => 0,
        'product' => 'Conseil',
        'quantity' => 2,
        'unit_price' => 500,
        'tva_rate' => 20,
    ]);
    $invoice->recalculateTotals();

    Livewire::actingAs($admin)
        ->test(InvoiceShow::class, ['invoice' => $invoice])
        ->call('generateDocument');

    $history = DocumentHistory::query()->where('invoice_id', $invoice->id)->first();

    expect($history)->not->toBeNull()
        ->and($history->document_type)->toBe('invoice')
        ->and($history->client_id)->toBe($client->id)
        ->and($history->data['client']['name'])->toBe('Yassine Bennani')
        ->and($history->data['items'][0]['product'])->toBe('Conseil')
        ->and($history->html)->toContain('Yassine Bennani')
        ->and($history->html)->toContain('Conseil')
        ->and($history->html)->toContain('1 200,00 DH');

    $this->actingAs($admin)
        ->get(route('documents.print', $history))
        ->assertOk()
        ->assertSee('Yassine Bennani')
        ->assertSee('Conseil');
});

test('selecting a catalogue item pre-fills the invoice line', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $item = Item::factory()->create([
        'name' => 'Audit annuel',
        'description' => 'Audit complet',
        'unit_price' => 1000,
        'tva_rate' => 14,
    ]);

    Livewire::actingAs($admin)
        ->test(InvoiceForm::class)
        ->set('items.0.item_id', (string) $item->id)
        ->assertSet('items.0.product', 'Audit annuel')
        ->assertSet('items.0.description', 'Audit complet')
        ->assertSet('items.0.unit_price', 1000.0)
        ->assertSet('items.0.tva_rate', 14.0);
});

test('invoice lines keep a reference to the selected catalogue item', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $client = Client::factory()->create();
    $item = Item::factory()->create();

    Livewire::actingAs($admin)
        ->test(InvoiceForm::class)
        ->set('client_id', (string) $client->id)
        ->set('invoice_date', '2026-11-09')
        ->set('items', [
            [
                'uid' => 'line-a',
                'item_id' => $item->id,
                'product' => $item->name,
                'description' => '',
                'quantity' => 1,
                'unit_price' => 100,
                'tva_rate' => 20,
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $invoice = Invoice::query()->latest('id')->first();

    expect($invoice->items)->toHaveCount(1)
        ->and($invoice->items[0]->item_id)->toBe($item->id);
});

test('the shared documents page lists and prints documents created in code', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $client = Client::factory()->create(['company_name' => 'Client Document SARL']);
    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'number' => 'FAC-2026-9001',
    ]);

    $history = DocumentHistory::query()->create([
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'invoice_id' => $invoice->id,
        'document_type' => 'invoice',
        'title' => 'Document ajouté en code',
        'data' => [
            'client' => ['name' => $client->displayName()],
            'invoice_number' => $invoice->number,
        ],
        'html' => '<html><body>Document ajouté en code</body></html>',
    ]);

    Livewire::actingAs($admin)
        ->test(DocumentsList::class)
        ->assertSee('Document ajouté en code')
        ->assertSee('Client Document SARL')
        ->assertSee('FAC-2026-9001');

    $this->actingAs($admin)
        ->get(route('documents.print', $history))
        ->assertOk()
        ->assertSee('Document ajouté en code');
});

test('code-created invoice documents automatically link to the client', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->create(['client_id' => $client->id]);

    $history = DocumentHistory::query()->create([
        'user_id' => $admin->id,
        'invoice_id' => $invoice->id,
        'document_type' => 'invoice',
        'title' => 'Document auto-lié',
        'data' => [],
        'html' => '<html><body>Document auto-lié</body></html>',
    ]);

    expect($history->client_id)->toBe($client->id);
});

test('the client detail page lists documents linked to the client', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $client = Client::factory()->create();

    DocumentHistory::query()->create([
        'user_id' => $admin->id,
        'client_id' => $client->id,
        'document_type' => 'invoice',
        'title' => 'Document client',
        'data' => [],
        'html' => '<html><body>Document client</body></html>',
    ]);

    Livewire::actingAs($admin)
        ->test(ClientShow::class, ['client' => $client])
        ->assertSee('Document client');
});

test('the invoice detail page lists documents linked to the invoice', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $invoice = Invoice::factory()->create();

    DocumentHistory::query()->create([
        'user_id' => $admin->id,
        'invoice_id' => $invoice->id,
        'document_type' => 'invoice',
        'title' => 'Document facture',
        'data' => [],
        'html' => '<html><body>Document facture</body></html>',
    ]);

    Livewire::actingAs($admin)
        ->test(InvoiceShow::class, ['invoice' => $invoice])
        ->assertSee('Document facture');
});

test('users with the documents permission can print documents created by others', function () {
    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::factory()->create(['role_id' => $managerRole->id]);
    $owner = User::factory()->create();

    $history = DocumentHistory::query()->create([
        'user_id' => $owner->id,
        'document_type' => 'invoice',
        'title' => 'Document partagé',
        'data' => [],
        'html' => '<html><body>Document partagé</body></html>',
    ]);

    $this->actingAs($manager)
        ->get(route('documents.print', $history))
        ->assertOk()
        ->assertSee('Document partagé');
});
