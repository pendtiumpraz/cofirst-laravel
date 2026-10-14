<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unique constraints for one-to-one tables plus composite indexes for the
 * queries every dashboard runs.
 *
 * - profiles.user_id and user_points.user_id are used through hasOne() but were
 *   not unique. Duplicates are merged first (profiles: keep the newest row;
 *   user_points: sum the counters into the oldest row).
 * - Composite indexes start with the foreign-key column, so on SQLite (where
 *   constrained() creates no index) they also serve as the FK index.
 * Every index is created only when missing, so a partially-applied run can be retried.
 */
return new class extends Migration
{
    /**
     * Indexes to create: [table, columns, name].
     *
     * @var array<int, array{0: string, 1: array<int, string>, 2: string}>
     */
    private array $indexes = [
        ['enrollments', ['student_id', 'status'], 'enrollments_student_status_index'],
        ['enrollments', ['class_id', 'status'], 'enrollments_class_status_index'],
        ['enrollments', ['course_id', 'status'], 'enrollments_course_status_index'],
        ['reports', ['student_id', 'report_date'], 'reports_student_date_index'],
        ['reports', ['class_id', 'report_date'], 'reports_class_date_index'],
        ['class_reports', ['student_id', 'report_date'], 'class_reports_student_date_index'],
        ['class_reports', ['class_id', 'meeting_number'], 'class_reports_class_meeting_index'],
        ['class_reports', ['teacher_id', 'report_date'], 'class_reports_teacher_date_index'],
        ['announcements', ['start_date', 'end_date'], 'announcements_period_index'],
        ['class_names', ['status', 'is_active'], 'class_names_status_active_index'],
        ['class_names', ['course_id', 'status'], 'class_names_course_status_index'],
        ['material_access', ['student_id', 'material_id', 'accessed_at'], 'material_access_student_material_index'],
        ['users', ['is_active'], 'users_is_active_index'],
        ['schedules', ['class_id', 'day_of_week', 'start_time'], 'schedules_class_slot_index'],
        ['schedules', ['enrollment_id', 'is_active'], 'schedules_enrollment_active_index'],
        ['schedules', ['teacher_assignment_id', 'is_active'], 'schedules_assignment_active_index'],
        ['teacher_assignments', ['class_id', 'teacher_id'], 'teacher_assignments_class_teacher_index'],
        ['teacher_assignments', ['teacher_id', 'unassigned_at'], 'teacher_assignments_teacher_active_index'],
        ['class_teacher', ['teacher_id', 'class_id'], 'class_teacher_teacher_class_index'],
        ['parent_student', ['student_id', 'parent_id'], 'parent_student_student_parent_index'],
        ['syllabuses', ['curriculum_id', 'meeting_number'], 'syllabuses_curriculum_meeting_index'],
        ['materials', ['syllabus_id', 'order'], 'materials_syllabus_order_index'],
        ['curriculums', ['course_id', 'status'], 'curriculums_course_status_index'],
        ['class_progress', ['class_id', 'status'], 'class_progress_class_status_index'],
        ['testimonials', ['user_id', 'is_active'], 'testimonials_user_active_index'],
        ['project_galleries', ['student_id', 'is_featured'], 'project_galleries_student_featured_index'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->dedupeProfiles();
        $this->dedupeUserPoints();

        if (! Schema::hasIndex('profiles', 'profiles_user_id_unique')) {
            Schema::table('profiles', fn (Blueprint $table) => $table->unique('user_id'));
        }

        if (! Schema::hasIndex('user_points', 'user_points_user_id_unique')) {
            Schema::table('user_points', fn (Blueprint $table) => $table->unique('user_id'));
        }

        foreach ($this->indexes as [$table, $columns, $name]) {
            if (! Schema::hasIndex($table, $name)) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (array_reverse($this->indexes) as [$table, , $name]) {
            if (Schema::hasIndex($table, $name)) {
                $this->restoreForeignKeyIndexes($table, $name);
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
            }
        }

        // Reason: MySQL silently drops the FK's implicit index once the unique index
        // exists, so the FK index is added back before the unique one is dropped.
        foreach (['profiles', 'user_points'] as $table) {
            if (Schema::hasIndex($table, "{$table}_user_id_unique")) {
                $this->restoreForeignKeyIndexes($table, "{$table}_user_id_unique");
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropUnique(['user_id']));
            }
        }
    }

    /**
     * Give every foreign key served by index $name its own index again before $name is dropped.
     *
     * Reason: MySQL/MariaDB silently drop a foreign key's implicit index once a
     * composite index starting with the same column exists (as up() creates), and
     * then refuse to drop that composite index (error 1553, "needed in a foreign key
     * constraint"). Re-creating "<table>_<column>_foreign" also restores the
     * pre-migration schema. SQLite needs no index for a foreign key.
     */
    private function restoreForeignKeyIndexes(string $table, string $name): void
    {
        if (! in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $indexes = collect(Schema::getIndexes($table));
        $dropped = $indexes->firstWhere('name', $name)['columns'] ?? [];

        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            $columns = $foreignKey['columns'];
            $startsWithKey = fn (array $indexColumns) => array_slice($indexColumns, 0, count($columns)) === $columns;

            if (! $startsWithKey($dropped)) {
                continue;
            }

            $stillCovered = $indexes->contains(
                fn (array $index) => $index['name'] !== $name && $startsWithKey($index['columns'])
            );

            if (! $stillCovered) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $foreignKey['name']));
                $indexes = collect(Schema::getIndexes($table));
            }
        }
    }

    /**
     * Keep only the newest profile per user.
     */
    private function dedupeProfiles(): void
    {
        $userIds = DB::table('profiles')->select('user_id')
            ->groupBy('user_id')->havingRaw('COUNT(*) > 1')->pluck('user_id');

        foreach ($userIds as $userId) {
            $keepId = DB::table('profiles')->where('user_id', $userId)->max('id');
            DB::table('profiles')->where('user_id', $userId)->where('id', '<>', $keepId)->delete();
        }
    }

    /**
     * Merge duplicate point rows into the oldest row per user.
     */
    private function dedupeUserPoints(): void
    {
        $userIds = DB::table('user_points')->select('user_id')
            ->groupBy('user_id')->havingRaw('COUNT(*) > 1')->pluck('user_id');

        foreach ($userIds as $userId) {
            $rows = DB::table('user_points')->where('user_id', $userId)->orderBy('id')->get();
            $keep = $rows->first();

            DB::table('user_points')->where('id', $keep->id)->update([
                'points' => $rows->sum('points'),
                'total_earned' => $rows->sum('total_earned'),
                'total_spent' => $rows->sum('total_spent'),
                'level' => $rows->max('level'),
                'current_streak' => $rows->max('current_streak'),
                'longest_streak' => $rows->max('longest_streak'),
                'last_activity_date' => $rows->max('last_activity_date'),
            ]);

            DB::table('user_points')->where('user_id', $userId)->where('id', '<>', $keep->id)->delete();
        }
    }
};
