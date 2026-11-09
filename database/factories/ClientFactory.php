<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => Client::TYPE_COMPANY,
            'company_name' => fake()->company(),
            'first_name' => null,
            'last_name' => null,
            'ice' => fake()->numerify('##############'),
            'if_number' => fake()->numerify('########'),
            'rc' => fake()->numerify('######'),
            'cnss' => fake()->numerify('#######'),
            'cin' => null,
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'notes' => null,
        ];
    }

    /**
     * Indicate that the client is an individual.
     */
    public function individual(): static
    {
        return $this->state(fn (): array => [
            'type' => Client::TYPE_INDIVIDUAL,
            'company_name' => null,
            'ice' => null,
            'if_number' => null,
            'rc' => null,
            'cnss' => null,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'cin' => fake()->bothify('??######'),
        ]);
    }
}
