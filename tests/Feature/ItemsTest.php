<?php

use App\Livewire\Items\ItemsList;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('administrators can create a catalogue item', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Livewire::actingAs($admin)
        ->test(ItemsList::class)
        ->set('name', 'Prestation de conseil')
        ->set('description', 'Accompagnement stratégique')
        ->set('unit', 'heure')
        ->set('unit_price', '250.50')
        ->set('tva_rate', '20')
        ->call('saveItem')
        ->assertHasNoErrors();

    $item = Item::query()->where('name', 'Prestation de conseil')->first();

    expect($item)->not->toBeNull()
        ->and((float) $item->unit_price)->toBe(250.50)
        ->and((float) $item->tva_rate)->toBe(20.0)
        ->and($item->unit)->toBe('heure');
});

test('administrators can update a catalogue item', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $item = Item::factory()->create(['name' => 'Ancien nom', 'tva_rate' => 20]);

    Livewire::actingAs($admin)
        ->test(ItemsList::class)
        ->call('openEditModal', $item->id)
        ->set('name', 'Nouveau nom')
        ->set('unit_price', '999.99')
        ->set('tva_rate', '14')
        ->call('saveItem')
        ->assertHasNoErrors();

    $item->refresh();

    expect($item->name)->toBe('Nouveau nom')
        ->and((float) $item->unit_price)->toBe(999.99)
        ->and((float) $item->tva_rate)->toBe(14.0);
});

test('items used on an invoice cannot be deleted', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $item = Item::factory()->create();
    $invoice = Invoice::factory()->create();

    $invoice->items()->create([
        'item_id' => $item->id,
        'position' => 0,
        'product' => $item->name,
        'quantity' => 1,
        'unit_price' => 100,
        'tva_rate' => 20,
    ]);

    Livewire::actingAs($admin)
        ->test(ItemsList::class)
        ->call('confirmDelete', $item->id)
        ->call('deleteItem')
        ->assertHasErrors(['delete']);

    expect($item->fresh())->not->toBeNull();
});

test('unused items can be deleted', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $item = Item::factory()->create();

    Livewire::actingAs($admin)
        ->test(ItemsList::class)
        ->call('confirmDelete', $item->id)
        ->call('deleteItem')
        ->assertHasNoErrors();

    expect($item->fresh())->toBeNull();
});

test('the item catalogue can be searched by name', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Item::factory()->create(['name' => 'Alpha Audit']);
    Item::factory()->create(['name' => 'Beta Formation']);

    Livewire::actingAs($admin)
        ->test(ItemsList::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha Audit')
        ->assertDontSee('Beta Formation');
});
