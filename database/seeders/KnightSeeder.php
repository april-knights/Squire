<?php

namespace Database\Seeders;

use App\Models\Knight;
use Illuminate\Database\Seeder;

class KnightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "asd";
        Knight::factory()->grandmaster()->create();
        echo "asd";
        Knight::factory(10)->commander()->create();
        echo "asd";
        Knight::factory(10)->councilor()->create();
        echo "asd";
        Knight::factory(50)->knight()->create();
        echo "asd";
    }
}
