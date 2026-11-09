<?php

namespace App\Support;

/**
 * Centralised Moroccan TVA calculation.
 *
 * Amounts are calculated in centimes (integer) to avoid floating-point drift,
 * then converted back to a two-decimal float for storage and display.
 */
final class Tva
{
    /**
     * The TVA rates allowed by the application.
     *
     * @var array<int, int>
     */
    public const RATES = [2, 6, 10, 14, 20];

    /**
     * Dropdown options keyed by rate.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::RATES as $rate) {
            $options[$rate] = $rate.' %';
        }

        return $options;
    }

    /**
     * Calculate the TVA amount for a base amount.
     */
    public static function amount(float|int|string $base, float|int|string $rate): float
    {
        return self::lineTotals(1, $base, $rate)['tva_amount'];
    }

    /**
     * Calculate the totals for a single invoice line.
     *
     * Quantities are kept to three decimals and money to two decimals.
     *
     * @return array{total_ht: float, tva_amount: float, total_ttc: float}
     */
    public static function lineTotals(
        float|int|string $quantity,
        float|int|string $unitPrice,
        float|int|string $rate,
    ): array {
        $quantityMilli = (int) round((float) $quantity * 1000);
        $unitPriceCents = (int) round((float) $unitPrice * 100);

        $lineHtCents = (int) round($unitPriceCents * $quantityMilli / 1000);
        $tvaCents = (int) round($lineHtCents * (float) $rate / 100);

        return [
            'total_ht' => (float) ($lineHtCents / 100),
            'tva_amount' => (float) ($tvaCents / 100),
            'total_ttc' => (float) (($lineHtCents + $tvaCents) / 100),
        ];
    }

    /**
     * Sum already-calculated invoice lines.
     *
     * @param  iterable<int, array{total_ht: float|int|string, tva_amount: float|int|string, total_ttc: float|int|string}>  $lines
     * @return array{subtotal: float, tva_total: float, total: float}
     */
    public static function sumLines(iterable $lines): array
    {
        $subtotalCents = 0;
        $tvaCents = 0;
        $totalCents = 0;

        foreach ($lines as $line) {
            $subtotalCents += (int) round((float) $line['total_ht'] * 100);
            $tvaCents += (int) round((float) $line['tva_amount'] * 100);
            $totalCents += (int) round((float) $line['total_ttc'] * 100);
        }

        return [
            'subtotal' => (float) ($subtotalCents / 100),
            'tva_total' => (float) ($tvaCents / 100),
            'total' => (float) ($totalCents / 100),
        ];
    }
}
