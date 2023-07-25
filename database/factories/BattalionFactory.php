<?php

namespace Database\Factories\Model;

use App\Model\Battalion;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'battalias' => $this->faker->slug,
            'name' => $this->faker->userName,
            'battdescr' => $this->faker->text,
            'color' => $this->faker->colorName,
            'motto' => $this->faker->text(20)
        ];
    }
}
