<div class="p-6 md:p-8 space-y-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div>
            <flux:heading size="xl" level="1">Archive</flux:heading>
            <flux:subheading class="mt-1">Consultez tous les clients et documents archivés.</flux:subheading>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-2">
        <flux:button variant="{{ $tab === 'documents' ? 'primary' : 'ghost' }}" size="sm" wire:click="setTab('documents')">Documents</flux:button>
        <flux:button variant="{{ $tab === 'clients' ? 'primary' : 'ghost' }}" size="sm" wire:click="setTab('clients')">Clients</flux:button>
    </div>

    @if ($tab === 'documents')
        <!-- Filters -->
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher un document..." clearable />
            <flux:select wire:model.live="typeFilter" placeholder="Tous les types">
                <flux:select.option value="">Tous les types</flux:select.option>
                @foreach ($documentTypes as $type)
                    <flux:select.option :value="$type->value">{{ $type->title() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input type="date" wire:model.live="dateFrom" placeholder="Du" />
            <flux:input type="date" wire:model.live="dateTo" placeholder="Au" />
        </div>

        <!-- Documents table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left text-xs uppercase tracking-wide text-zinc-500">
                        <th class="py-2 pe-3">Date</th>
                        <th class="py-2 pe-3">Type</th>
                        <th class="py-2 pe-3">Titre</th>
                        <th class="py-2 pe-3">Client</th>
                        <th class="py-2 pe-3">N°</th>
                        <th class="py-2 pe-3 text-end">Total</th>
                        <th class="py-2 pe-3">QR</th>
                        <th class="py-2 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $history)
                        <tr wire:key="archive-doc-{{ $history->id }}" class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="py-2 pe-3 whitespace-nowrap text-zinc-500 dark:text-zinc-400">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2 pe-3">
                                <flux:badge color="zinc" size="sm">
                                    {{ \App\Livewire\Documents\DocumentType::tryFrom($history->document_type)?->title() ?? $history->document_type }}
                                </flux:badge>
                            </td>
                            <td class="py-2 pe-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $history->title }}</td>
                            <td class="py-2 pe-3 text-zinc-500 dark:text-zinc-400">{{ $history->client?->displayName() ?? '—' }}</td>
                            <td class="py-2 pe-3 text-zinc-500 dark:text-zinc-400">{{ $history->invoice?->number ?? data_get($history->data, 'invoice_number') ?? '—' }}</td>
                            <td class="py-2 pe-3 text-end tabular-nums">{{ number_format((float) data_get($history->data, 'total', 0), 2, ',', ' ') }} DH</td>
                            <td class="py-2 pe-3">
                                {!! \App\Support\QrCode::svg(route('archive.documents.show', $history->public_token), 48) !!}
                            </td>
                            <td class="py-2 text-end">
                                <div class="flex justify-end gap-1">
                                    <flux:button variant="ghost" size="sm" icon="printer" :href="route('documents.print', $history)" target="_blank">Imprimer</flux:button>
                                    <flux:button variant="ghost" size="sm" icon="qr-code" :href="route('archive.documents.qr', $history->public_token)" target="_blank">QR</flux:button>
                                    <flux:button variant="ghost" size="sm" icon="eye" :href="route('archive.documents.show', $history->public_token)" wire:navigate>Voir</flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-sm text-zinc-500">Aucun document archivé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($documents->hasPages())
            <div class="flex justify-end gap-2 pt-2">
                <flux:button size="sm" variant="subtle" wire:click="previousPage" :disabled="$documents->onFirstPage()">Précédent</flux:button>
                <flux:text size="sm" class="self-center">Page {{ $documents->currentPage() }} / {{ $documents->lastPage() }}</flux:text>
                <flux:button size="sm" variant="subtle" wire:click="nextPage" :disabled="! $documents->hasMorePages()">Suivant</flux:button>
            </div>
        @endif
    @else
        <!-- Clients -->
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher un client..." clearable />
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left text-xs uppercase tracking-wide text-zinc-500">
                        <th class="py-2 pe-3">Client</th>
                        <th class="py-2 pe-3">Type</th>
                        <th class="py-2 pe-3">Contact</th>
                        <th class="py-2 pe-3">Documents</th>
                        <th class="py-2 pe-3">QR</th>
                        <th class="py-2 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr wire:key="archive-client-{{ $client->id }}" class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="py-2 pe-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $client->displayName() }}</td>
                            <td class="py-2 pe-3 text-zinc-500 dark:text-zinc-400">{{ $client->isCompany() ? 'Entreprise' : 'Particulier' }}</td>
                            <td class="py-2 pe-3 text-zinc-500 dark:text-zinc-400">{{ $client->email ?: ($client->phone ?: '—') }}</td>
                            <td class="py-2 pe-3 text-zinc-500 dark:text-zinc-400">{{ $client->document_histories_count }}</td>
                            <td class="py-2 pe-3">
                                {!! \App\Support\QrCode::svg(route('archive.clients.show', $client->public_token), 48) !!}
                            </td>
                            <td class="py-2 text-end">
                                <div class="flex justify-end gap-1">
                                    <flux:button variant="ghost" size="sm" icon="qr-code" :href="route('archive.clients.qr', $client->public_token)" target="_blank">QR</flux:button>
                                    <flux:button variant="ghost" size="sm" icon="eye" :href="route('archive.clients.show', $client->public_token)" wire:navigate>Voir</flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-sm text-zinc-500">Aucun client archivé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($clients->hasPages())
            <div class="flex justify-end gap-2 pt-2">
                <flux:button size="sm" variant="subtle" wire:click="previousPage" :disabled="$clients->onFirstPage()">Précédent</flux:button>
                <flux:text size="sm" class="self-center">Page {{ $clients->currentPage() }} / {{ $clients->lastPage() }}</flux:text>
                <flux:button size="sm" variant="subtle" wire:click="nextPage" :disabled="! $clients->hasMorePages()">Suivant</flux:button>
            </div>
        @endif
    @endif
</div>
