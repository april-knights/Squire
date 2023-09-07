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
        DB::statement('ALTER TABLE battalion MODIFY crtsetdt DATETIME DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE battalion MODIFY lstmddt DATETIME DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE battalion MODIFY activeflg BIT(1) DEFAULT 1');
        DB::statement('ALTER TABLE battalion MODIFY delflg BIT(1) DEFAULT 0');

        foreach (['division', 'krank', 'security', 'skill'] as $table) {
            DB::statement("ALTER TABLE $table MODIFY crtsetdt DATETIME DEFAULT CURRENT_TIMESTAMP");
            DB::statement("ALTER TABLE $table MODIFY lstmdts DATETIME DEFAULT CURRENT_TIMESTAMP");
            DB::statement("ALTER TABLE $table MODIFY activeflg BIT(1) DEFAULT 1");
            DB::statement("ALTER TABLE $table MODIFY delflg BIT(1) DEFAULT 0");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE battalion MODIFY crtsetdt DATETIME');
        DB::statement('ALTER TABLE battalion MODIFY lstmddt DATETIME');
        DB::statement('ALTER TABLE battalion MODIFY activeflg BIT(1)');
        DB::statement('ALTER TABLE battalion MODIFY delflg BIT(1)');

        foreach (['division', 'krank', 'security', 'skill'] as $table) {
            DB::statement("ALTER TABLE $table MODIFY crtsetdt DATETIME");
            DB::statement("ALTER TABLE $table MODIFY lstmdts DATETIME");
            DB::statement("ALTER TABLE $table MODIFY activeflg BIT(1)");
            DB::statement("ALTER TABLE $table MODIFY delflg BIT(1)");
        }
    }
};
