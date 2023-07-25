<?php

namespace Database\Seeders;

use App\Model\Battalion;
use App\Model\Knight;
use Illuminate\Database\Seeder;

class BattalionSeeder extends Seeder
{
    const PER_RANK_COUNT = 10;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $knightStateFunc = function (array $attrs, Battalion $battalion) {
            return ['batt' => $battalion->id];
        };
        foreach (range(1, 5) as $ignored) {
            $battalion = Battalion::factory()->hasLeader(
                Knight::whereDoesntHave('battalion')->whereHas('rank', fn($query) => $query->commander()),
                $knightStateFunc
            )->create();
            Knight::whereDoesntHave('battalion')->inRandomOrder()->limit(10)
                ->battalion()->associate($battalion)->save();
        }
    }
}
