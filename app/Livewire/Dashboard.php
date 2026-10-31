<?php

namespace App\Livewire;

use App\Models\Config as CompanyConfig;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Tableau de bord')]
class Dashboard extends Component
{
    public function render(): View
    {
        $totalUsers = User::query()->count();
        $verifiedUsers = User::query()->whereNotNull('email_verified_at')->count();
        $pendingUsers = $totalUsers - $verifiedUsers;
        $verificationRate = $totalUsers > 0 ? (int) round($verifiedUsers / $totalUsers * 100) : 0;
        $company = CompanyConfig::query()->first();

        return view('livewire.dashboard', [
            'stats' => [
                [
                    'label' => 'Utilisateurs',
                    'value' => $totalUsers,
                    'hint' => 'Comptes enregistrés',
                    'icon' => 'users',
                ],
                [
                    'label' => 'Comptes vérifiés',
                    'value' => $verifiedUsers,
                    'hint' => $totalUsers > 0 ? $verificationRate.' % du total' : 'Aucun compte',
                    'icon' => 'check-badge',
                ],
                [
                    'label' => 'En attente',
                    'value' => $pendingUsers,
                    'hint' => 'Vérification e-mail',
                    'icon' => 'clock',
                ],
                [
                    'label' => 'Fiche entreprise',
                    'value' => $company ? 'Configurée' : 'À compléter',
                    'hint' => $company?->raison_sociale ?: 'Aucune information renseignée',
                    'icon' => 'building-office-2',
                ],
            ],
            'recentUsers' => auth()->user()?->hasPermission('users_view')
                ? User::query()->latest()->take(5)->get()
                : collect(),
        ]);
    }
}
