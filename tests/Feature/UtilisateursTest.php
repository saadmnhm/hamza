<?php

use App\Livewire\Utilisateurs\Utilisateurs;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('users without a role cannot access the user management page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users'))
        ->assertForbidden();
});

test('administrators see real users and can filter them by role', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $comptableRole = Role::query()->where('slug', 'comptable')->firstOrFail();

    $admin = User::factory()->create([
        'name' => 'Admin Réel',
        'role_id' => $adminRole->id,
    ]);

    $comptable = User::factory()->create([
        'name' => 'Comptable Réel',
        'role_id' => $comptableRole->id,
    ]);

    $this->actingAs($admin)
        ->get(route('users'))
        ->assertOk()
        ->assertSee('Admin Réel')
        ->assertSee('Comptable Réel');

    Livewire::actingAs($admin)
        ->test(Utilisateurs::class)
        ->set('roleFilter', (string) $comptableRole->id)
        ->assertSee('Comptable Réel')
        ->assertDontSee('Admin Réel');
});

test('administrators can invite a user with a real role', function () {
    Notification::fake();

    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $comptableRole = Role::query()->where('slug', 'comptable')->firstOrFail();

    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Livewire::actingAs($admin)
        ->test(Utilisateurs::class)
        ->call('openCreateModal')
        ->set('first_name', 'Karim')
        ->set('last_name', 'Mansouri')
        ->set('email', 'karim@example.com')
        ->set('role_id', (string) $comptableRole->id)
        ->call('saveUser')
        ->assertHasNoErrors();

    $user = User::query()->where('email', 'karim@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Karim Mansouri')
        ->and($user->role_id)->toBe($comptableRole->id)
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->is_active)->toBeTrue();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('administrators can update a user profile and role', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $user = User::factory()->create(['role_id' => $adminRole->id]);

    Livewire::actingAs($admin)
        ->test(Utilisateurs::class)
        ->call('openEditModal', $user->id)
        ->set('first_name', 'Nouveau')
        ->set('last_name', 'Profil')
        ->set('role_id', (string) $managerRole->id)
        ->call('saveUser')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Nouveau Profil')
        ->and($user->role_id)->toBe($managerRole->id);
});

test('status filters use the real account state', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $pending = User::factory()->unverified()->create(['name' => 'Compte En Attente']);
    $inactive = User::factory()->create([
        'name' => 'Compte Inactif',
        'is_active' => false,
    ]);

    Livewire::actingAs($admin)
        ->test(Utilisateurs::class)
        ->set('statusFilter', 'pending')
        ->assertSee($pending->name)
        ->assertDontSee($inactive->name);

    Livewire::actingAs($admin)
        ->test(Utilisateurs::class)
        ->set('statusFilter', 'inactive')
        ->assertSee($inactive->name)
        ->assertDontSee($pending->name);
});

test('an administrator cannot deactivate their own account', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Livewire::actingAs($admin)
        ->test(Utilisateurs::class)
        ->call('toggleStatus', $admin->id)
        ->assertSee('Vous ne pouvez pas désactiver votre propre compte.');

    expect($admin->refresh()->is_active)->toBeTrue();
});

test('a manager can invite a user but cannot assign a role', function () {
    Notification::fake();

    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $employeRole = Role::query()->where('slug', 'employe')->firstOrFail();
    $manager = User::factory()->create(['role_id' => $managerRole->id]);

    Livewire::actingAs($manager)
        ->test(Utilisateurs::class)
        ->call('openCreateModal')
        ->set('first_name', 'Nadia')
        ->set('last_name', 'Test')
        ->set('email', 'nadia@example.com')
        ->set('role_id', (string) Role::query()->where('slug', 'admin')->value('id'))
        ->call('saveUser')
        ->assertHasNoErrors();

    $user = User::query()->where('email', 'nadia@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role_id)->toBe($employeRole->id);
});

test('a manager cannot change the role of an existing user', function () {
    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $employeRole = Role::query()->where('slug', 'employe')->firstOrFail();
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

    $manager = User::factory()->create(['role_id' => $managerRole->id]);
    $user = User::factory()->create([
        'name' => 'Employé Test',
        'role_id' => $employeRole->id,
    ]);

    Livewire::actingAs($manager)
        ->test(Utilisateurs::class)
        ->call('openEditModal', $user->id)
        ->set('first_name', 'Employé')
        ->set('last_name', 'Modifié')
        ->set('role_id', (string) $adminRole->id)
        ->call('saveUser')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Employé Modifié')
        ->and($user->role_id)->toBe($employeRole->id);
});

test('a manager cannot manage an administrator account', function () {
    $managerRole = Role::query()->where('slug', 'manager')->firstOrFail();
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

    $manager = User::factory()->create(['role_id' => $managerRole->id]);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    Livewire::actingAs($manager)
        ->test(Utilisateurs::class)
        ->call('openEditModal', $admin->id)
        ->assertStatus(403);
});

test('deactivated users cannot authenticate', function () {
    $user = User::factory()->create(['is_active' => false]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});
