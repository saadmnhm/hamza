<?php

namespace Database\Factories;

use App\Models\Item;
use App\Support\Tva;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'unit' => fake()->randomElement(['unité', 'heure', 'jour', 'forfait']),
            'unit_price' => fake()->randomFloat(2, 50, 5000),
            'tva_rate' => fake()->randomElement(Tva::RATES),
        ];
    }
}
