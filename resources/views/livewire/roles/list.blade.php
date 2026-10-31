<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">Rôles & Permissions</flux:heading>
            <flux:subheading class="mt-1">Définissez les niveaux d'accès et contrôlez les autorisations attribuées à votre équipe.</flux:subheading>
        </div>
        <div class="flex items-center gap-3">
            <flux:button variant="primary" icon="plus" wire:click="createRole">Nouveau rôle</flux:button>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" text="{{ session('status') }}" />
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-circle" text="{{ session('error') }}" />
    @endif

    <!-- Barre de recherche -->
    <div class="flex justify-between items-center">
        <div class="w-full sm:w-80">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher un rôle..." clearable />
        </div>
    </div>

    <!-- Tableau des Rôles -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nom du Rôle</flux:table.column>
            <flux:table.column>Description</flux:table.column>
            <flux:table.column>Utilisateurs</flux:table.column>
            <flux:table.column>Permissions</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($roles as $role)
                <flux:table.row wire:key="role-{{ $role->id }}">
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:icon :name="$role->icon()" class="size-4 text-zinc-500 dark:text-zinc-400" />
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $role->name }}</span>
                            @if ($role->is_system)
                                <flux:badge color="purple" size="sm" class="ml-1">Système</flux:badge>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="text-zinc-500 text-sm">
                        {{ $role->description ?: 'Aucune description' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300">
                            <flux:icon name="users" class="size-4 text-zinc-400" />
                            <span>{{ $role->users_count }} membre(s)</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($role->is_system)
                            <flux:badge color="emerald" size="sm">Toutes les permissions (Accès Total)</flux:badge>
                        @elseif ($role->permissionCount() > 0)
                            <flux:badge color="zinc" size="sm">{{ $role->permissionCount() }} permission(s)</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">Aucune permission</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                            <flux:menu>
                                <flux:menu.item icon="pencil-square" wire:click="editRole({{ $role->id }})">
                                    Modifier
                                </flux:menu.item>
                                <flux:menu.item icon="document-duplicate" wire:click="duplicateRole({{ $role->id }})">
                                    Dupliquer
                                </flux:menu.item>

                                @unless ($role->is_system)
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $role->id }})">
                                        Supprimer
                                    </flux:menu.item>
                                @endunless
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">
                        <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                            <flux:icon name="shield-check" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            <flux:text size="sm">Aucun rôle ne correspond à cette recherche.</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- MODAL CRUD (Création & Édition d'un rôle) -->
    <flux:modal name="role-modal" class="md:w-200 space-y-6">
        <div>
            <flux:heading size="lg">{{ $editingRoleId ? 'Modifier le rôle' : 'Créer un rôle' }}</flux:heading>
            <flux:subheading class="mt-1">Nommez le rôle et cochez les permissions accordées à ce profil.</flux:subheading>
        </div>

        <form wire:submit="saveRole" class="space-y-6">
            <!-- Informations générales -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field class="md:col-span-2">
                    <flux:label required>Nom du Rôle</flux:label>
                    <flux:input wire:model="role_name" placeholder="Ex: Responsable RH" />
                    <flux:error name="role_name" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="role_description" rows="2" placeholder="Décrivez succinctement les responsabilités de ce rôle..." />
                    <flux:error name="role_description" />
                </flux:field>
            </div>

            <flux:separator />

            <!-- Matrice des Permissions -->
            <div class="space-y-4">
                <flux:heading level="3" size="base">Permissions d'accès</flux:heading>

                @if ($editingRoleId && $roles->firstWhere('id', $editingRoleId)?->is_system)
                    <flux:callout variant="secondary" icon="lock-closed" text="Le rôle Administrateur est un rôle système : il conserve automatiquement toutes les permissions." />
                @endif

                <div class="space-y-6">
                    @foreach ($permissionGroups as $groupKey => $group)
                        <div wire:key="permission-group-{{ $groupKey }}" class="p-4 border border-zinc-200 dark:border-zinc-800 rounded-xl bg-zinc-50/50 dark:bg-zinc-800/30 space-y-3">
                            <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100 flex flex-wrap items-center justify-between gap-2">
                                <span class="flex items-center gap-2">
                                    <flux:icon :name="$group['icon']" class="size-4 text-zinc-500" />
                                    {{ $group['label'] }}
                                </span>

                                <span class="flex items-center gap-1">
                                    <flux:button size="xs" variant="subtle" type="button" wire:click="selectAllPermissions('{{ $groupKey }}')">
                                        Tout cocher
                                    </flux:button>
                                    <flux:button size="xs" variant="subtle" type="button" wire:click="clearAllPermissions('{{ $groupKey }}')">
                                        Tout décocher
                                    </flux:button>
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                @foreach ($group['permissions'] as $permissionKey => $permissionLabel)
                                    <flux:checkbox
                                        wire:model="permissions.{{ $permissionKey }}"
                                        label="{{ $permissionLabel }}"
                                        wire:key="permission-{{ $permissionKey }}"
                                    />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <flux:error name="permissions" />
            </div>

            <!-- Actions Pied de page -->
            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Annuler</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check">Enregistrer le rôle</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- MODAL CONFIRMATION SUPPRESSION -->
    <flux:modal name="delete-role-modal" class="md:w-96 space-y-6">
        <div class="text-center space-y-3">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                <flux:icon name="exclamation-triangle" class="size-6" />
            </div>
            <flux:heading size="lg">Supprimer ce rôle ?</flux:heading>
            <flux:subheading>Cette action est irréversible. Si des utilisateurs sont associés à ce rôle, vous devrez d'abord leur réattribuer un autre rôle.</flux:subheading>
        </div>

        <flux:error name="delete" />

        <div class="flex justify-center gap-3 pt-2">
            <flux:modal.close>
                <flux:button variant="ghost">Annuler</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="deleteRole" icon="trash">Confirmer la suppression</flux:button>
        </div>
    </flux:modal>
</div>
