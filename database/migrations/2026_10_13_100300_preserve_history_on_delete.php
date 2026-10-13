<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stop cascade deletes from erasing learning history and authorship.
 *
 * Each listed foreign key is made NULLABLE with ON DELETE SET NULL:
 * - reports.teacher_id: deleting a teacher kept deleting every report they wrote.
 * - class_reports.schedule_id / curriculum_id / syllabus_id / material_id: deleting a
 *   schedule slot or editing the curriculum deleted teachers' meeting reports.
 * - announcements.user_id, class_photos.uploaded_by: content survives its author.
 * - certificates.issued_by, reward_redemptions.processed_by: these had no rule at all
 *   (RESTRICT), which made deleting the issuing admin/teacher throw a 500.
 *
 * Schedules keep their cascades (a schedule row is a derived weekly slot); because
 * class_reports.schedule_id is now SET NULL, the chain enrollment -> schedule ->
 * class report no longer destroys reports.
 */
return new class extends Migration
{
    /**
     * Foreign keys to relax: table => [column => referenced table].
     *
     * @var array<string, array<string, string>>
     */
    private array $keys = [
        'reports' => ['teacher_id' => 'users'],
        'class_reports' => [
            'schedule_id' => 'schedules',
            'curriculum_id' => 'curriculums',
            'syllabus_id' => 'syllabuses',
            'material_id' => 'materials',
        ],
        'announcements' => ['user_id' => 'users'],
        'class_photos' => ['uploaded_by' => 'users'],
        'certificates' => ['issued_by' => 'users'],
        'reward_redemptions' => ['processed_by' => 'users'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->keys as $table => $columns) {
            $this->rebuildForeignKeys($table, $columns, nullable: true, onDelete: 'set null');
        }
    }

    /**
     * Reverse the migrations: restore NOT NULL + CASCADE (or the original RESTRICT).
     *
     * Rows whose reference was nulled in the meantime cannot be restored, so the
     * NOT NULL constraint is only re-applied when no NULLs exist.
     */
    public function down(): void
    {
        $restrictOriginally = ['certificates' => true, 'reward_redemptions' => true];

        foreach ($this->keys as $table => $columns) {
            $onDelete = isset($restrictOriginally[$table]) ? 'restrict' : 'cascade';
            // reward_redemptions.processed_by was nullable from the start.
            $nullable = $table === 'reward_redemptions';

            $this->rebuildForeignKeys($table, $columns, nullable: $nullable, onDelete: $onDelete);
        }
    }

    /**
     * Drop, alter and re-create foreign keys in three passes.
     *
     * Reason: MySQL refuses to change a column that is part of a FK in some
     * versions, and SQLite rebuilds the table per pass, so the passes are kept
     * separate and in a fixed order on both drivers.
     *
     * @param  array<string, string>  $columns
     */
    private function rebuildForeignKeys(string $table, array $columns, bool $nullable, string $onDelete): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($columns) {
            foreach (array_keys($columns) as $column) {
                $blueprint->dropForeign([$column]);
            }
        });

        $canBeNotNull = $nullable || collect(array_keys($columns))
            ->every(fn (string $column) => ! DB::table($table)->whereNull($column)->exists());

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $nullable, $canBeNotNull) {
            foreach (array_keys($columns) as $column) {
                $blueprint->unsignedBigInteger($column)->nullable($nullable || ! $canBeNotNull)->change();
            }
        });

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $onDelete) {
            foreach ($columns as $column => $references) {
                $blueprint->foreign($column)->references('id')->on($references)->onDelete($onDelete);
            }
        });
    }
};
