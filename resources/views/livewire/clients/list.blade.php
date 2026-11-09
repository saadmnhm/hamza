<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">Gestion des Clients</flux:heading>
            <flux:subheading class="mt-1">Gérez les entreprises et les particuliers liés à votre activité.</flux:subheading>
        </div>
        <div class="flex items-center gap-3">
            @if (auth()->user()?->hasPermission('clients_create'))
                <flux:button variant="primary" icon="plus" wire:click="openCreateModal">Nouveau client</flux:button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" text="{{ session('status') }}" />
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-circle" text="{{ session('error') }}" />
    @endif

    <!-- Recherche et filtre -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
        <div class="w-full sm:w-96">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher un nom, ICE, CIN, e-mail..." clearable />
        </div>

        <flux:select wire:model.live="typeFilter" class="w-full sm:w-56" placeholder="Tous les types">
            <flux:select.option value="">Tous les types</flux:select.option>
            <flux:select.option value="company">Entreprise (personne morale)</flux:select.option>
            <flux:select.option value="individual">Particulier (personne physique)</flux:select.option>
        </flux:select>
    </div>

    <!-- Tableau des clients -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Client</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Identifiants</flux:table.column>
            <flux:table.column>Contact</flux:table.column>
            <flux:table.column>Factures</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($clients as $client)
                <flux:table.row wire:key="client-{{ $client->id }}">
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar
                                :name="$client->displayName()"
                                :initials="mb_substr($client->displayName(), 0, 2)"
                                :icon="$client->isCompany() ? 'building-office' : 'user'"
                            />
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $client->displayName() }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $client->fullAddress() ?: 'Adresse non renseignée' }}
                                </div>
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($client->isCompany())
                            <flux:badge color="blue" size="sm">Entreprise</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">Particulier</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="text-xs text-zinc-500 dark:text-zinc-400">
                        @if ($client->isCompany())
                            <div>ICE : {{ $client->ice ?: '—' }}</div>
                            <div>IF : {{ $client->if_number ?: '—' }} · RC : {{ $client->rc ?: '—' }}</div>
                        @else
                            <div>CIN : {{ $client->cin ?: '—' }}</div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="text-xs text-zinc-500 dark:text-zinc-400">
                        <div>{{ $client->phone ?: '—' }}</div>
                        <div>{{ $client->email ?: '—' }}</div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge color="zinc" size="sm">{{ $client->invoices_count }} facture(s)</flux:badge>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                            <flux:menu>
                                <flux:menu.item icon="eye" :href="route('clients.show', $client)" wire:navigate>
                                    Voir la fiche
                                </flux:menu.item>

                                @if (auth()->user()?->hasPermission('clients_edit'))
                                    <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $client->id }})">
                                        Modifier
                                    </flux:menu.item>
                                @endif

                                @if (auth()->user()?->hasPermission('invoices_create'))
                                    <flux:menu.item icon="document-text" :href="route('clients.documents.create', ['client' => $client->id, 'type' => 'invoice'])" wire:navigate>
                                        Créer une facture
                                    </flux:menu.item>
                                @endif

                                @if (auth()->user()?->hasPermission('clients_delete'))
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $client->id }})">
                                        Supprimer
                                    </flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                            <flux:icon name="user-group" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            <flux:text size="sm">Aucun client ne correspond à ces critères.</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-zinc-200 dark:border-zinc-800 pt-4">
        <flux:text size="sm" class="text-zinc-500">
            Affichage de {{ $clients->firstItem() ?? 0 }} à {{ $clients->lastItem() ?? 0 }} sur {{ $clients->total() }} client(s)
        </flux:text>

        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="subtle" wire:click="previousPage" :disabled="$clients->onFirstPage()">
                Précédent
            </flux:button>

            <flux:text size="sm">Page {{ $clients->currentPage() }} / {{ max($clients->lastPage(), 1) }}</flux:text>

            <flux:button size="sm" variant="subtle" wire:click="nextPage" :disabled="! $clients->hasMorePages()">
                Suivant
            </flux:button>
        </div>
    </div>

    <!-- Modal création / modification -->
    <flux:modal name="client-modal" class="md:w-200 space-y-6">
        <div>
            <flux:heading size="lg">{{ $editingClientId ? 'Modifier le client' : 'Nouveau client' }}</flux:heading>
            <flux:subheading class="mt-1">Choisissez le type de client : les champs s'adaptent automatiquement.</flux:subheading>
        </div>

        <form wire:submit="saveClient" class="space-y-5">
            <flux:field>
                <flux:label required>Type de client</flux:label>
                <flux:select wire:model.live="type">
                    <flux:select.option value="company">Entreprise (personne morale)</flux:select.option>
                    <flux:select.option value="individual">Particulier (personne physique)</flux:select.option>
                </flux:select>
                <flux:error name="type" />
            </flux:field>

            @if ($type === 'company')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field class="md:col-span-2">
                        <flux:label required>Raison sociale</flux:label>
                        <flux:input wire:model="company_name" placeholder="Ex : Société Exemple SARL" />
                        <flux:error name="company_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>ICE</flux:label>
                        <flux:input wire:model="ice" placeholder="001234567000089" maxlength="15" />
                        <flux:error name="ice" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Identifiant fiscal (IF)</flux:label>
                        <flux:input wire:model="if_number" placeholder="12345678" />
                        <flux:error name="if_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Registre de commerce (RC)</flux:label>
                        <flux:input wire:model="rc" placeholder="123456" />
                        <flux:error name="rc" />
                    </flux:field>

                    <flux:field>
                        <flux:label>CNSS</flux:label>
                        <flux:input wire:model="cnss" placeholder="1234567" />
                        <flux:error name="cnss" />
                    </flux:field>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

                    <flux:field>
                        <flux:label>CIN</flux:label>
                        <flux:input wire:model="cin" placeholder="AB123456" />
                        <flux:error name="cin" />
                    </flux:field>
                </div>
            @endif

            <flux:separator />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field class="md:col-span-2">
                    <flux:label>Adresse</flux:label>
                    <flux:input wire:model="address" placeholder="Rue, numéro, quartier..." />
                    <flux:error name="address" />
                </flux:field>

                <flux:field>
                    <flux:label>Ville</flux:label>
                    <flux:input wire:model="city" placeholder="Casablanca" />
                    <flux:error name="city" />
                </flux:field>

                <flux:field>
                    <flux:label>Code postal</flux:label>
                    <flux:input wire:model="postal_code" placeholder="20000" />
                    <flux:error name="postal_code" />
                </flux:field>

                <flux:field>
                    <flux:label>Téléphone</flux:label>
                    <flux:input wire:model="phone" icon="phone" placeholder="+212 6 00 00 00 00" />
                    <flux:error name="phone" />
                </flux:field>

                <flux:field>
                    <flux:label>E-mail</flux:label>
                    <flux:input type="email" wire:model="email" icon="envelope" placeholder="contact@exemple.ma" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Notes / informations complémentaires</flux:label>
                    <flux:textarea wire:model="notes" rows="2" placeholder="Informations utiles pour la facturation..." />
                    <flux:error name="notes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Annuler</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check">
                    {{ $editingClientId ? 'Enregistrer les modifications' : 'Créer le client' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal confirmation suppression -->
    <flux:modal name="delete-client-modal" class="md:w-96 space-y-6">
        <div class="text-center space-y-3">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                <flux:icon name="exclamation-triangle" class="size-6" />
            </div>
            <flux:heading size="lg">Supprimer ce client ?</flux:heading>
            <flux:subheading>
                {{ $deletingClient?->displayName() }} sera définitivement supprimé. Un client lié à des factures ne peut pas être supprimé.
            </flux:subheading>
        </div>

        <flux:error name="delete" />

        <div class="flex justify-center gap-3 pt-2">
            <flux:modal.close>
                <flux:button variant="ghost">Annuler</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="deleteClient" icon="trash">Confirmer la suppression</flux:button>
        </div>
    </flux:modal>
</div>
