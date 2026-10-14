<?php

namespace Tests\Feature\Data;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Migrations run, roll back and re-run on SQLite.
 *
 * Reason: uses its own temporary SQLite file connection so the shared in-memory
 * test database (migrated once per process by RefreshDatabase) is never touched.
 */
class MigrationRollbackTest extends TestCase
{
    private const CONNECTION = 'migration_probe';

    /** Number of forward migrations added by the 2026-10-13 data-layer overhaul. */
    private const OVERHAUL_STEPS = 9;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = tempnam(sys_get_temp_dir(), 'cf-migrate-');
        config(['database.connections.'.self::CONNECTION => [
            'driver' => 'sqlite',
            'database' => $this->path,
            'prefix' => '',
            'foreign_key_constraints' => true,
            // Reason: a throwaway file; skipping fsync makes the many table rebuilds fast.
            'journal_mode' => 'memory',
            'synchronous' => 'off',
        ]]);
    }

    protected function tearDown(): void
    {
        DB::purge(self::CONNECTION);
        @unlink($this->path);

        parent::tearDown();
    }

    public function test_fresh_migration_produces_the_expected_schema(): void
    {
        $this->migrate('migrate:fresh');
        $schema = Schema::connection(self::CONNECTION);

        foreach (['attendance' => 'student_id', 'teacher_handovers' => 'to_teacher_id', 'financial_transactions' => 'refund_of_id',
            'users' => 'deleted_at', 'reports' => 'score', 'class_photos' => 'date_taken', 'enrollments' => 'notes'] as $table => $column) {
            $this->assertTrue($schema->hasColumn($table, $column), "{$table}.{$column} missing");
        }

        $this->assertFalse($schema->hasTable('class_teacher_pivot'));
        $this->assertFalse($schema->hasTable('gamification_tables'));
        $this->assertTrue($schema->hasIndex('profiles', 'profiles_user_id_unique'));

        $foreignKeys = collect($schema->getForeignKeys('financial_transactions'))->keyBy(fn ($fk) => $fk['columns'][0]);
        $this->assertSame('restrict', strtolower($foreignKeys['student_id']['on_delete']));
        $this->assertSame('set null', strtolower($foreignKeys['course_id']['on_delete']));
    }

    public function test_rolling_back_one_step_and_migrating_again_works(): void
    {
        $this->migrate('migrate:fresh');
        $this->migrate('migrate:rollback', ['--step' => 1]);

        $this->assertFalse(Schema::connection(self::CONNECTION)->hasColumn('class_photos', 'date_taken'));

        $this->migrate('migrate');
        $this->assertTrue(Schema::connection(self::CONNECTION)->hasColumn('class_photos', 'date_taken'));
    }

    public function test_rolling_back_the_whole_overhaul_restores_the_previous_schema_with_data(): void
    {
        $this->migrate('migrate:fresh');
        $db = DB::connection(self::CONNECTION);
        $userId = $db->table('users')->insertGetId(['name' => 'A', 'email' => 'a@example.test', 'password' => 'x', 'created_at' => now(), 'updated_at' => now()]);
        $courseId = $db->table('courses')->insertGetId(['name' => 'C', 'slug' => 'c', 'description' => 'd', 'price' => 1000000, 'created_at' => now(), 'updated_at' => now()]);
        $db->table('financial_transactions')->insert(['student_id' => $userId, 'course_id' => $courseId, 'amount' => 1000000, 'payment_method' => null, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);

        $this->migrate('migrate:rollback', ['--step' => self::OVERHAUL_STEPS]);
        $schema = Schema::connection(self::CONNECTION);

        // Edge: the stub tables are back and the new columns are gone, data kept.
        $this->assertTrue($schema->hasTable('class_teacher_pivot'));
        $this->assertFalse($schema->hasColumn('attendance', 'student_id'));
        $this->assertFalse($schema->hasColumn('users', 'deleted_at'));
        $this->assertSame('cash', $db->table('financial_transactions')->value('payment_method'));

        $this->migrate('migrate');
        $this->assertTrue($schema->hasColumn('financial_transactions', 'invoice_number'));
        $this->assertSame(1, $db->table('financial_transactions')->count());
    }

    public function test_the_new_schema_rejects_invalid_data(): void
    {
        $this->migrate('migrate:fresh');
        $db = DB::connection(self::CONNECTION);
        $userId = $db->table('users')->insertGetId(['name' => 'A', 'email' => 'a@example.test', 'password' => 'x']);
        $classId = $db->table('class_names')->insertGetId(['course_id' => $db->table('courses')->insertGetId(['name' => 'C', 'slug' => 'c', 'description' => 'd', 'price' => 1]), 'name' => 'K', 'start_date' => '2026-01-01', 'end_date' => '2026-02-01']);
        $row = ['class_id' => $classId, 'student_id' => $userId, 'attendance_date' => '2026-01-05', 'status' => 'present'];
        $db->table('attendance')->insert($row);

        // Failure: one attendance row per student per class per day.
        $this->expectException(QueryException::class);
        $db->table('attendance')->insert($row);
    }

    /**
     * Run a migration command against the probe connection and assert success.
     *
     * @param  array<string, mixed>  $options
     */
    private function migrate(string $command, array $options = []): void
    {
        $exit = Artisan::call($command, $options + ['--database' => self::CONNECTION, '--force' => true]);

        $this->assertSame(0, $exit, Artisan::output());
    }
}
