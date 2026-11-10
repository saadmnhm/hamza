<div class="space-y-8">
    <!-- Document Generation Rubrique -->
    <div class="space-y-4">
        <flux:card class="space-y-4">
            <div>
                <flux:heading size="xl" level="2">Génération de documents</flux:heading>
                <flux:subheading>Créez des documents imprimables professionnels pour vos besoins RH et comptabilité</flux:subheading>
            </div>

            <div>
                <flux:heading size="sm">Documents RH</flux:heading>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 px-0 py-3">
                    @foreach ($generationTypes as $type)
                        <a
                            href="{{ route('documents.' . $type->value) }}"
                            wire:navigate
                            class="group block rounded-xl border-2 border-transparent hover:border-primary transition-colors duration-150 flex flex-col items-center justify-center"
                        >
                            <flux:card class="h-full w-full p-6 text-center transition-all duration-150 group-hover:bg-primary/5 group-hover:text-primary">
                                <div class="size-12 mx-auto mb-3 text-primary">
                                    <flux:icon :name="$type->icon()" class="size-6" />
                                </div>

                                <flux:heading class="text-sm font-medium mb-1">{{ $type->title() }}</flux:heading>
                                <flux:text size="sm" class="text-zinc-500 line-clamp-2">{{ $type->description() }}</flux:text>
                            </flux:card>
                        </a>
                    @endforeach
                </div>
            </div>

            @if (auth()->user()?->hasPermission('invoices_create'))
                <flux:separator />

                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm">Documents commerciaux</flux:heading>
                        <flux:subheading>Facture, bon de livraison ou bon de commande : choisissez le client puis le type de document.</flux:subheading>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
                        <flux:select wire:model="commercialClientId" label="Client" placeholder="Sélectionner un client">
                            @foreach ($clients as $client)
                                <flux:select.option :value="$client->id">{{ $client->displayName() }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model="commercialType" label="Type de document">
                            @foreach ($commercialTypes as $type)
                                <flux:select.option :value="$type->value">{{ $type->title() }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:button variant="primary" icon="document-plus" wire:click="createCommercial">
                            Créer le document
                        </flux:button>
                    </div>

                    @error('commercialClientId')
                        <flux:text size="sm" class="text-red-500">{{ $message }}</flux:text>
                    @enderror

                    @error('commercialType')
                        <flux:text size="sm" class="text-red-500">{{ $message }}</flux:text>
                    @enderror

                    @if ($clients->isEmpty())
                        <flux:text size="sm" class="text-zinc-500">
                            Aucun client disponible. <flux:link :href="route('clients.index')" wire:navigate>Créez d'abord un client</flux:link>.
                        </flux:text>
                    @endif
                </div>
            @endif
        </flux:card>
    </div>

    <!-- Shared document history: same partial used by the client and invoice interfaces -->
    <div>
        <flux:card class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg">Documents générés</flux:heading>
                    <flux:subheading>Tous les documents archivés, partagés entre les interfaces clients et factures.</flux:subheading>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher un document, un client, un numéro..." clearable />

                    <flux:select wire:model.live="typeFilter" placeholder="Tous les types">
                        <flux:select.option value="">Tous les types</flux:select.option>
                        @foreach ($documentTypes as $type)
                            <flux:select.option :value="$type->value">{{ $type->title() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            @include('livewire.documents.partials.history-table', [
                'histories' => $histories,
                'showClient' => true,
                'showInvoice' => true,
                'emptyMessage' => 'Aucun document généré pour le moment.',
            ])

            @if ($histories->hasPages())
                <div class="flex justify-end gap-2 pt-2">
                    <flux:button size="sm" variant="subtle" wire:click="previousPage" :disabled="$histories->onFirstPage()">
                        Précédent
                    </flux:button>

                    <flux:text size="sm" class="self-center">
                        Page {{ $histories->currentPage() }} / {{ $histories->lastPage() }}
                    </flux:text>

                    <flux:button size="sm" variant="subtle" wire:click="nextPage" :disabled="! $histories->hasMorePages()">
                        Suivant
                    </flux:button>
                </div>
            @endif
        </flux:card>
    </div>
</div>
