<?php

use App\Livewire\QrGroups\QrGroups;
use App\Models\Client;
use App\Models\DocumentHistory;
use App\Models\QrGroup;
use App\Models\QrGroupItem;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

function qrAdmin(): User
{
    return User::factory()->create([
        'role_id' => Role::query()->where('slug', 'admin')->value('id'),
    ]);
}

function qrDocument(User $user, Client $client): DocumentHistory
{
    return DocumentHistory::factory()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'document_type' => 'invoice',
        'title' => 'Facture QR de test',
        'data' => [
            'invoice_number' => 'FAC-QR-001',
            'total' => 1200,
            'client' => ['name' => $client->displayName()],
        ],
    ]);
}

function qrGroupWithItems(User $user, Client $client, DocumentHistory $document): QrGroup
{
    $group = QrGroup::query()->create([
        'user_id' => $user->id,
        'name' => 'Lot imprimable',
        'description' => 'Groupe de test',
    ]);

    $group->items()->create([
        'item_type' => QrGroupItem::TYPE_DOCUMENT,
        'item_id' => $document->id,
        'public_token' => $document->public_token,
        'label' => $document->title,
        'meta' => ['document_type' => $document->document_type, 'number' => 'FAC-QR-001'],
    ]);

    $group->items()->create([
        'item_type' => QrGroupItem::TYPE_CLIENT,
        'item_id' => $client->id,
        'public_token' => $client->public_token,
        'label' => $client->displayName(),
        'meta' => ['client_type' => $client->type, 'type_label' => 'Entreprise'],
    ]);

    return $group;
}

it('renders the QR groups page for an administrator', function () {
    $user = qrAdmin();
    $client = Client::factory()->create();
    qrDocument($user, $client);

    $this->actingAs($user)
        ->get(route('qr-groups.index'))
        ->assertOk()
        ->assertSee('QR Codes');
});

it('creates a group from selected documents and clients', function () {
    $user = qrAdmin();
    $client = Client::factory()->create(['company_name' => 'Atlas QR SARL']);
    $document = qrDocument($user, $client);

    $component = Livewire::actingAs($user)
        ->test(QrGroups::class)
        ->set('name', 'Lot de test')
        ->set('description', 'Groupe de test')
        ->set('selected', ['document:'.$document->id, 'client:'.$client->id])
        ->call('createGroup');

    $group = QrGroup::query()->first();

    expect($group)->not->toBeNull()
        ->and($group?->name)->toBe('Lot de test')
        ->and($group?->items)->toHaveCount(2)
        ->and($group?->items->where('item_type', QrGroupItem::TYPE_DOCUMENT)->first()?->public_token)->toBe($document->public_token)
        ->and($group?->items->where('item_type', QrGroupItem::TYPE_CLIENT)->first()?->public_token)->toBe($client->public_token);

    $component->assertRedirect(route('qr-groups.show', $group?->public_token));
});

it('selects all filtered results for a group', function () {
    $user = qrAdmin();
    $client = Client::factory()->create();

    DocumentHistory::factory()->count(3)->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'document_type' => 'invoice',
    ]);

    $component = Livewire::actingAs($user)
        ->test(QrGroups::class)
        ->call('selectAllResults');

    expect($component->get('selected'))->toHaveCount(3);
});

it('shows and prints every QR code of a group', function () {
    $user = qrAdmin();
    $client = Client::factory()->create();
    $document = qrDocument($user, $client);
    $group = qrGroupWithItems($user, $client, $document);

    $this->actingAs($user)
        ->get(route('qr-groups.show', $group->public_token))
        ->assertOk()
        ->assertSee('Lot imprimable')
        ->assertSee($document->title);

    $this->actingAs($user)
        ->get(route('qr-groups.print', $group->public_token))
        ->assertOk()
        ->assertSee('Lot imprimable')
        ->assertSee($document->title)
        ->assertSee($client->displayName())
        ->assertSee('<svg', false);
});

it('prints an individual client or document QR label', function () {
    $user = qrAdmin();
    $client = Client::factory()->create();
    $document = qrDocument($user, $client);

    $this->actingAs($user)
        ->get(route('archive.documents.qr', $document->public_token))
        ->assertOk()
        ->assertSee($document->title)
        ->assertSee('<svg', false);

    $this->actingAs($user)
        ->get(route('archive.clients.qr', $client->public_token))
        ->assertOk()
        ->assertSee($client->displayName())
        ->assertSee('<svg', false);
});

it('forbids QR group access to users without permissions', function () {
    $user = User::factory()->create(['role_id' => null]);

    $this->actingAs($user)
        ->get(route('qr-groups.index'))
        ->assertForbidden();
});

it('deletes a QR group', function () {
    $user = qrAdmin();
    $group = QrGroup::query()->create([
        'user_id' => $user->id,
        'name' => 'À supprimer',
    ]);

    Livewire::actingAs($user)
        ->test(QrGroups::class)
        ->call('deleteGroup', $group->id)
        ->assertHasNoErrors();

    expect(QrGroup::query()->find($group->id))->toBeNull();
});
