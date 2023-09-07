<?php

namespace Database\Seeders;

use App\Models\Battalion;
use App\Models\Knight;
use Illuminate\Database\Seeder;

class BattalionSeeder extends Seeder
{
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
            Knight::whereDoesntHave('battalion')->inRandomOrder()->limit(10)->get()
                ->each(function (Knight $knight) use ($battalion) {
                    $knight->battalion()->associate($battalion)->save();
                });
        }
    }
}
