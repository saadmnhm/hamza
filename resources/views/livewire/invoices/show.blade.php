<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="xl" level="1">{{ $invoice->number }}</flux:heading>
                <flux:badge color="blue" size="sm">Facture</flux:badge>
            </div>
            <flux:subheading class="mt-1">
                Émise le {{ $invoice->invoice_date?->format('d/m/Y') ?? '—' }}
                @if ($invoice->due_date)
                    · Échéance le {{ $invoice->due_date->format('d/m/Y') }}
                @endif
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <flux:button variant="ghost" icon="arrow-left" :href="route('clients.show', $invoice->client)" wire:navigate>Retour au client</flux:button>

            @if (auth()->user()?->hasPermission('invoices_create'))
                <flux:button variant="subtle" icon="pencil-square" :href="route('documents.edit', $invoice)" wire:navigate>Modifier</flux:button>
            @endif

            @if (auth()->user()?->hasPermission('invoices_view'))
                <flux:button variant="primary" icon="printer" wire:click="generateDocument">Générer / Imprimer</flux:button>
            @endif

            @if (auth()->user()?->hasPermission('invoices_delete'))
                <flux:button
                    variant="danger"
                    icon="trash"
                    wire:click="deleteInvoice"
                    wire:confirm="Supprimer définitivement cette facture ?"
                >
                    Supprimer
                </flux:button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" text="{{ session('status') }}" />
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Client -->
        <flux:card class="space-y-3 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:heading size="lg">Client</flux:heading>
                <flux:badge :color="$invoice->client?->isCompany() ? 'blue' : 'zinc'">
                    {{ $invoice->client?->isCompany() ? 'Entreprise' : 'Particulier' }}
                </flux:badge>
            </div>

            @if ($invoice->client)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="sm:col-span-2 font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $invoice->client->displayName() }}
                    </div>

                    @if ($invoice->client->isCompany())
                        <div><span class="text-zinc-500">ICE :</span> {{ $invoice->client->ice ?: '—' }}</div>
                        <div><span class="text-zinc-500">IF :</span> {{ $invoice->client->if_number ?: '—' }}</div>
                        <div><span class="text-zinc-500">RC :</span> {{ $invoice->client->rc ?: '—' }}</div>
                        <div><span class="text-zinc-500">CNSS :</span> {{ $invoice->client->cnss ?: '—' }}</div>
                    @else
                        <div><span class="text-zinc-500">CIN :</span> {{ $invoice->client->cin ?: '—' }}</div>
                    @endif

                    <div class="sm:col-span-2"><span class="text-zinc-500">Adresse :</span> {{ $invoice->client->fullAddress() ?: '—' }}</div>
                    <div><span class="text-zinc-500">Téléphone :</span> {{ $invoice->client->phone ?: '—' }}</div>
                    <div><span class="text-zinc-500">E-mail :</span> {{ $invoice->client->email ?: '—' }}</div>
                </div>
            @else
                <flux:text size="sm">Le client associé n'existe plus.</flux:text>
            @endif
        </flux:card>

        <!-- Totaux -->
        <flux:card class="space-y-3">
            <flux:heading size="lg">Totaux</flux:heading>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-zinc-500">Total HT</span>
                    <span class="font-medium tabular-nums">{{ number_format((float) $invoice->subtotal, 2, ',', ' ') }} DH</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-zinc-500">Total TVA</span>
                    <span class="font-medium tabular-nums">{{ number_format((float) $invoice->tva_total, 2, ',', ' ') }} DH</span>
                </div>
                <flux:separator />
                <div class="flex justify-between text-base">
                    <span class="font-semibold">Total TTC</span>
                    <span class="font-bold tabular-nums">{{ number_format((float) $invoice->total, 2, ',', ' ') }} DH</span>
                </div>
            </div>

            @if ($invoice->payment_terms)
                <div class="pt-2 text-sm">
                    <span class="text-zinc-500">Paiement :</span> {{ $invoice->payment_terms }}
                </div>
            @endif
        </flux:card>
    </div>

    <!-- Lignes -->
    <flux:card class="space-y-4">
        <flux:heading size="lg">Produits & services</flux:heading>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left text-xs uppercase tracking-wide text-zinc-500">
                        <th class="py-2 pe-3">Produit / service</th>
                        <th class="py-2 pe-3">Description</th>
                        <th class="py-2 pe-3 text-end">Qté</th>
                        <th class="py-2 pe-3 text-end">PU HT</th>
                        <th class="py-2 pe-3 text-end">TVA</th>
                        <th class="py-2 pe-3 text-end">TVA (DH)</th>
                        <th class="py-2 pe-3 text-end">Total HT</th>
                        <th class="py-2 text-end">Total TTC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoice->items as $item)
                        <tr wire:key="invoice-show-item-{{ $item->id }}" class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="py-3 pe-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $item->product }}</td>
                            <td class="py-3 pe-3 text-zinc-500 dark:text-zinc-400">{{ $item->description ?: '—' }}</td>
                            <td class="py-3 pe-3 text-end tabular-nums">{{ rtrim(rtrim(number_format((float) $item->quantity, 3, ',', ' '), '0'), ',') }}</td>
                            <td class="py-3 pe-3 text-end tabular-nums">{{ number_format((float) $item->unit_price, 2, ',', ' ') }}</td>
                            <td class="py-3 pe-3 text-end tabular-nums">{{ number_format((float) $item->tva_rate, 0, ',', ' ') }} %</td>
                            <td class="py-3 pe-3 text-end tabular-nums">{{ number_format((float) $item->tva_amount, 2, ',', ' ') }}</td>
                            <td class="py-3 pe-3 text-end tabular-nums">{{ number_format((float) $item->total_ht, 2, ',', ' ') }}</td>
                            <td class="py-3 text-end tabular-nums font-semibold">{{ number_format((float) $item->total_ttc, 2, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-sm text-zinc-500">Aucune ligne sur cette facture.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoice->notes)
            <div class="pt-2 text-sm">
                <span class="text-zinc-500">Notes :</span>
                <p class="mt-1 whitespace-pre-wrap">{{ $invoice->notes }}</p>
            </div>
        @endif
    </flux:card>

    <!-- Same shared partial used by the Documents page and the client detail modal -->
    <flux:card class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="lg">Documents générés</flux:heading>
            <flux:badge color="zinc" size="sm">{{ $invoice->documentHistories->count() }} document(s)</flux:badge>
        </div>

        @include('livewire.documents.partials.history-table', [
            'histories' => $invoice->documentHistories->sortByDesc('created_at'),
            'emptyMessage' => 'Aucun document généré pour cette facture.',
        ])
    </flux:card>
</div>
