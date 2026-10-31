<?php

use App\Livewire\Roles\RolesList;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('users without permission cannot access role management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('roleslist'))
        ->assertForbidden();
});

test('administrators see the default roles and their real member counts', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    User::factory()->create(['role_id' => $adminRole->id]);

    $this->actingAs($admin)
        ->get(route('roleslist'))
        ->assertOk()
        ->assertSee('Administrateur')
        ->assertSee('Manager Ops')
        ->assertSee('Comptable');

    Livewire::actingAs($admin)
        ->test(RolesList::class)
        ->assertSee('2 membre(s)');
});

test('administrators can create a role with selected permissions', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Livewire::actingAs($admin)
        ->test(RolesList::class)
        ->call('createRole')
        ->set('role_name', 'Responsable RH')
        ->set('role_description', 'Gère les employés et les accès.')
        ->set('permissions.company_view', true)
        ->set('permissions.users_view', true)
        ->call('saveRole')
        ->assertHasNoErrors();

    $role = Role::query()->where('name', 'Responsable RH')->first();

    expect($role)->not->toBeNull()
        ->and($role->slug)->toBe('responsable-rh')
        ->and($role->permissions)->toBe(['company_view', 'users_view'])
        ->and($role->is_system)->toBeFalse();
});

test('the system role always keeps every permission', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Livewire::actingAs($admin)
        ->test(RolesList::class)
        ->call('editRole', $adminRole->id)
        ->set('role_name', 'Administrateur principal')
        ->set('permissions.company_view', false)
        ->call('saveRole')
        ->assertHasNoErrors();

    $adminRole->refresh();

    expect($adminRole->name)->toBe('Administrateur principal')
        ->and($adminRole->slug)->toBe('admin')
        ->and($adminRole->permissions)->toBe(['*'])
        ->and($adminRole->hasPermission('anything'))->toBeTrue();
});

test('a role assigned to users cannot be deleted', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $role = Role::factory()->create(['name' => 'Rôle occupé']);

    User::factory()->create(['role_id' => $role->id]);

    Livewire::actingAs($admin)
        ->test(RolesList::class)
        ->call('confirmDelete', $role->id)
        ->call('deleteRole')
        ->assertHasErrors('delete');

    expect($role->fresh())->not->toBeNull();
});

test('an empty role can be deleted', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $role = Role::factory()->create(['name' => 'Rôle vide']);

    Livewire::actingAs($admin)
        ->test(RolesList::class)
        ->call('confirmDelete', $role->id)
        ->call('deleteRole')
        ->assertHasNoErrors();

    expect($role->fresh())->toBeNull();
});

test('administrators can duplicate a role', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $role = Role::factory()->create([
        'name' => 'Rôle source',
        'permissions' => ['company_view', 'invoices_view'],
    ]);

    Livewire::actingAs($admin)
        ->test(RolesList::class)
        ->call('duplicateRole', $role->id);

    $copy = Role::query()->where('name', 'Rôle source (copie)')->first();

    expect($copy)->not->toBeNull()
        ->and($copy->permissions)->toBe(['company_view', 'invoices_view'])
        ->and($copy->is_system)->toBeFalse();
});

test('permission group shortcuts mark and clear every permission', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Livewire::actingAs($admin)
        ->test(RolesList::class)
        ->call('createRole')
        ->call('selectAllPermissions', 'company')
        ->assertSet('permissions.company_view', true)
        ->assertSet('permissions.company_edit', true)
        ->assertSet('permissions.fiscal_edit', true)
        ->call('clearAllPermissions', 'company')
        ->assertSet('permissions.company_view', false)
        ->assertSet('permissions.company_edit', false)
        ->assertSet('permissions.fiscal_edit', false);
});

test('role permissions are evaluated by the user model', function () {
    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $user = User::factory()->create(['role_id' => $managerRole->id]);

    expect($user->hasPermission('users_view'))->toBeTrue()
        ->and($user->hasPermission('roles_manage'))->toBeFalse();
});
