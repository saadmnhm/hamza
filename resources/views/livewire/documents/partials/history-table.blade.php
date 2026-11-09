@props([
    'histories',
    'showClient' => false,
    'showInvoice' => false,
    'emptyMessage' => 'Aucun document généré.',
])

@if ($histories->isEmpty())
    <div class="flex flex-col items-center justify-center gap-2 py-8 text-center">
        <flux:icon name="document-text" class="size-8 text-zinc-300 dark:text-zinc-600" />
        <flux:text size="sm">{{ $emptyMessage }}</flux:text>
    </div>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left text-xs uppercase tracking-wide text-zinc-500">
                    <th class="py-2 pe-3">Date</th>
                    <th class="py-2 pe-3">Type</th>
                    <th class="py-2 pe-3">Titre</th>
                    @if ($showClient)
                        <th class="py-2 pe-3">Client</th>
                    @endif
                    @if ($showInvoice)
                        <th class="py-2 pe-3">Facture</th>
                    @endif
                    <th class="py-2 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($histories as $history)
                    <tr wire:key="document-history-{{ $history->id }}" class="border-b border-zinc-100 dark:border-zinc-800">
                        <td class="py-2 pe-3 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                            {{ $history->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-2 pe-3">
                            <flux:badge color="zinc" size="sm">
                                {{ \App\Livewire\Documents\DocumentType::tryFrom($history->document_type)?->title() ?? $history->document_type }}
                            </flux:badge>
                        </td>
                        <td class="py-2 pe-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $history->title }}</td>
                        @if ($showClient)
                            <td class="py-2 pe-3 text-zinc-500 dark:text-zinc-400">
                                {{ $history->client?->displayName() ?? data_get($history->data, 'client.name') ?? data_get($history->data, 'client_name') ?? '—' }}
                            </td>
                        @endif
                        @if ($showInvoice)
                            <td class="py-2 pe-3 text-zinc-500 dark:text-zinc-400">
                                {{ $history->invoice?->number ?? data_get($history->data, 'invoice_number') ?? '—' }}
                            </td>
                        @endif
                        <td class="py-2 text-end">
                            <flux:button variant="ghost" size="sm" icon="printer" :href="route('documents.print', $history)" target="_blank">
                                Imprimer
                            </flux:button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
