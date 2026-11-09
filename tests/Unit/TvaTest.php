<?php

use App\Support\Tva;

test('the supported tva rates are the moroccan standard rates', function () {
    expect(Tva::RATES)->toBe([2, 6, 10, 14, 20])
        ->and(Tva::options())->toBe([
            2 => '2 %',
            6 => '6 %',
            10 => '10 %',
            14 => '14 %',
            20 => '20 %',
        ]);
});

test('tva amounts are calculated with centime precision', function () {
    expect(Tva::amount(100, 20))->toBe(20.0)
        ->and(Tva::amount(100, 14))->toBe(14.0)
        ->and(Tva::amount(99.99, 20))->toBe(20.0)
        ->and(Tva::amount(33.33, 10))->toBe(3.33);
});

test('invoice line totals combine quantity unit price and tva', function () {
    $totals = Tva::lineTotals(3, 33.33, 20);

    expect($totals['total_ht'])->toBe(99.99)
        ->and($totals['tva_amount'])->toBe(20.0)
        ->and($totals['total_ttc'])->toBe(119.99);
});

test('line totals are summed without floating point drift', function () {
    $totals = Tva::sumLines([
        Tva::lineTotals(2, 100, 20),
        Tva::lineTotals(1, 50, 10),
    ]);

    expect($totals)->toBe([
        'subtotal' => 250.0,
        'tva_total' => 45.0,
        'total' => 295.0,
    ]);
});
