<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 year', 'now');
        $year = $date->format('Y');

        return [
            'client_id' => Client::factory(),
            'number' => 'FAC-'.$year.'-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'invoice_date' => $date,
            'due_date' => (clone $date)->modify('+30 days'),
            'payment_terms' => 'Paiement à 30 jours',
            'notes' => null,
            'subtotal' => 0,
            'tva_total' => 0,
            'total' => 0,
        ];
    }
}
