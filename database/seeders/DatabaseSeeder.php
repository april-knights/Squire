<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(BattalionSeeder::class);
        $this->call(DivisionSeeder::class);
        $this->call(KnightSeeder::class);
        $this->call(OrderSeeder::class);
    }
}
