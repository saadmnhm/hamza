<div class=" p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">Rôles & Permissions</flux:heading>
            <flux:subheading class="mt-1">Définissez les niveaux d'accès et contrôlez les autorisations attribuées à votre équipe.</flux:subheading>
        </div>
        <div class="flex items-center gap-3">
            <flux:modal.trigger name="role-modal">
                <flux:button variant="primary" icon="plus" wire:click="createRole">Nouveau rôle</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <!-- Barre de recherche -->
    <div class="flex justify-between items-center">
        <div class="w-full sm:w-80">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher un rôle..." clearable />
        </div>
    </div>

    <!-- Tableau des Rôles -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable>Nom du Rôle</flux:table.column>
            <flux:table.column>Description</flux:table.column>
            <flux:table.column>Utilisateurs</flux:table.column>
            <flux:table.column>Permissions</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            <!-- Rôle 1: Administrateur -->
            <flux:table.row>
                <flux:table.cell>
                    <div class="flex items-center gap-2">
                        <flux:icon name="shield-check" class="size-4 text-purple-600 dark:text-purple-400" />
                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">Administrateur</span>
                        <flux:badge color="purple" size="sm" class="ml-1">Système</flux:badge>
                    </div>
                </flux:table.cell>
                <flux:table.cell class="text-zinc-500 text-sm">Accès total et illimité à toutes les fonctionnalités et configurations.</flux:table.cell>
                <flux:table.cell>
                    <div class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon name="users" class="size-4 text-zinc-400" />
                        <span>2 membres</span>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge color="emerald" size="sm">Toutes les permissions (Accès Total)</flux:badge>
                </flux:table.cell>
                <flux:table.cell align="end">
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                        <flux:menu>
                            <flux:modal.trigger name="role-modal">
                                <flux:menu.item icon="pencil-square" wire:click="editRole(1)">Modifier</flux:menu.item>
                            </flux:modal.trigger>
                            <flux:menu.item icon="document-duplicate" wire:click="duplicateRole(1)">Dupliquer</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>

            <!-- Rôle 2: Manager -->
            <flux:table.row>
                <flux:table.cell>
                    <div class="flex items-center gap-2">
                        <flux:icon name="user-group" class="size-4 text-blue-600 dark:text-blue-400" />
                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">Manager Ops</span>
                    </div>
                </flux:table.cell>
                <flux:table.cell class="text-zinc-500 text-sm">Gestion des équipes, validation des factures et suivi d'activité.</flux:table.cell>
                <flux:table.cell>
                    <div class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon name="users" class="size-4 text-zinc-400" />
                        <span>5 membres</span>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge color="zinc" size="sm">14 permissions</flux:badge>
                </flux:table.cell>
                <flux:table.cell align="end">
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                        <flux:menu>
                            <flux:modal.trigger name="role-modal">
                                <flux:menu.item icon="pencil-square" wire:click="editRole(2)">Modifier</flux:menu.item>
                            </flux:modal.trigger>
                            <flux:menu.item icon="document-duplicate" wire:click="duplicateRole(2)">Dupliquer</flux:menu.item>
                            <flux:menu.separator />
                            <flux:modal.trigger name="delete-role-modal">
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete(2)">Supprimer</flux:menu.item>
                            </flux:modal.trigger>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>

            <!-- Rôle 3: Comptable -->
            <flux:table.row>
                <flux:table.cell>
                    <div class="flex items-center gap-2">
                        <flux:icon name="banknotes" class="size-4 text-emerald-600 dark:text-emerald-400" />
                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">Comptable</span>
                    </div>
                </flux:table.cell>
                <flux:table.cell class="text-zinc-500 text-sm">Gestion de la facturation, accès aux identifiants fiscaux (ICE, CNSS, IF).</flux:table.cell>
                <flux:table.cell>
                    <div class="flex items-center gap-1.5 text-sm text-zinc-600 dark:text-zinc-300">
                        <flux:icon name="users" class="size-4 text-zinc-400" />
                        <span>3 membres</span>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge color="zinc" size="sm">8 permissions</flux:badge>
                </flux:table.cell>
                <flux:table.cell align="end">
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                        <flux:menu>
                            <flux:modal.trigger name="role-modal">
                                <flux:menu.item icon="pencil-square" wire:click="editRole(3)">Modifier</flux:menu.item>
                            </flux:modal.trigger>
                            <flux:menu.item icon="document-duplicate" wire:click="duplicateRole(3)">Dupliquer</flux:menu.item>
                            <flux:menu.separator />
                            <flux:modal.trigger name="delete-role-modal">
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete(3)">Supprimer</flux:menu.item>
                            </flux:modal.trigger>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
        </flux:table.rows>
    </flux:table>

    <!-- MODAL CRUD (Création & Édition d'un rôle) -->
    <flux:modal name="role-modal" class="md:w-200 space-y-6">
        <div>
            <flux:heading size="lg">Configuration du Rôle</flux:heading>
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

                <div class="space-y-6">
                    <!-- Module 1: Configuration Entreprise -->
                    <div class="p-4 border border-zinc-200 dark:border-zinc-800 rounded-xl bg-zinc-50/50 dark:bg-zinc-800/30 space-y-3">
                        <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <flux:icon name="building-office" class="size-4 text-zinc-500" />
                                Configuration & Infos Légales
                            </span>
                            <flux:button size="xs" variant="subtle">Tout cocher</flux:button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <flux:checkbox wire:model="permissions.company_view" label="Consulter la fiche entreprise" />
                            <flux:checkbox wire:model="permissions.company_edit" label="Modifier le logo & coordonnées" />
                            <flux:checkbox wire:model="permissions.fiscal_edit" label="Gérer les identifiants fiscaux (ICE, CNSS, IF)" />
                        </div>
                    </div>

                    <!-- Module 2: Utilisateurs & Équipe -->
                    <div class="p-4 border border-zinc-200 dark:border-zinc-800 rounded-xl bg-zinc-50/50 dark:bg-zinc-800/30 space-y-3">
                        <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <flux:icon name="users" class="size-4 text-zinc-500" />
                                Gestion des Utilisateurs
                            </span>
                            <flux:button size="xs" variant="subtle">Tout cocher</flux:button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <flux:checkbox wire:model="permissions.users_view" label="Voir la liste des utilisateurs" />
                            <flux:checkbox wire:model="permissions.users_invite" label="Inviter de nouveaux membres" />
                            <flux:checkbox wire:model="permissions.users_edit" label="Modifier les profils utilisateurs" />
                            <flux:checkbox wire:model="permissions.roles_manage" label="Gérer les rôles et permissions" />
                        </div>
                    </div>

                    <!-- Module 3: Facturation & Compta -->
                    <div class="p-4 border border-zinc-200 dark:border-zinc-800 rounded-xl bg-zinc-50/50 dark:bg-zinc-800/30 space-y-3">
                        <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <flux:icon name="document-text" class="size-4 text-zinc-500" />
                                Facturation & Financement
                            </span>
                            <flux:button size="xs" variant="subtle">Tout cocher</flux:button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <flux:checkbox wire:model="permissions.invoices_view" label="Consulter les factures et devis" />
                            <flux:checkbox wire:model="permissions.invoices_create" label="Créer et émettre des factures" />
                            <flux:checkbox wire:model="permissions.invoices_delete" label="Supprimer/Annuler des documents" />
                            <flux:checkbox wire:model="permissions.reports_export" label="Exporter les états comptables" />
                        </div>
                    </div>
                </div>
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

        <div class="flex justify-center gap-3 pt-2">
            <flux:modal.close>
                <flux:button variant="ghost">Annuler</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="deleteRole" icon="trash">Confirmer la suppression</flux:button>
        </div>
    </flux:modal>
</div>