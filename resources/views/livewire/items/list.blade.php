<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">Catalogue des articles</flux:heading>
            <flux:subheading class="mt-1">Gérez les produits et services utilisés dans vos factures.</flux:subheading>
        </div>
        <div class="flex items-center gap-3">
            @if (auth()->user()?->hasPermission('items_create'))
                <flux:button variant="primary" icon="plus" wire:click="openCreateModal">Nouvel article</flux:button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" text="{{ session('status') }}" />
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-circle" text="{{ session('error') }}" />
    @endif

    <!-- Recherche -->
    <div class="w-full sm:w-96">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher un article, une description..." clearable />
    </div>

    <!-- Tableau -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Article</flux:table.column>
            <flux:table.column>Description</flux:table.column>
            <flux:table.column>Unité</flux:table.column>
            <flux:table.column align="end">PU HT</flux:table.column>
            <flux:table.column align="end">TVA</flux:table.column>
            <flux:table.column align="end">Utilisations</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($items as $item)
                <flux:table.row wire:key="item-{{ $item->id }}">
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar :name="$item->name" :initials="mb_substr($item->name, 0, 2)" icon="cube" />
                            <div>
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">Article #{{ $item->id }}</div>
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="max-w-72 truncate text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $item->description ?: '—' }}
                    </flux:table.cell>

                    <flux:table.cell class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $item->unit ?: '—' }}
                    </flux:table.cell>

                    <flux:table.cell align="end" class="tabular-nums text-zinc-900 dark:text-zinc-100">
                        {{ number_format((float) $item->unit_price, 2, ',', ' ') }} DH
                    </flux:table.cell>

                    <flux:table.cell align="end" class="tabular-nums text-zinc-500 dark:text-zinc-400">
                        {{ number_format((float) $item->tva_rate, 0, ',', ' ') }} %
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:badge color="zinc" size="sm">{{ $item->invoice_items_count }} ligne(s)</flux:badge>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                            <flux:menu>
                                @if (auth()->user()?->hasPermission('items_edit'))
                                    <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $item->id }})">
                                        Modifier
                                    </flux:menu.item>
                                @endif

                                @if (auth()->user()?->hasPermission('items_delete'))
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $item->id }})">
                                        Supprimer
                                    </flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                            <flux:icon name="cube" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            <flux:text size="sm">Aucun article ne correspond à ces critères.</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-zinc-200 dark:border-zinc-800 pt-4">
        <flux:text size="sm" class="text-zinc-500">
            Affichage de {{ $items->firstItem() ?? 0 }} à {{ $items->lastItem() ?? 0 }} sur {{ $items->total() }} article(s)
        </flux:text>

        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="subtle" wire:click="previousPage" :disabled="$items->onFirstPage()">
                Précédent
            </flux:button>

            <flux:text size="sm">Page {{ $items->currentPage() }} / {{ max($items->lastPage(), 1) }}</flux:text>

            <flux:button size="sm" variant="subtle" wire:click="nextPage" :disabled="! $items->hasMorePages()">
                Suivant
            </flux:button>
        </div>
    </div>

    <!-- Modal création / modification -->
    <flux:modal name="item-modal" class="md:w-150 space-y-6">
        <div>
            <flux:heading size="lg">{{ $editingItemId ? 'Modifier l’article' : 'Nouvel article' }}</flux:heading>
            <flux:subheading class="mt-1">Ces informations pré-remplissent les lignes de facture.</flux:subheading>
        </div>

        <form wire:submit="saveItem" class="space-y-5">
            <flux:field>
                <flux:label required>Nom de l’article / service</flux:label>
                <flux:input wire:model="name" placeholder="Ex : Prestation de conseil" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Description</flux:label>
                <flux:textarea wire:model="description" rows="3" placeholder="Détail de la prestation ou du produit..." />
                <flux:error name="description" />
            </flux:field>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:field>
                    <flux:label>Unité</flux:label>
                    <flux:input wire:model="unit" placeholder="Unité, heure, jour..." />
                    <flux:error name="unit" />
                </flux:field>

                <flux:field>
                    <flux:label required>Prix unitaire HT</flux:label>
                    <flux:input type="number" wire:model="unit_price" step="0.01" min="0" />
                    <flux:error name="unit_price" />
                </flux:field>

                <flux:field>
                    <flux:label required>Taux de TVA</flux:label>
                    <flux:select wire:model="tva_rate">
                        @foreach ($tvaRates as $rate => $label)
                            <flux:select.option :value="$rate">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tva_rate" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="ghost">Annuler</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check">
                    {{ $editingItemId ? 'Enregistrer les modifications' : 'Créer l’article' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal suppression -->
    <flux:modal name="delete-item-modal" class="md:w-96 space-y-6">
        <div class="text-center space-y-3">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                <flux:icon name="exclamation-triangle" class="size-6" />
            </div>
            <flux:heading size="lg">Supprimer cet article ?</flux:heading>
            <flux:subheading>
                {{ $deletingItem?->name }} sera définitivement supprimé. Un article utilisé dans une facture ne peut pas être supprimé.
            </flux:subheading>
        </div>

        <flux:error name="delete" />

        <div class="flex justify-center gap-3 pt-2">
            <flux:modal.close>
                <flux:button variant="ghost">Annuler</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="deleteItem" icon="trash">Confirmer la suppression</flux:button>
        </div>
    </flux:modal>
</div>
