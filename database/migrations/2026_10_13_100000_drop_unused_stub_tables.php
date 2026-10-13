<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop two empty tables that were created by mistaken migrations.
 *
 * - class_teacher_pivot (2025_07_08_000523): the real pivot is class_teacher.
 * - gamification_tables (2025_07_05_235501): the real gamification tables are
 *   user_points, point_transactions, badges, user_badges, rewards, reward_redemptions.
 *
 * Neither table is referenced anywhere. The original migrations stay in place
 * because production has already run them; this forward migration removes the tables.
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private array $tables = ['class_teacher_pivot', 'gamification_tables'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }
    }

    /**
     * Reverse the migrations (recreate the empty stubs exactly as they were).
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                Schema::create($table, function (Blueprint $blueprint) {
                    $blueprint->id();
                    $blueprint->timestamps();
                });
            }
        }
    }
};
