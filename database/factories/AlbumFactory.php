<?php

namespace Database\Factories;

use App\Models\Album;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Album>
 */
class AlbumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $artist = fake()->lastName();
        $title = fake()->words(3, asText: true);

        return [
            'artist' => $artist,
            'title' => $title,
            'year' => fake()->year(),
            'cover_path' => fake()->optional(0.7)->imageUrl(),
            'folder_path' => fake()->slug() . '/' . fake()->slug(),
        ];
    }
}
