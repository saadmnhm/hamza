@php
    $shortcuts = collect([
        [
            'title' => 'Configuration entreprise',
            'description' => 'Identité visuelle, coordonnées et identifiants fiscaux.',
            'route' => 'config',
            'icon' => 'building-office-2',
            'tone' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
            'permission' => 'company_view',
        ],
        [
            'title' => 'Gestion des utilisateurs',
            'description' => 'Comptes, accès et statuts de vérification.',
            'route' => 'users',
            'icon' => 'users',
            'tone' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
            'permission' => 'users_view',
        ],
        [
            'title' => 'Rôles & permissions',
            'description' => "Niveaux d'accès et permissions de l'équipe.",
            'route' => 'roleslist',
            'icon' => 'shield-check',
            'tone' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
            'permission' => 'roles_manage',
        ],
        [
            'title' => 'Génération de documents',
            'description' => 'Créez des documents imprimables RH et comptabilité',
            'route' => 'documents',
            'icon' => 'document-text',
            'tone' => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
            'permission' => 'documents_view',
        ],
        [
            'title' => 'Mon profil',
            'description' => 'Nom, e-mail et sécurité de votre compte.',
            'route' => 'profile.edit',
            'icon' => 'user-circle',
            'tone' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            'permission' => null,
        ],
    ])->filter(fn (array $shortcut): bool => $shortcut['permission'] === null || auth()->user()?->hasPermission($shortcut['permission']));
@endphp

<div class="flex flex-col gap-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">Tableau de bord</flux:heading>
            <flux:subheading>
                Bonjour {{ auth()->user()->name }}, voici une vue d'ensemble de votre espace.
            </flux:subheading>
        </div>

        @if (auth()->user()?->hasPermission('company_view'))
            <flux:button variant="primary" icon="building-office-2" :href="route('config')" wire:navigate>
                Configurer l'entreprise
            </flux:button>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <flux:card wire:key="stat-{{ $loop->index }}" class="flex flex-col justify-between gap-4">
                <div class="flex items-start justify-between gap-3">
                    <flux:text>{{ $stat['label'] }}</flux:text>

                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/5 dark:text-zinc-300">
                        <flux:icon :name="$stat['icon']" class="size-5" />
                    </span>
                </div>

                <div>
                    <p class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $stat['value'] }}</p>
                    <flux:text size="sm" class="mt-1">{{ $stat['hint'] }}</flux:text>
                </div>
            </flux:card>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="space-y-4 xl:col-span-2" aria-labelledby="shortcuts-heading">
            <flux:heading id="shortcuts-heading" size="lg" level="2">Accès rapides</flux:heading>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($shortcuts as $shortcut)
                    <a
                        href="{{ route($shortcut['route']) }}"
                        wire:navigate
                        wire:key="shortcut-{{ $shortcut['route'] }}"
                        class="group block rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                    >
                        <flux:card class="h-full transition duration-150 hover:border-zinc-300 hover:shadow-sm dark:hover:border-white/20">
                            <div class="flex items-start gap-4">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg {{ $shortcut['tone'] }}">
                                    <flux:icon :name="$shortcut['icon']" class="size-5" />
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <flux:heading>{{ $shortcut['title'] }}</flux:heading>
                                        <flux:icon name="arrow-right" class="size-4 shrink-0 text-zinc-400 transition group-hover:translate-x-0.5 group-hover:text-zinc-600 dark:group-hover:text-zinc-200" />
                                    </div>

                                    <flux:text class="mt-1">{{ $shortcut['description'] }}</flux:text>
                                </div>
                            </div>
                        </flux:card>
                    </a>
                @endforeach
            </div>
        </section>

        @if (auth()->user()?->hasPermission('users_view'))
            <flux:card class="flex h-full flex-col">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <flux:heading level="2">Derniers utilisateurs</flux:heading>
                        <flux:text size="sm" class="mt-1">Les 5 derniers comptes créés.</flux:text>
                    </div>

                    <flux:button variant="ghost" size="sm" class="shrink-0" :href="route('users')" wire:navigate icon-trailing="arrow-right">
                        Tout voir
                    </flux:button>
                </div>

                <div class="mt-4 flex-1 divide-y divide-zinc-200 dark:divide-white/10">
                    @forelse ($recentUsers as $recentUser)
                        <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0" wire:key="recent-user-{{ $recentUser->id }}">
                            <flux:avatar size="sm" :name="$recentUser->name" :initials="$recentUser->initials()" />

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $recentUser->name }}</p>
                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $recentUser->email }}</p>
                            </div>

                            @if (! $recentUser->is_active)
                                <flux:badge color="zinc" size="sm">Inactif</flux:badge>
                            @elseif ($recentUser->email_verified_at)
                                <flux:badge color="emerald" size="sm">Vérifié</flux:badge>
                            @else
                                <flux:badge color="amber" size="sm">En attente</flux:badge>
                            @endif
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                            <flux:icon name="users" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            <flux:text size="sm">Aucun utilisateur pour le moment.</flux:text>
                        </div>
                    @endforelse
                </div>
            </flux:card>
        @endif
    </div>
</div>
