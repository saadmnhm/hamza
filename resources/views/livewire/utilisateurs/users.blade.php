<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">Gestion des Utilisateurs</flux:heading>
            <flux:subheading class="mt-1">Gérez les accès, les rôles et les autorisations des membres de votre organisation.</flux:subheading>
        </div>
        <div class="flex items-center gap-3">
            @if (auth()->user()?->hasPermission('users_invite'))
                <flux:button variant="primary" icon="plus" wire:click="openCreateModal">Ajouter un utilisateur</flux:button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" text="{{ session('status') }}" />
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-circle" text="{{ session('error') }}" />
    @endif

    <!-- Barre de recherche et filtres -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
        <div class="w-full sm:w-80">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher nom, email..." clearable />
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
            <flux:select wire:model.live="roleFilter" class="w-48" placeholder="Tous les rôles">
                <flux:select.option value="">Tous les rôles</flux:select.option>
                @foreach ($roles as $role)
                    <flux:select.option :value="$role->id">{{ $role->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="statusFilter" class="w-40" placeholder="Tous les statuts">
                <flux:select.option value="">Tous les statuts</flux:select.option>
                <flux:select.option value="active">Actif</flux:select.option>
                <flux:select.option value="pending">En attente</flux:select.option>
                <flux:select.option value="inactive">Inactif</flux:select.option>
            </flux:select>
        </div>
    </div>

    <!-- Tableau des utilisateurs -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Utilisateur</flux:table.column>
            <flux:table.column>Rôle</flux:table.column>
            <flux:table.column>Statut</flux:table.column>
            <flux:table.column>Dernière Connexion</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($users as $user)
                <flux:table.row wire:key="user-{{ $user->id }}">
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar :name="$user->name" :initials="$user->initials()" />
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</div>
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($user->role)
                            <flux:badge :color="$user->role->color()" size="sm">{{ $user->role->name }}</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">Sans rôle</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        @if (! $user->is_active)
                            <flux:badge color="zinc" size="sm">Inactif</flux:badge>
                        @elseif ($user->email_verified_at === null)
                            <flux:badge color="amber" size="sm">En attente</flux:badge>
                        @else
                            <flux:badge color="emerald" size="sm">Actif</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="text-zinc-500 text-sm">
                        {{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Jamais connecté' }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        @if (auth()->user()?->hasPermission('users_edit') || auth()->user()?->hasPermission('users_invite'))
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>
                                    @if ($user->email_verified_at === null)
                                        @if (auth()->user()?->hasPermission('users_invite'))
                                            <flux:menu.item icon="paper-airplane" wire:click="resendInvitation({{ $user->id }})">
                                                Renvoyer l'invitation
                                            </flux:menu.item>
                                        @endif

                                        @if (auth()->user()?->hasPermission('users_edit'))
                                            <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $user->id }})">
                                                Modifier le profil
                                            </flux:menu.item>
                                        @endif

                                        @if (auth()->user()?->hasPermission('users_invite'))
                                            <flux:menu.separator />
                                            <flux:menu.item icon="trash" variant="danger" wire:click="cancelInvitation({{ $user->id }})">
                                                Annuler l'invitation
                                            </flux:menu.item>
                                        @endif
                                    @else
                                        @if (auth()->user()?->hasPermission('users_edit'))
                                            <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $user->id }})">
                                                Modifier le profil
                                            </flux:menu.item>
                                            <flux:menu.item icon="key" wire:click="sendPasswordReset({{ $user->id }})">
                                                Réinitialiser le mot de passe
                                            </flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item
                                                :icon="$user->is_active ? 'x-circle' : 'check-circle'"
                                                :variant="$user->is_active ? 'danger' : null"
                                                wire:click="toggleStatus({{ $user->id }})"
                                            >
                                                {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                                            </flux:menu.item>
                                        @endif
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">
                        <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                            <flux:icon name="users" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            <flux:text size="sm">Aucun utilisateur ne correspond à ces critères.</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-zinc-200 dark:border-zinc-800 pt-4">
        <flux:text size="sm" class="text-zinc-500">
            Affichage de {{ $users->firstItem() ?? 0 }} à {{ $users->lastItem() ?? 0 }} sur {{ $users->total() }} utilisateur(s)
        </flux:text>

        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="subtle" wire:click="previousPage" :disabled="$users->onFirstPage()">
                Précédent
            </flux:button>

            <flux:text size="sm">Page {{ $users->currentPage() }} / {{ max($users->lastPage(), 1) }}</flux:text>

            <flux:button size="sm" variant="subtle" wire:click="nextPage" :disabled="! $users->hasMorePages()">
                Suivant
            </flux:button>
        </div>
    </div>

    <!-- Modal d'ajout / modification d'utilisateur -->
    <flux:modal name="user-modal" class="md:w-128 space-y-6">
        <div>
            <flux:heading size="lg">{{ $editingUserId ? 'Modifier un utilisateur' : 'Ajouter un utilisateur' }}</flux:heading>
            <flux:subheading class="mt-1">
                {{ $editingUserId ? 'Mettez à jour les informations et le rôle de ce membre.' : 'Envoyez une invitation par e-mail à un nouveau membre de votre équipe.' }}
            </flux:subheading>
        </div>

        <form wire:submit="saveUser" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Prénom</flux:label>
                    <flux:input wire:model="first_name" placeholder="Karim" />
                    <flux:error name="first_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>Nom</flux:label>
                    <flux:input wire:model="last_name" placeholder="Mansouri" />
                    <flux:error name="last_name" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label required>Adresse E-mail</flux:label>
                <flux:input type="email" wire:model="email" icon="envelope" placeholder="karim@entreprise.ma" />
                <flux:error name="email" />
            </flux:field>

            @if (auth()->user()?->hasPermission('roles_manage'))
                <flux:field>
                    <flux:label required>Rôle de l'utilisateur</flux:label>
                    <flux:select wire:model="role_id" placeholder="Sélectionner un rôle...">
                        @foreach ($roles as $role)
                            <flux:select.option :value="$role->id">{{ $role->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="role_id" />
                </flux:field>
            @else
                @php
                    $selectedRole = $role_id !== '' ? $roles->firstWhere('id', (int) $role_id) : null;
                    $fallbackRole = $roles->firstWhere('slug', 'employe');
                @endphp

                <flux:field>
                    <flux:label>Rôle de l'utilisateur</flux:label>
                    <flux:input
                        :value="$editingUserId ? ($selectedRole?->name ?? 'Sans rôle') : ($fallbackRole?->name ?? 'Sans rôle')"
                        disabled
                    />
                    <flux:description>
                        {{ $editingUserId ? 'Seuls les gestionnaires de rôles peuvent modifier cette valeur.' : 'Le nouveau membre recevra ce rôle par défaut.' }}
                    </flux:description>
                </flux:field>
            @endif

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Annuler</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check">
                    {{ $editingUserId ? 'Enregistrer les modifications' : "Envoyer l'invitation" }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
