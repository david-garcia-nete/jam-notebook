<?php

namespace Database\Factories;

use App\Models\Pattern;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pattern>
 */
class PatternFactory extends Factory
{
    protected $model = Pattern::class;

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
            'type' => fake()->randomElement([
                'chord progression',
                'bassline',
                'drum groove',
                'melody',
                'lyrics',
                'exercise',
                'arrangement idea',
            ]),
            'instrument' => fake()->randomElement(['piano', 'guitar', 'bass', 'drums', 'vocals', 'synth', 'other']),
            'key' => fake()->randomElement(['C', 'G', 'D', 'A minor', 'F# minor']),
            'tempo' => fake()->numberBetween(70, 160),
            'style' => fake()->randomElement(['rock', 'jazz', 'funk', 'ambient']),
            'difficulty' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'content' => fake()->paragraph(),
            'tablature' => "E|----------------|\nB|--3--5--7--8----|\nG|----------------|",
            'notation_url' => fake()->optional()->url(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
