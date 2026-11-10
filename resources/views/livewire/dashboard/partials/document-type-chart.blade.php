@props([
    'segments' => [],
    'total' => 0,
])

@php
    $rawTotal = max((int) $total, 0);
    $divisor = max($rawTotal, 1);
    $radius = 54;
    $circumference = 2 * M_PI * $radius;
    $offset = 0;
@endphp

<div class="flex h-full flex-col gap-5">
    <div>
        <flux:heading size="lg">Documents archivés</flux:heading>
        <flux:text size="sm">Répartition par type de document</flux:text>
    </div>

    <div class="flex flex-1 flex-col items-center justify-center gap-6 sm:flex-row">
        <div class="relative size-40 shrink-0">
            <svg viewBox="0 0 140 140" class="size-40" role="img" aria-label="Répartition des documents par type">
                <circle
                    cx="70"
                    cy="70"
                    r="{{ $radius }}"
                    fill="none"
                    class="stroke-zinc-100 dark:stroke-white/10"
                    stroke-width="18"
                />

                <g transform="rotate(-90 70 70)">
                    @foreach ($segments as $segment)
                        @php
                            $length = ($segment['value'] / $divisor) * $circumference;
                            $dash = $length.' '.($circumference - $length);
                        @endphp

                        @if ($length > 0)
                            <circle
                                cx="70"
                                cy="70"
                                r="{{ $radius }}"
                                fill="none"
                                stroke="{{ $segment['color'] }}"
                                stroke-width="18"
                                stroke-dasharray="{{ $dash }}"
                                stroke-dashoffset="{{ -$offset }}"
                            >
                                <title>{{ $segment['label'] }} : {{ $segment['value'] }}</title>
                            </circle>
                        @endif

                        @php $offset += $length; @endphp
                    @endforeach
                </g>
            </svg>

            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ number_format($rawTotal, 0, ',', ' ') }}</span>
                <span class="text-xs text-zinc-500">documents</span>
            </div>
        </div>

        <ul class="w-full space-y-2">
            @foreach ($segments as $segment)
                <li class="flex items-center justify-between gap-3 text-sm">
                    <span class="flex min-w-0 items-center gap-2">
                        <span class="size-2.5 shrink-0 rounded-full" style="background-color: {{ $segment['color'] }}"></span>
                        <span class="truncate text-zinc-600 dark:text-zinc-300">{{ $segment['label'] }}</span>
                    </span>

                    <span class="font-medium tabular-nums text-zinc-900 dark:text-white">{{ $segment['value'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
