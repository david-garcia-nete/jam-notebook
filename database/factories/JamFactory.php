<?php

namespace Database\Factories;

use App\Models\Jam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jam>
 */
class JamFactory extends Factory
{
    protected $model = Jam::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'key' => fake()->optional()->randomElement(['C', 'G', 'D minor', 'A minor']),
            'tempo' => fake()->optional()->numberBetween(70, 160),
            'style' => fake()->optional()->randomElement(['rock', 'jazz', 'funk', 'ambient']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
