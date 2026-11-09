<?php

use App\Livewire\Clients\ClientsList;
use App\Models\Client;
use App\Models\DocumentHistory;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('users without permission cannot access client management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertForbidden();
});

test('administrators can list and search clients', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Client::factory()->create(['company_name' => 'Alpha SARL']);
    Client::factory()->individual()->create([
        'first_name' => 'Beta',
        'last_name' => 'Client',
    ]);

    $this->actingAs($admin)
        ->get(route('clients.index'))
        ->assertOk()
        ->assertSee('Alpha SARL')
        ->assertSee('Beta Client');

    Livewire::actingAs($admin)
        ->test(ClientsList::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha SARL')
        ->assertDontSee('Beta Client');
});

test('administrators can create a company client', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Livewire::actingAs($admin)
        ->test(ClientsList::class)
        ->call('openCreateModal')
        ->set('type', Client::TYPE_COMPANY)
        ->set('company_name', 'Gamma Industries')
        ->set('ice', '001234567000089')
        ->set('if_number', '12345678')
        ->set('rc', '123456')
        ->set('cnss', '1234567')
        ->set('address', '1 rue du Test')
        ->set('city', 'Casablanca')
        ->set('postal_code', '20000')
        ->set('phone', '+212600000000')
        ->set('email', 'gamma@example.com')
        ->call('saveClient')
        ->assertHasNoErrors();

    $client = Client::query()->where('company_name', 'Gamma Industries')->first();

    expect($client)->not->toBeNull()
        ->and($client->type)->toBe(Client::TYPE_COMPANY)
        ->and($client->ice)->toBe('001234567000089')
        ->and($client->first_name)->toBeNull()
        ->and($client->cin)->toBeNull();
});

test('administrators can create an individual client', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Livewire::actingAs($admin)
        ->test(ClientsList::class)
        ->call('openCreateModal')
        ->set('type', Client::TYPE_INDIVIDUAL)
        ->set('first_name', 'Sara')
        ->set('last_name', 'Alami')
        ->set('cin', 'AB123456')
        ->set('city', 'Rabat')
        ->call('saveClient')
        ->assertHasNoErrors();

    $client = Client::query()->where('cin', 'AB123456')->first();

    expect($client)->not->toBeNull()
        ->and($client->type)->toBe(Client::TYPE_INDIVIDUAL)
        ->and($client->displayName())->toBe('Sara Alami')
        ->and($client->company_name)->toBeNull()
        ->and($client->ice)->toBeNull();
});

test('client validation depends on the selected type', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Livewire::actingAs($admin)
        ->test(ClientsList::class)
        ->call('openCreateModal')
        ->set('type', Client::TYPE_COMPANY)
        ->set('company_name', '')
        ->call('saveClient')
        ->assertHasErrors(['company_name']);

    Livewire::actingAs($admin)
        ->test(ClientsList::class)
        ->call('openCreateModal')
        ->set('type', Client::TYPE_INDIVIDUAL)
        ->set('first_name', '')
        ->set('last_name', '')
        ->call('saveClient')
        ->assertHasErrors(['first_name', 'last_name']);
});

test('administrators can update a client', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $client = Client::factory()->create(['company_name' => 'Ancien Nom']);

    Livewire::actingAs($admin)
        ->test(ClientsList::class)
        ->call('openEditModal', $client->id)
        ->set('company_name', 'Nouveau Nom')
        ->set('city', 'Fès')
        ->call('saveClient')
        ->assertHasNoErrors();

    expect($client->fresh()->company_name)->toBe('Nouveau Nom')
        ->and($client->fresh()->city)->toBe('Fès');
});

test('a client linked to an invoice cannot be deleted', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $client = Client::factory()->create();
    Invoice::factory()->create(['client_id' => $client->id]);

    Livewire::actingAs($admin)
        ->test(ClientsList::class)
        ->call('confirmDelete', $client->id)
        ->call('deleteClient')
        ->assertHasErrors('delete');

    expect($client->fresh())->not->toBeNull();
});

test('a client linked to a generated document cannot be deleted', function () {
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
        ->test(ClientsList::class)
        ->call('confirmDelete', $client->id)
        ->call('deleteClient')
        ->assertHasErrors('delete');

    expect($client->fresh())->not->toBeNull();
});

test('an unlinked client can be deleted', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $client = Client::factory()->create();

    Livewire::actingAs($admin)
        ->test(ClientsList::class)
        ->call('confirmDelete', $client->id)
        ->call('deleteClient')
        ->assertHasNoErrors();

    expect($client->fresh())->toBeNull();
});

test('managers with the client permission can create clients', function () {
    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $manager = User::factory()->create(['role_id' => $managerRole->id]);

    Livewire::actingAs($manager)
        ->test(ClientsList::class)
        ->call('openCreateModal')
        ->set('company_name', 'Client Manager')
        ->call('saveClient')
        ->assertHasNoErrors();

    expect(Client::query()->where('company_name', 'Client Manager')->exists())->toBeTrue();
});
