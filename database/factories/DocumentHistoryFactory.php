<?php

namespace Database\Factories;

use App\Models\DocumentHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentHistory>
 */
class DocumentHistoryFactory extends Factory
{
    protected $model = DocumentHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => null,
            'invoice_id' => null,
            'document_type' => 'invoice',
            'title' => fake()->sentence(),
            'data' => [],
            'html' => '<html><body>Document</body></html>',
        ];
    }
}
