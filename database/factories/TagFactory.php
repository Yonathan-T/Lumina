<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $journalTags = [
            '#mindfulness', '#anxiety', '#gratitude', 
            '#dreams', '#goals', '#relationships',
            '#work', '#health', '#creativity'
        ];
    
        // Randomly pick between predefined tags and new ones
        $name = fake()->randomElement([
            fake()->randomElement($journalTags),
            '#' . fake()->unique()->word() // Random new tag (e.g. "#sunshine")
        ]);
    
        return [
            'name' => $name,
            'slug' => Str::slug(str_replace('#', '', $name)),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
