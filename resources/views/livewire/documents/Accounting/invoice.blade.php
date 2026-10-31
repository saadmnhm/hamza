@props(['config', 'history'])

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Facture') }}</flux:heading>

    <div class="row mb-6">
        <div class="col-md-6">
            <flux:card>
                <flux:heading>Paramètres de l'entreprise</flux:heading>
                <div class="px-4 py-3">
                    @if ($config)
                        <p><strong>Raison sociale :</strong> {{ $config->raison_sociale }}</p>
                        <p><strong>SIREN (ICE) :</strong> {{ $config->ice }}</p>
                        <p><strong>Adresse :</strong> {{ $config->adresse }}, {{ $config->code_postal }} {{ $config->ville }}</p>
                    @endif
                </div>
            </flux:card>
        </div>
    </div>

    <flux:card class="w-full">
        <flux:heading>Générer une facture</flux:heading>
        <div class="px-4 py-5">
            <form wire:submit="generate" class="space-y-6">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <flux:input wire:model="invoice_number" :label="__('N° de facture')" type="text" required autofocus />
                    </div>
                    <div>
                        <flux:input wire:model="invoice_date" :label="__('Date de facture')" type="date" required />
                    </div>
                    <div>
                        <flux:input wire:model="client_name" :label="__('Nom du client')" type="text" required />
                    </div>
                    <div>
                        <flux:input wire:model="client_address" :label="__('Adresse du client')" type="text" required />
                    </div>
                    <div>
                        <flux:input wire:model="client_vat_number" :label="__('N° TVA client')" type="text" />
                    </div>
                    <div class="sm:col-span-2">
                        <flux:textarea wire:model="items_description" :label="__('Description des articles')" rows="3" required />
                    </div>
                    <div>
                        <flux:input wire:model="items_quantity" :label="__('Quantité')" type="number" required min="1" step="1" />
                    </div>
                    <div>
                        <flux:input wire:model="items_unit_price" :label="__('Prix unitaire (DH)')" type="number" required min="0" step="100" />
                    </div>
                    <div>
                        <flux:input wire:model="items_total" :label="__('Total article')" type="number" required min="0" step="100" />
                    </div>
                    <div>
                        <flux:input wire:model="subtotal" :label="__('Sous-total (DH)')" type="number" required min="0" step="100" />
                    </div>
                    <div>
                        <flux:input wire:model="tax_rate" :label="__('Taux de TVA (%)')" type="number" required min="0" max="100" />
                    </div>
                    <div>
                        <flux:input wire:model="tax_amount" :label="__('Montant TVA (DH)')" type="number" required min="0" step="100" />
                    </div>
                    <div>
                        <flux:input wire:model="total" :label="__('Total TTC (DH)')" type="number" required min="0" step="100" />
                    </div>
                    <div>
                        <flux:input wire:model="payment_terms" :label="__('Conditions de paiement')" type="text" required />
                    </div>
                    <div class="sm:col-span-2">
                        <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">{{ __('Générer la facture') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:card>

    @if ($showPreview && $generatedHtml)
    <flux:card class="w-full mt-6">
        <div class="flex items-center justify-between mb-4">
            <flux:heading>Aperçu du document</flux:heading>
            <div class="flex gap-2">
                @if ($historyId)
                <flux:button variant="primary" :href="route('documents.print', $historyId)" target="_blank">
                    <flux:icon name="printer" class="mr-2" /> Imprimer (PDF)
                </flux:button>
                @else
                <flux:button variant="primary" wire:click="printDocument">
                    <flux:icon name="printer" class="mr-2" /> Imprimer
                </flux:button>
                @endif
                <flux:button variant="ghost" wire:click="closePreview">
                    <flux:icon name="x-mark" class="size-4" />
                </flux:button>
            </div>
        </div>
        <div class="border rounded-lg overflow-hidden bg-white p-4">
            <iframe srcdoc="{{ $generatedHtml }}" class="w-full h-[600px] border-0"></iframe>
        </div>
    </flux:card>
    @endif

    @if ($history && $history->count() > 0)
    <flux:card class="w-full mt-6">
        <flux:heading class="mb-4">Historique des documents</flux:heading>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2 px-3">Date</th>
                        <th class="text-left py-2 px-3">Client</th>
                        <th class="text-left py-2 px-3">N° Facture</th>
                        <th class="text-right py-2 px-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($history as $item)
                    <tr class="border-b hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="py-2 px-3">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2 px-3">{{ $item->data['client_name'] ?? 'N/A' }}</td>
                        <td class="py-2 px-3">{{ $item->data['invoice_number'] ?? 'N/A' }}</td>
                        <td class="py-2 px-3 text-right">
                            <div class="flex gap-2 justify-end">
                                <flux:button variant="ghost" size="sm" wire:click="reprintFromHistory({{ $item->id }})">
                                    <flux:icon name="arrow-path" class="size-4" /> Régénérer
                                </flux:button>
                                <flux:button variant="ghost" size="sm" :href="route('documents.print', $item->id)" target="_blank">
                                    <flux:icon name="printer" class="size-4" /> Imprimer
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>
    @endif
</section>

<script>
    // Print functionality is now handled via the print route
</script>