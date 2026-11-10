@props([
    'data' => [],
    'total' => 0,
])

@php
    $values = collect($data)->pluck('value');
    $max = max((float) ($values->max() ?? 0), 1);
    $count = max($values->count(), 1);

    $chartLeft = 46;
    $chartRight = 620;
    $chartTop = 16;
    $chartBottom = 190;
    $chartWidth = $chartRight - $chartLeft;
    $slotWidth = $chartWidth / $count;
    $barWidth = max($slotWidth - 10, 6);
    $gridSteps = 4;
@endphp

<div class="flex h-full flex-col gap-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="lg">Chiffre d'affaires</flux:heading>
            <flux:text size="sm">Factures TTC sur les 12 derniers mois</flux:text>
        </div>

        <div class="sm:text-end">
            <p class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                {{ number_format((float) $total, 2, ',', ' ') }}
                <span class="text-sm font-normal text-zinc-500">DH</span>
            </p>
            <flux:text size="sm">Total sur la période</flux:text>
        </div>
    </div>

    <div class="min-h-56 flex-1">
        <svg viewBox="0 0 640 224" class="h-56 w-full" role="img" aria-label="Chiffre d'affaires des 12 derniers mois">
            @for ($step = 0; $step <= $gridSteps; $step++)
                @php
                    $y = $chartTop + (($chartBottom - $chartTop) / $gridSteps) * $step;
                    $gridValue = $max * (1 - $step / $gridSteps);
                @endphp

                <line
                    x1="{{ $chartLeft }}"
                    x2="{{ $chartRight }}"
                    y1="{{ $y }}"
                    y2="{{ $y }}"
                    class="stroke-zinc-200 dark:stroke-white/10"
                    stroke-width="1"
                    stroke-dasharray="{{ $step === $gridSteps ? '0' : '4 4' }}"
                />

                <text
                    x="{{ $chartLeft - 8 }}"
                    y="{{ $y + 4 }}"
                    text-anchor="end"
                    class="fill-zinc-400 text-[10px]"
                >
                    {{ $gridValue >= 1000 ? number_format($gridValue / 1000, 0, ',', ' ').'k' : number_format($gridValue, 0, ',', ' ') }}
                </text>
            @endfor

            @foreach ($data as $index => $point)
                @php
                    $height = ($point['value'] / $max) * ($chartBottom - $chartTop);
                    $height = max($height, $point['value'] > 0 ? 4 : 0);
                    $x = $chartLeft + ($index * $slotWidth) + (($slotWidth - $barWidth) / 2);
                    $y = $chartBottom - $height;
                @endphp

                <rect
                    x="{{ $x }}"
                    y="{{ $y }}"
                    width="{{ $barWidth }}"
                    height="{{ $height }}"
                    rx="4"
                    class="fill-blue-500 transition-opacity hover:opacity-80 dark:fill-blue-400"
                >
                    <title>{{ $point['full_label'] }} : {{ number_format((float) $point['value'], 2, ',', ' ') }} DH</title>
                </rect>

                <text
                    x="{{ $x + ($barWidth / 2) }}"
                    y="{{ $chartBottom + 20 }}"
                    text-anchor="middle"
                    class="fill-zinc-500 text-[10px]"
                >
                    {{ $point['label'] }}
                </text>
            @endforeach
        </svg>
    </div>
</div>
