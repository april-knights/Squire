<?php

namespace Database\Factories;

use App\Models\Battalion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends Factory<Battalion>
 */
class BattalionFactory extends Factory
{
    use FactoryHasActive;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'battalias' => Str::slug($this->faker->text(8)),
            'name' => $this->faker->userName,
            'battdescr' => $this->faker->text,
            'color' => $this->faker->colorName,
            'motto' => $this->faker->text(20),
            'crtsetid' => 1, // The grandmaster is created first, make it so every battalion is created by them
            'lstmdby' => 1 // Same for modifying
        ];
    }
}
