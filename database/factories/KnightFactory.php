<?php

namespace Database\Factories;

use App\Models\Battalion;
use App\Models\Event;
use App\Models\Knight;
use App\Models\Rank;
use App\Models\Security;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Knight>
 */
class KnightFactory extends Factory
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
            'knum' => $this->faker->unique()->numberBetween(100000),
            'rname' => $this->faker->unique()->userName,
            'dname' => $this->faker->unique()->userName,
            'discordid' => $this->faker->unique()->numberBetween(100000000000000000, 999999999999999999),
            'email' => $this->faker->email,
            'bio' => $this->faker->text(100),
            'rlimpact' => $this->faker->text(50),
            'frenemy' => $this->faker->boolean,
            'inttrans' => $this->faker->text, // Interview transcript
            'crtsetid' => 1, // The grandmaster is created first, make it so everyone is created by them
            'lstmdby' => 1 // Same for modifying
        ];
    }

    private function initRelations(int $securityId = null): static
    {
        $securityId ??= Security::inRandomOrder()->first()->pkey;
        return $this->state(function (array $data) use ($securityId) {
            return [
                'firstevent' => Event::inRandomOrder()->first()->pkey,
                'security' => $securityId,
                'rnk' => Rank::security(Security::find($securityId))->inRandomOrder()->first()->id
            ];
        });
    }

    /**
     * Grandmaster of the knights.
     * @return $this
     */
    public function grandmaster(): static
    {
        return $this->initRelations(Security::GRANDMASTER_SECURITY_ID);
    }

    /**
     * Leader of a battalion
     * @return $this
     */
    public function commander(): static
    {
        return $this->initRelations(Security::COMMANDER_SECURITY_ID);
    }

    /**
     * A member of the council.
     * @return $this
     */
    public function councilor(): static
    {
        return $this->initRelations(Security::COUNCILOR_SECURINTY_ID);
    }

    /**
     * Regular knight with random rank and security level.
     * @return $this
     */
    public function knight(): static
    {
        return $this->initRelations();
    }
}
