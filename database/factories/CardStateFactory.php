<?php

namespace Database\Factories;

use App\Models\CardState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CardState>
 */
class CardStateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['album', 'playlist']),
            'reference_id' => fake()->numberBetween(1, 1000),
            'folder_name' => fake()->slug(),
            'synced_at' => fake()->optional(0.7)->dateTime(),
        ];
    }
}
