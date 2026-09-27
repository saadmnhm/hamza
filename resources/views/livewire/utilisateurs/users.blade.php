<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">Gestion des Utilisateurs</flux:heading>
            <flux:subheading class="mt-1">Gérez les accès, les rôles et les autorisations des membres de votre organisation.</flux:subheading>
        </div>
        <div class="flex items-center gap-3">
            <flux:modal.trigger name="add-user-modal">
                <flux:button variant="primary" icon="plus">Ajouter un utilisateur</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
        <div class="w-full sm:w-80">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher nom, email..." clearable />
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
            <flux:select wire:model.live="roleFilter" class="w-40" placeholder="Tous les rôles">
                <flux:select.option value="">Tous les rôles</flux:select.option>
                <flux:select.option value="admin">Administrateur</flux:select.option>
                <flux:select.option value="manager">Manager</flux:select.option>
                <flux:select.option value="comptable">Comptable</flux:select.option>
                <flux:select.option value="employe">Employé</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="statusFilter" class="w-36" placeholder="Tous les statuts">
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
            <flux:table.column sortable>Utilisateur</flux:table.column>
            <flux:table.column>Rôle</flux:table.column>
            <flux:table.column>Statut</flux:table.column>
            <flux:table.column>Dernière Connexion</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            <!-- Utilisateur 1 -->
            <flux:table.row>
                <flux:table.cell>
                    <div class="flex items-center gap-3">
                        <flux:avatar src="https://fluxui.dev/img/demo/user-1.jpg" name="Youssef El Amrani" />
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">Youssef El Amrani</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">youssef@entreprise.ma</div>
                        </div>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge color="purple" size="sm">Administrateur</flux:badge>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge color="emerald" size="sm">
                        Actif
                    </flux:badge>
                </flux:table.cell>
                <flux:table.cell class="text-zinc-500 text-sm">Aujourd'hui, 10:42</flux:table.cell>
                <flux:table.cell align="end">
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                        <flux:menu>
                            <flux:menu.item icon="pencil-square">Modifier le profil</flux:menu.item>
                            <flux:menu.item icon="key">Réinitialiser le mot de passe</flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item icon="trash" variant="danger">Désactiver</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>

            <!-- Utilisateur 2 -->
            <flux:table.row>
                <flux:table.cell>
                    <div class="flex items-center gap-3">
                        <flux:avatar initials="SB" name="Sarah Benali" />
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">Sarah Benali</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">s.benali@entreprise.ma</div>
                        </div>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge color="blue" size="sm">Comptable</flux:badge>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge color="emerald" size="sm">
                        Actif
                    </flux:badge>
                </flux:table.cell>
                <flux:table.cell class="text-zinc-500 text-sm">Hier, 16:15</flux:table.cell>
                <flux:table.cell align="end">
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                        <flux:menu>
                            <flux:menu.item icon="pencil-square">Modifier le profil</flux:menu.item>
                            <flux:menu.item icon="key">Réinitialiser le mot de passe</flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item icon="trash" variant="danger">Désactiver</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>

            <!-- Utilisateur 3 -->
            <flux:table.row>
                <flux:table.cell>
                    <div class="flex items-center gap-3">
                        <flux:avatar initials="KO" name="Karim Ouali" />
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">Karim Ouali</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">k.ouali@entreprise.ma</div>
                        </div>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge color="zinc" size="sm">Employé</flux:badge>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge color="amber" size="sm">
                         En attente
                    </flux:badge>
                </flux:table.cell>
                <flux:table.cell class="text-zinc-500 text-sm">Jamais connecté</flux:table.cell>
                <flux:table.cell align="end">
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                        <flux:menu>
                            <flux:menu.item icon="paper-airplane">Renvoyer l'invitation</flux:menu.item>
                            <flux:menu.item icon="pencil-square">Modifier le profil</flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item icon="trash" variant="danger">Annuler l'invitation</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
        </flux:table.rows>
    </flux:table>

    <!-- Pagination -->
    <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-800 pt-4 text-sm text-zinc-500">
        <div>Affichage de 1 à 3 sur 12 utilisateurs</div>
        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="subtle" disabled>Précédent</flux:button>
            <flux:button size="sm" variant="subtle">Suivant</flux:button>
        </div>
    </div>

    <!-- Modal d'ajout d'utilisateur -->
    <flux:modal name="add-user-modal" class="md:w-128 space-y-6">
        <div>
            <flux:heading size="lg">Ajouter un utilisateur</flux:heading>
            <flux:subheading class="mt-1">Envoyez une invitation par e-mail à un nouveau membre de votre équipe.</flux:subheading>
        </div>

        <form wire:submit="inviteUser" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Prénom</flux:label>
                    <flux:input wire:model="new_user_firstname" placeholder="Karim" />
                    <flux:error name="new_user_firstname" />
                </flux:field>

                <flux:field>
                    <flux:label required>Nom</flux:label>
                    <flux:input wire:model="new_user_lastname" placeholder="Mansouri" />
                    <flux:error name="new_user_lastname" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label required>Adresse E-mail</flux:label>
                <flux:input type="email" wire:model="new_user_email" icon="envelope" placeholder="karim@entreprise.ma" />
                <flux:error name="new_user_email" />
            </flux:field>

            <flux:field>
                <flux:label required>Rôle de l'utilisateur</flux:label>
                <flux:select wire:model="new_user_role" placeholder="Sélectionner un rôle...">
                    <flux:select.option value="admin">Administrateur (Accès total)</flux:select.option>
                    <flux:select.option value="manager">Manager (Gestion des équipes)</flux:select.option>
                    <flux:select.option value="comptable">Comptable (Facturation & Documents)</flux:select.option>
                    <flux:select.option value="employe">Employé (Accès restreint)</flux:select.option>
                </flux:select>
                <flux:error name="new_user_role" />
            </flux:field>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Annuler</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="paper-airplane">Envoyer l'invitation</flux:button>
            </div>
        </form>
    </flux:modal>
</div>