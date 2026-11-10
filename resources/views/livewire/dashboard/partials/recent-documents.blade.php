@props([
    'documents' => collect(),
])

<div class="flex h-full flex-col gap-5">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="lg">Derniers documents</flux:heading>
            <flux:text size="sm">Les 6 derniers documents archivés</flux:text>
        </div>

        <flux:button variant="ghost" size="sm" :href="route('documents')" wire:navigate icon-trailing="arrow-right">
            Tout voir
        </flux:button>
    </div>

    <ul class="flex-1 divide-y divide-zinc-200 dark:divide-white/10">
        @foreach ($documents as $document)
            <li class="flex items-center gap-3 py-3 first:pt-0 last:pb-0" wire:key="recent-document-{{ $document->id }}">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/5 dark:text-zinc-300">
                    <flux:icon name="document-text" class="size-4" />
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $document->title }}</p>
                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                        {{ $document->client?->displayName() ?? data_get($document->data, 'client.name') ?? '—' }}
                        · {{ $document->created_at?->format('d/m/Y') }}
                    </p>
                </div>

                @if ($document->public_token)
                    <flux:button variant="ghost" size="sm" icon="eye" :href="route('archive.documents.show', $document->public_token)" wire:navigate>
                        Voir
                    </flux:button>
                @endif
            </li>
        @endforeach
    </ul>
</div>
