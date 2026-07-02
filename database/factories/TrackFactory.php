<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'album_id' => Album::factory(),
            'title' => fake()->words(3, asText: true),
            'track_number' => fake()->numberBetween(1, 20),
            'duration' => fake()->numberBetween(120, 600),
            'file_path' => fake()->slug() . '/' . fake()->slug() . '.mp3',
            'format' => fake()->randomElement(['mp3', 'flac', 'wav', 'aac']),
            'bitrate' => fake()->randomElement([128, 192, 256, 320]),
        ];
    }
}
