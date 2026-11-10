@props([
    'companies' => 0,
    'individuals' => 0,
])

@php
    $companies = (int) $companies;
    $individuals = (int) $individuals;
    $total = $companies + $individuals;
    $divisor = max($total, 1);
    $companyPercent = (int) round(($companies / $divisor) * 100);
    $individualPercent = (int) round(($individuals / $divisor) * 100);
@endphp

<div class="flex h-full flex-col gap-5">
    <div>
        <flux:heading size="lg">Clients</flux:heading>
        <flux:text size="sm">Répartition entreprises / particuliers</flux:text>
    </div>

    <div class="flex-1 space-y-4">
        <div>
            <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                <span class="flex items-center gap-2 text-zinc-600 dark:text-zinc-300">
                    <span class="size-2.5 rounded-full bg-blue-500"></span>
                    Entreprises
                </span>
                <span class="font-medium tabular-nums text-zinc-900 dark:text-white">
                    {{ $companies }}
                    <span class="text-xs font-normal text-zinc-500">({{ $companyPercent }} %)</span>
                </span>
            </div>

            <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10">
                <div class="h-full rounded-full bg-blue-500" style="width: {{ $companyPercent }}%"></div>
            </div>
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                <span class="flex items-center gap-2 text-zinc-600 dark:text-zinc-300">
                    <span class="size-2.5 rounded-full bg-emerald-500"></span>
                    Particuliers
                </span>
                <span class="font-medium tabular-nums text-zinc-900 dark:text-white">
                    {{ $individuals }}
                    <span class="text-xs font-normal text-zinc-500">({{ $individualPercent }} %)</span>
                </span>
            </div>

            <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-white/10">
                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $individualPercent }}%"></div>
            </div>
        </div>
    </div>

    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-white/5">
        <flux:text size="sm">Total clients</flux:text>
        <p class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $total }}</p>
    </div>
</div>
