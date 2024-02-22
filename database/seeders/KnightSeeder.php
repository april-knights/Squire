<?php

namespace Database\Seeders;

use App\Models\Knight;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KnightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Needed because the grandmaster is created by themselves
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        // If the seeder fails, nothing gets written to the DB, yet the auto increment value is changed
        DB::statement('ALTER TABLE knight AUTO_INCREMENT = 1');
        Knight::factory()->grandmaster()->create();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        Knight::factory(10)->commander()->create();
        Knight::factory(10)->councilor()->create();
        Knight::factory(50)->knight()->create();
    }
}
