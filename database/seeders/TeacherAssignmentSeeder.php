<?php

namespace Database\Seeders;

use App\Models\ClassName;
use Illuminate\Database\Seeder;

/**
 * Repair seeder: make teacher_assignments mirror the class_teacher pivot for
 * every class (the pivot is the source of truth).
 *
 * Classes created by ClassNameSeeder are already consistent; this also fixes
 * classes created elsewhere (e.g. older seeds where the two tables disagreed).
 * Idempotent.
 */
class TeacherAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fixed = 0;

        ClassName::with('teachers:id')->orderBy('id')->each(function (ClassName $class) use (&$fixed) {
            $pivotIds = $class->teachers->pluck('id')->sort()->values()->all();
            $activeIds = $class->activeTeacherAssignments()->pluck('teacher_id')->unique()->sort()->values()->all();

            if ($pivotIds !== $activeIds && $pivotIds !== []) {
                $class->syncTeachers($pivotIds);
                $fixed++;
            }
        });

        $this->command?->info("Teacher assignments synced with class_teacher ({$fixed} classes repaired).");
    }
}
