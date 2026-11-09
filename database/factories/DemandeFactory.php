<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Demande;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Demande>
 */
class DemandeFactory extends Factory
{
    protected $model = Demande::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'notes' => null,
        ];
    }
}
