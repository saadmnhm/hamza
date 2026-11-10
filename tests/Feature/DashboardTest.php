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

test('dashboard displays statistics, charts and shortcuts', function () {
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

    /** @var array<string, array{label: string, value: int|string, hint: string, icon: string}> $stats */
    $stats = collect($dashboard->viewData('stats'))->keyBy('label')->all();

    expect($stats)->toHaveKeys([
        "Chiffre d'affaires",
        'Factures émises',
        'Clients',
        'Documents archivés',
        'Demandes',
        'Articles',
        'Utilisateurs',
        'Comptes vérifiés',
        'En attente',
        'Fiche entreprise',
    ])
        ->and($stats["Chiffre d'affaires"]['value'])->toBe('0,00 DH')
        ->and($stats['Factures émises']['value'])->toBe(0)
        ->and($stats['Clients']['value'])->toBe(0)
        ->and($stats['Documents archivés']['value'])->toBe(0)
        ->and($stats['Utilisateurs']['value'])->toBe(2)
        ->and($stats['Comptes vérifiés']['value'])->toBe(1)
        ->and($stats['En attente']['value'])->toBe(1)
        ->and($stats['Fiche entreprise']['value'])->toBe('Configurée')
        ->and($stats['Fiche entreprise']['hint'])->toBe('Société Exemple');

    expect($dashboard->viewData('revenueChart'))->toHaveCount(12)
        ->and($dashboard->viewData('documentTypeChart'))->toHaveCount(5)
        ->and($dashboard->viewData('documentTypeTotal'))->toBe(0)
        ->and($dashboard->viewData('clientTypeChart'))->toBe([
            'companies' => 0,
            'individuals' => 0,
        ]);

    $dashboard
        ->assertSee('Tableau de bord')
        ->assertSee("Chiffre d'affaires")
        ->assertSee('Factures TTC sur les 12 derniers mois')
        ->assertSee('Répartition par type de document')
        ->assertSee('Répartition entreprises / particuliers')
        ->assertSee('Configuration entreprise')
        ->assertSee('Société Exemple')
        ->assertSee('Admin Test');
});
