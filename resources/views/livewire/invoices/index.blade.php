<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">Factures</flux:heading>
            <flux:subheading class="mt-1">Créez, suivez et imprimez les factures de vos clients.</flux:subheading>
        </div>
        <div class="flex items-center gap-3">
            @if (auth()->user()?->hasPermission('invoices_create'))
                <flux:button variant="primary" icon="plus" :href="route('clients.index')" wire:navigate>Choisir un client</flux:button>
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
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher un numéro ou un client..." clearable />
        </div>

        <flux:select wire:model.live="clientFilter" class="w-full sm:w-64" placeholder="Tous les clients">
            <flux:select.option value="">Tous les clients</flux:select.option>
            @foreach ($clients as $client)
                <flux:select.option :value="$client->id">{{ $client->displayName() }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <!-- Tableau des factures -->
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Numéro</flux:table.column>
            <flux:table.column>Client</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Échéance</flux:table.column>
            <flux:table.column align="end">Total TTC</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($invoices as $invoice)
                <flux:table.row wire:key="invoice-{{ $invoice->id }}">
                    <flux:table.cell>
                        <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $invoice->number }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invoice->items_count }} ligne(s)</div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->client?->displayName() ?? 'Client supprimé' }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $invoice->client?->isCompany() ? 'Entreprise' : 'Particulier' }}
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $invoice->invoice_date?->format('d/m/Y') ?? '—' }}
                    </flux:table.cell>

                    <flux:table.cell class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $invoice->due_date?->format('d/m/Y') ?? '—' }}
                    </flux:table.cell>

                    <flux:table.cell align="end" class="font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ number_format((float) $invoice->total, 2, ',', ' ') }} DH
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                            <flux:menu>
                                <flux:menu.item icon="eye" :href="route('documents.show', $invoice)" wire:navigate>
                                    Voir la facture
                                </flux:menu.item>

                                @if (auth()->user()?->hasPermission('invoices_create'))
                                    <flux:menu.item icon="pencil-square" :href="route('documents.edit', $invoice)" wire:navigate>
                                        Modifier
                                    </flux:menu.item>
                                @endif

                                @if (auth()->user()?->hasPermission('invoices_view'))
                                    <flux:menu.item icon="printer" wire:click="generateAndPrint({{ $invoice->id }})">
                                        Imprimer
                                    </flux:menu.item>
                                @endif

                                @if (auth()->user()?->hasPermission('invoices_delete'))
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        icon="trash"
                                        variant="danger"
                                        wire:click="deleteInvoice({{ $invoice->id }})"
                                        wire:confirm="Supprimer définitivement cette facture ?"
                                    >
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
                            <flux:icon name="document-text" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            <flux:text size="sm">Aucune facture ne correspond à ces critères.</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-zinc-200 dark:border-zinc-800 pt-4">
        <flux:text size="sm" class="text-zinc-500">
            Affichage de {{ $invoices->firstItem() ?? 0 }} à {{ $invoices->lastItem() ?? 0 }} sur {{ $invoices->total() }} facture(s)
        </flux:text>

        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="subtle" wire:click="previousPage" :disabled="$invoices->onFirstPage()">
                Précédent
            </flux:button>

            <flux:text size="sm">Page {{ $invoices->currentPage() }} / {{ max($invoices->lastPage(), 1) }}</flux:text>

            <flux:button size="sm" variant="subtle" wire:click="nextPage" :disabled="! $invoices->hasMorePages()">
                Suivant
            </flux:button>
        </div>
    </div>
</div>
