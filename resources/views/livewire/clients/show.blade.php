<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="xl" level="1">{{ $client->displayName() }}</flux:heading>
                <flux:badge :color="$client->isCompany() ? 'blue' : 'zinc'">
                    {{ $client->isCompany() ? 'Entreprise' : 'Particulier' }}
                </flux:badge>
            </div>
            <flux:subheading class="mt-1">
                {{ $client->isCompany() ? 'Personne morale' : 'Personne physique' }}
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <flux:button variant="ghost" icon="arrow-left" :href="route('clients.index')" wire:navigate>Retour</flux:button>

            <flux:dropdown>
                <flux:button variant="primary" icon="document-plus">
                    Créer un document
                </flux:button>

                <flux:menu>
                    @if (auth()->user()?->hasPermission('invoices_create'))
                        <flux:menu.item icon="document-text" :href="route('clients.documents.create', ['client' => $client->id, 'type' => 'invoice'])" wire:navigate>
                            Facture
                        </flux:menu.item>
                        <flux:menu.item icon="truck" :href="route('clients.documents.create', ['client' => $client->id, 'type' => 'delivery_note'])" wire:navigate>
                            Bon de livraison
                        </flux:menu.item>
                        <flux:menu.item icon="clipboard-document-list" :href="route('clients.documents.create', ['client' => $client->id, 'type' => 'purchase_order'])" wire:navigate>
                            Bon de commande
                        </flux:menu.item>
                    @endif

                    @if (auth()->user()?->hasPermission('documents_view'))
                        <flux:menu.separator />
                        <flux:menu.item icon="users" :href="route('documents.employment_contract', ['client' => $client->id])" wire:navigate>
                            Contrat de travail
                        </flux:menu.item>
                        <flux:menu.item icon="banknotes" :href="route('documents.salary_slip', ['client' => $client->id])" wire:navigate>
                            Fiche de paie
                        </flux:menu.item>
                    @endif
                </flux:menu>
            </flux:dropdown>

            @if (auth()->user()?->hasPermission('clients_delete'))
                <flux:button
                    variant="danger"
                    icon="trash"
                    wire:click="deleteClient"
                    wire:confirm="Supprimer définitivement ce client ?"
                >
                    Supprimer
                </flux:button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" text="{{ session('status') }}" />
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-circle" text="{{ session('error') }}" />
    @endif

    <!-- Informations client -->
    <flux:card class="space-y-4">
        <flux:heading size="lg">Informations client</flux:heading>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            @if ($client->isCompany())
                <div class="sm:col-span-2"><span class="text-zinc-500">Raison sociale :</span> {{ $client->company_name ?: '—' }}</div>
                <div><span class="text-zinc-500">ICE :</span> {{ $client->ice ?: '—' }}</div>
                <div><span class="text-zinc-500">IF :</span> {{ $client->if_number ?: '—' }}</div>
                <div><span class="text-zinc-500">RC :</span> {{ $client->rc ?: '—' }}</div>
                <div><span class="text-zinc-500">CNSS :</span> {{ $client->cnss ?: '—' }}</div>
            @else
                <div><span class="text-zinc-500">Prénom :</span> {{ $client->first_name ?: '—' }}</div>
                <div><span class="text-zinc-500">Nom :</span> {{ $client->last_name ?: '—' }}</div>
                <div><span class="text-zinc-500">CIN :</span> {{ $client->cin ?: '—' }}</div>
            @endif

            <div class="sm:col-span-2"><span class="text-zinc-500">Adresse :</span> {{ $client->fullAddress() ?: '—' }}</div>
            <div><span class="text-zinc-500">Téléphone :</span> {{ $client->phone ?: '—' }}</div>
            <div><span class="text-zinc-500">E-mail :</span> {{ $client->email ?: '—' }}</div>

            @if ($client->notes)
                <div class="sm:col-span-2">
                    <span class="text-zinc-500">Notes :</span>
                    <p class="mt-1 whitespace-pre-wrap">{{ $client->notes }}</p>
                </div>
            @endif
        </div>
    </flux:card>

    <!-- Documents commerciaux -->
    <flux:card class="space-y-4">
        <flux:heading size="lg">Documents commerciaux du client</flux:heading>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left text-xs uppercase tracking-wide text-zinc-500">
                        <th class="py-2 pe-3">N° document</th>
                        <th class="py-2 pe-3">Type</th>
                        <th class="py-2 pe-3">Date</th>
                        <th class="py-2 pe-3 text-end">Total TTC</th>
                        <th class="py-2 pe-3">Paiement</th>
                        <th class="py-2 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($client->invoices as $invoice)
                        <tr wire:key="client-invoice-{{ $invoice->id }}" class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="py-3 pe-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->number }}</td>
                            <td class="py-3 pe-3">
                                <flux:badge color="zinc" size="sm">
                                    {{ \App\Models\Invoice::LABELS[$invoice->type] ?? $invoice->type }}
                                </flux:badge>
                            </td>
                            <td class="py-3 pe-3 text-zinc-500 dark:text-zinc-400">{{ $invoice->invoice_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="py-3 pe-3 text-end tabular-nums">{{ number_format((float) $invoice->total, 2, ',', ' ') }} DH</td>
                            <td class="py-3 pe-3 text-zinc-500 dark:text-zinc-400">{{ $invoice->payment_terms ?: '—' }}</td>
                            <td class="py-3 text-end">
                                <flux:button variant="ghost" size="sm" icon="eye" :href="route('documents.show', $invoice)" wire:navigate>
                                    Voir
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-sm text-zinc-500">Aucun document commercial pour ce client.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    <!-- Demandes -->
    <flux:card class="space-y-4">
        <flux:heading size="lg">Demandes du client</flux:heading>

        @forelse ($client->demandes as $demande)
            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                        Demande du {{ $demande->date?->format('d/m/Y') ?? '—' }}
                    </div>
                    @if ($demande->notes)
                        <div class="text-sm text-zinc-500">{{ $demande->notes }}</div>
                    @endif
                </div>
                <ul class="text-sm text-zinc-600 dark:text-zinc-300 space-y-1">
                    @forelse ($demande->documentHistories as $doc)
                        <li class="flex items-center justify-between gap-2">
                            <span>{{ $doc->title }} — {{ $doc->created_at->format('d/m/Y H:i') }}</span>
                            <flux:button variant="ghost" size="sm" icon="printer" :href="route('documents.print', $doc)" target="_blank">
                                Imprimer
                            </flux:button>
                        </li>
                    @empty
                        <li class="text-zinc-500">Aucun document.</li>
                    @endforelse
                </ul>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-500">Aucune demande enregistrée.</flux:text>
        @endforelse
    </flux:card>

    <!-- Documents -->
    <flux:card class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="lg">Documents du client</flux:heading>
            <flux:badge color="zinc" size="sm">{{ $client->documentHistories->count() }} document(s)</flux:badge>
        </div>

        @include('livewire.documents.partials.history-table', [
            'histories' => $client->documentHistories,
            'showInvoice' => true,
            'emptyMessage' => 'Aucun document généré pour ce client.',
        ])
    </flux:card>
</div>
