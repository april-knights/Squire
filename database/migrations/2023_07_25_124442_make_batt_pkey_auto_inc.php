<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
          DB::statement('ALTER TABLE battalion MODIFY pkey TINYINT AUTO_INCREMENT');
          DB::statement('ALTER TABLE battalion AUTO_INCREMENT = 10');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE battalion MODIFY pkey TINYINT NOT NULL');
    }
};
