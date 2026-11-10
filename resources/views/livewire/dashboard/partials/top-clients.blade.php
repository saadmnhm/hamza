@props([
    'clients' => collect(),
])

@php
    $max = max(
        (float) $clients->max(fn ($client) => (float) $client->getAttribute('invoices_sum_total')),
        1,
    );
@endphp

<div class="flex h-full flex-col gap-5">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="lg">Meilleurs clients</flux:heading>
            <flux:text size="sm">Classés par chiffre d'affaires facturé</flux:text>
        </div>

        <flux:button variant="ghost" size="sm" :href="route('clients.index')" wire:navigate icon-trailing="arrow-right">
            Tous les clients
        </flux:button>
    </div>

    <ul class="flex-1 space-y-4">
        @foreach ($clients as $index => $client)
            @php
                $amount = (float) $client->getAttribute('invoices_sum_total');
                $count = (int) $client->getAttribute('invoices_count');
                $width = max(($amount / $max) * 100, 2);
            @endphp

            <li class="space-y-1.5">
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                            {{ $index + 1 }}
                        </span>

                        <a
                            href="{{ route('clients.show', $client) }}"
                            wire:navigate
                            class="truncate font-medium text-zinc-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                        >
                            {{ $client->displayName() }}
                        </a>
                    </span>

                    <span class="shrink-0 tabular-nums text-zinc-500">{{ number_format($amount, 2, ',', ' ') }} DH</span>
                </div>

                <div class="h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10">
                    <div class="h-full rounded-full bg-blue-500" style="width: {{ $width }}%"></div>
                </div>

                <p class="text-xs text-zinc-500">{{ $count }} facture(s)</p>
            </li>
        @endforeach
    </ul>
</div>
