<?php

namespace Database\Seeders;

use App\Model\Knight;
use Illuminate\Database\Seeder;

class KnightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Knight::factory()->grandmaster()->create();
        Knight::factory(10)->commander()->create();
        Knight::factory(10)->councilor()->create();
        Knight::factory(50)->knight()->create();
    }
}
