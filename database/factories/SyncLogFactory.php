<?php

namespace Database\Factories;

use App\Models\SyncLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SyncLog>
 */
class SyncLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTime();

        return [
            'status' => fake()->randomElement(['pending', 'running', 'completed', 'failed']),
            'started_at' => $startedAt,
            'finished_at' => fake()->optional(0.8)->dateTimeInInterval($startedAt, '+1 hour'),
            'summary' => [
                'added' => fake()->numberBetween(0, 50),
                'removed' => fake()->numberBetween(0, 20),
                'errors' => fake()->numberBetween(0, 5),
            ],
        ];
    }
}
