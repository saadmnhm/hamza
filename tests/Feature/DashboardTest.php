<?php

use App\Livewire\Dashboard;
use App\Models\Config as CompanyConfig;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard displays statistics and shortcuts', function () {
    $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
    $user = User::factory()->create([
        'name' => 'Admin Test',
        'role_id' => $adminRole->id,
    ]);
    User::factory()->unverified()->create();

    CompanyConfig::create([
        'raison_sociale' => 'Société Exemple',
        'ice' => '001234567000089',
        'if_number' => '12345678',
        'cnss' => '1234567',
        'rc' => '123456',
        'patente' => '34567890',
        'tribunal_rc' => 'Casablanca',
    ]);

    $dashboard = Livewire::actingAs($user)->test(Dashboard::class);

    $stats = $dashboard->viewData('stats');

    expect($stats)->toHaveCount(4)
        ->and($stats[0]['value'])->toBe(2)
        ->and($stats[1]['value'])->toBe(1)
        ->and($stats[2]['value'])->toBe(1)
        ->and($stats[3]['value'])->toBe('Configurée');

    $dashboard
        ->assertSee('Tableau de bord')
        ->assertSee('Configuration entreprise')
        ->assertSee('Société Exemple')
        ->assertSee('Admin Test');
});
