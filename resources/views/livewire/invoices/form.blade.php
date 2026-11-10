<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            @php
    $documentLabel = match ($type ?? 'invoice') {
        'delivery_note' => 'Bon de livraison',
        'purchase_order' => 'Bon de commande',
        default => 'Facture',
    };
@endphp
<flux:heading size="xl" level="1">{{ $invoiceId ? 'Modifier le '.$documentLabel : 'Nouveau '.$documentLabel }}</flux:heading>
            <flux:subheading class="mt-1">Renseignez le client et ajoutez autant de lignes que nécessaire.</flux:subheading>
        </div>
        <flux:button variant="ghost" icon="arrow-left" :href="route('clients.index')" wire:navigate>Retour aux clients</flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" text="{{ session('status') }}" />
    @endif

    @if ($clients->isEmpty())
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>Aucun client enregistré</flux:callout.heading>
            <flux:callout.text>
                Créez d'abord une fiche client avant de créer un document.
                <flux:link :href="route('clients.index')" wire:navigate>Gérer les clients</flux:link>
            </flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- Informations générales -->
        <flux:card class="space-y-4">
            <flux:heading size="lg">Informations générales</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <flux:field>
                    <flux:label>N° de document</flux:label>
                    <flux:input wire:model="number" readonly />
                    <flux:description>Généré automatiquement.</flux:description>
                    <flux:error name="number" />
                </flux:field>

                <flux:field>
                    <flux:label required>Date du document</flux:label>
                    <flux:input type="date" wire:model="invoice_date" required />
                    <flux:error name="invoice_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Date d'échéance</flux:label>
                    <flux:input type="date" wire:model="due_date" />
                    <flux:error name="due_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Client</flux:label>
                    <flux:select wire:model.live="client_id" placeholder="Sélectionner un client...">
                        @foreach ($clients as $client)
                            <flux:select.option :value="$client->id">
                                {{ $client->displayName() }} — {{ $client->isCompany() ? 'Entreprise' : 'Particulier' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="client_id" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Conditions de paiement</flux:label>
                    <flux:input wire:model="payment_terms" placeholder="Paiement à 30 jours" />
                    <flux:error name="payment_terms" />
                </flux:field>

                <flux:field>
                    <flux:label>Notes</flux:label>
                    <flux:input wire:model="notes" placeholder="Informations complémentaires..." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </flux:card>

        <!-- Récapitulatif client -->
        @if ($selectedClient)
            <flux:card class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <flux:heading size="lg">{{ $selectedClient->displayName() }}</flux:heading>
                        <flux:subheading>
                            {{ $selectedClient->isCompany() ? 'Personne morale' : 'Personne physique' }}
                        </flux:subheading>
                    </div>
                    <flux:badge :color="$selectedClient->isCompany() ? 'blue' : 'zinc'">
                        {{ $selectedClient->isCompany() ? 'Entreprise' : 'Particulier' }}
                    </flux:badge>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                    @if ($selectedClient->isCompany())
                        <div><span class="text-zinc-500">ICE :</span> {{ $selectedClient->ice ?: '—' }}</div>
                        <div><span class="text-zinc-500">IF :</span> {{ $selectedClient->if_number ?: '—' }}</div>
                        <div><span class="text-zinc-500">RC :</span> {{ $selectedClient->rc ?: '—' }}</div>
                        <div><span class="text-zinc-500">CNSS :</span> {{ $selectedClient->cnss ?: '—' }}</div>
                    @else
                        <div><span class="text-zinc-500">CIN :</span> {{ $selectedClient->cin ?: '—' }}</div>
                    @endif

                    <div class="sm:col-span-2"><span class="text-zinc-500">Adresse :</span> {{ $selectedClient->fullAddress() ?: '—' }}</div>
                    <div><span class="text-zinc-500">Téléphone :</span> {{ $selectedClient->phone ?: '—' }}</div>
                    <div><span class="text-zinc-500">E-mail :</span> {{ $selectedClient->email ?: '—' }}</div>
                </div>
            </flux:card>
        @endif

        <!-- Lignes de facture -->
        <flux:card class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <flux:heading size="lg">Produits & services</flux:heading>
                    <flux:subheading>Sélectionnez un article du catalogue ou saisissez une ligne libre. Les totaux se recalculent automatiquement.</flux:subheading>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <flux:button type="button" variant="subtle" icon="plus" wire:click="addItem">Ajouter une ligne</flux:button>
                </div>
            </div>

            <flux:error name="items" />

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1280px] text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left text-xs uppercase tracking-wide text-zinc-500">
                            <th class="py-2 pe-2 min-w-48">Article</th>
                            <th class="py-2 pe-2 min-w-48">Produit / service</th>
                            <th class="py-2 pe-2 min-w-56">Description</th>
                            <th class="py-2 pe-2 w-24">Qté</th>
                            <th class="py-2 pe-2 w-32">PU HT</th>
                            <th class="py-2 pe-2 w-28">TVA</th>
                            <th class="py-2 pe-2 w-28 text-end">TVA (DH)</th>
                            <th class="py-2 pe-2 w-28 text-end">Total HT</th>
                            <th class="py-2 pe-2 w-28 text-end">Total TTC</th>
                            <th class="py-2 w-12"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $index => $item)
                            <tr wire:key="invoice-item-{{ $item['uid'] }}" class="border-b border-zinc-100 dark:border-zinc-800 align-top">
                                <td class="py-3 pe-2">
                                    <flux:select wire:model.live="items.{{ $index }}.item_id">
                                        <flux:select.option value="">Saisie libre</flux:select.option>
                                        @foreach ($catalogItems as $catalogItem)
                                            <flux:select.option :value="$catalogItem->id">{{ $catalogItem->name }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="items.{{ $index }}.item_id" />
                                </td>
                                <td class="py-3 pe-2">
                                    <flux:input wire:model="items.{{ $index }}.product" list="product-suggestions" placeholder="Ex : Prestation de service" />
                                    <flux:error name="items.{{ $index }}.product" />
                                </td>
                                <td class="py-3 pe-2">
                                    <flux:input wire:model="items.{{ $index }}.description" placeholder="Détail optionnel" />
                                    <flux:error name="items.{{ $index }}.description" />
                                </td>
                                <td class="py-3 pe-2">
                                    <flux:input type="number" wire:model.live="items.{{ $index }}.quantity" step="0.001" min="0.001" />
                                    <flux:error name="items.{{ $index }}.quantity" />
                                </td>
                                <td class="py-3 pe-2">
                                    <flux:input type="number" wire:model.live="items.{{ $index }}.unit_price" step="0.01" min="0" />
                                    <flux:error name="items.{{ $index }}.unit_price" />
                                </td>
                                <td class="py-3 pe-2">
                                    <flux:select wire:model.live="items.{{ $index }}.tva_rate">
                                        @foreach ($tvaRates as $rate => $label)
                                            <flux:select.option :value="$rate">{{ $label }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="items.{{ $index }}.tva_rate" />
                                </td>
                                <td class="py-3 pe-2 text-end tabular-nums text-zinc-600 dark:text-zinc-300">
                                    {{ number_format($lineTotals[$index]['tva_amount'], 2, ',', ' ') }}
                                </td>
                                <td class="py-3 pe-2 text-end tabular-nums text-zinc-600 dark:text-zinc-300">
                                    {{ number_format($lineTotals[$index]['total_ht'], 2, ',', ' ') }}
                                </td>
                                <td class="py-3 pe-2 text-end tabular-nums font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ number_format($lineTotals[$index]['total_ttc'], 2, ',', ' ') }}
                                </td>
                                <td class="py-3 text-end">
                                    <flux:button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        :disabled="count($items) <= 1"
                                        wire:click="removeItem({{ $index }})"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <datalist id="product-suggestions">
                @foreach ($productSuggestions as $product)
                    <option value="{{ $product }}"></option>
                @endforeach
            </datalist>

            <!-- Totaux -->
            <div class="flex justify-end pt-2">
                <div class="w-full sm:w-80 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Total HT</span>
                        <span class="font-medium tabular-nums">{{ number_format($invoiceTotals['subtotal'], 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Total TVA</span>
                        <span class="font-medium tabular-nums">{{ number_format($invoiceTotals['tva_total'], 2, ',', ' ') }} DH</span>
                    </div>
                    <flux:separator />
                    <div class="flex justify-between text-base">
                        <span class="font-semibold">Total TTC</span>
                        <span class="font-bold tabular-nums">{{ number_format($invoiceTotals['total'], 2, ',', ' ') }} DH</span>
                    </div>
                </div>
            </div>
        </flux:card>

        <!-- Actions -->
        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button variant="ghost" :href="route('clients.index')" wire:navigate>Annuler</flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ $invoiceId ? 'Enregistrer les modifications' : 'Enregistrer le document' }}
            </flux:button>
            <flux:button type="button" variant="filled" icon="printer" wire:click="saveAndPrint">
                Enregistrer et imprimer
            </flux:button>
        </div>
    </form>
</div>
