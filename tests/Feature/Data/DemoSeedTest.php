<?php

namespace Tests\Feature\Data;

use App\Models\Certificate;
use App\Models\ClassName;
use App\Models\ClassProgress;
use App\Models\Conversation;
use App\Models\Enrollment;
use App\Models\FinancialTransaction;
use App\Models\PointTransaction;
use App\Models\RewardRedemption;
use App\Models\Schedule;
use App\Models\User;
use App\Models\UserPoint;
use Database\Seeders\Data\DemoBlueprint;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The demo dataset produced by DatabaseSeeder is complete and consistent.
 */
class DemoSeedTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Reason: the in-memory DB is migrated once per process; when another test
        // class migrated it without seeding, seed inside this test's transaction.
        if (! User::where('email', 'student@codingfirst.com')->exists()) {
            $this->seed();
        }
    }

    public function test_demo_parent_and_demo_student_are_linked_and_the_student_is_a_child(): void
    {
        $parent = User::where('email', 'parent@codingfirst.com')->firstOrFail();
        $student = User::where('email', 'student@codingfirst.com')->firstOrFail();

        $this->assertTrue($parent->hasRole('parent'));
        $this->assertTrue($student->hasRole('student'));
        $this->assertTrue($parent->isParentOf($student));
        $this->assertTrue($student->parents()->whereKey($parent->id)->exists());
        $this->assertSame(2, $parent->children()->count());
        $this->assertTrue($student->isChild());
        $this->assertGreaterThanOrEqual(8, $student->age);

        foreach (['superadmin', 'admin', 'finance', 'teacher', 'student', 'parent'] as $role) {
            $user = User::where('email', "{$role}@codingfirst.com")->firstOrFail();
            $this->assertTrue($user->hasRole($role), "{$role}@ must have the {$role} role");
            $this->assertNotNull($user->profile, "{$role}@ must have a profile");
        }
    }

    public function test_demo_student_has_real_data_everywhere(): void
    {
        $student = User::where('email', 'student@codingfirst.com')->firstOrFail();
        $active = $student->activeEnrollments()->get();

        $this->assertGreaterThanOrEqual(2, $active->count());
        $this->assertTrue(Schedule::forStudent($student)->where('is_active', true)->exists());
        $this->assertTrue($student->transactions()->where('status', 'paid')->exists());
        $this->assertTrue($student->transactions()->where('status', 'pending')->exists());
        $this->assertTrue($student->attendances()->exists());
        $this->assertTrue($student->classReports()->exists());
        $this->assertTrue($student->studentReports()->exists());
        $this->assertTrue($student->certificates()->exists());
        $this->assertTrue($student->projectGalleries()->exists());
        $this->assertGreaterThan(0, $student->points->points);

        foreach ($active as $enrollment) {
            $this->assertGreaterThan(0, ClassProgress::getProgressPercentage($student->id, $enrollment->class_id));
            $this->assertLessThan(100, ClassProgress::getProgressPercentage($student->id, $enrollment->class_id));
        }

        $teacher = User::where('email', 'teacher@codingfirst.com')->firstOrFail();
        $conversation = Conversation::getPrivateConversation($student->id, $teacher->id);
        $this->assertGreaterThan(0, $conversation->messages()->count());
    }

    public function test_demo_teacher_has_classes_and_assignment_tables_agree(): void
    {
        $teacher = User::where('email', 'teacher@codingfirst.com')->firstOrFail();

        $this->assertGreaterThanOrEqual(3, $teacher->taughtClasses()->count());
        $this->assertTrue($teacher->teachingClasses()->where('status', 'completed')->exists());

        foreach (ClassName::with('teachers')->get() as $class) {
            $pivot = $class->teachers->pluck('id')->sort()->values()->all();
            $history = $class->activeTeacherAssignments()->pluck('teacher_id')->sort()->values()->all();
            $this->assertSame($pivot, $history, "Teacher tables disagree for {$class->name}");
            $this->assertNotNull($class->teacher, "{$class->name} needs a teacher");
        }

        foreach (Schedule::with(['teacherAssignment', 'enrollment'])->get() as $schedule) {
            $this->assertSame($schedule->class_id, $schedule->enrollment->class_id);
            $this->assertSame($schedule->class_id, $schedule->teacherAssignment->class_id);
            $this->assertNull($schedule->teacherAssignment->unassigned_at);
        }
    }

    public function test_classes_are_consistent_with_dates_capacity_and_curriculum(): void
    {
        $today = Carbon::today();
        $classes = ClassName::with('curriculum')->withSeatsTaken()->get();

        $this->assertGreaterThanOrEqual(18, $classes->count());
        $this->assertEqualsCanonicalizing(['active', 'planned', 'completed'], $classes->pluck('status')->unique()->values()->all());

        foreach ($classes as $class) {
            $this->assertLessThanOrEqual($class->max_students, $class->seatsTaken(), "{$class->name} over capacity");
            $this->assertSame($class->course_id, $class->curriculum?->course_id, "{$class->name} uses another course's curriculum");

            match ($class->status) {
                'active' => $this->assertTrue($class->start_date->lte($today) && $class->end_date->gte($today), $class->name),
                'completed' => $this->assertTrue($class->end_date->lt($today), $class->name),
                'planned' => $this->assertTrue($class->start_date->gt($today), $class->name),
            };
        }

        $students = User::role('student')->with('profile', 'parents')->get();
        $children = $students->filter(fn (User $u) => $u->isChild());
        $this->assertCount(24, $students);
        $this->assertCount(16, $children);

        foreach ($students as $student) {
            $this->assertSame($student->isChild(), $student->parents->isNotEmpty(), "{$student->name}: only children have parents");
            $count = $student->enrollments()->count();
            $this->assertTrue($count >= 1 && $count <= 3, "{$student->name} has {$count} enrollments");
        }
    }

    public function test_financial_data_covers_every_status_with_whole_rupiah(): void
    {
        $statuses = FinancialTransaction::distinct()->pluck('status')->all();

        foreach (['paid', 'pending', 'failed', 'refunded', 'cancelled'] as $status) {
            $this->assertContains($status, $statuses);
        }

        $this->assertGreaterThanOrEqual(2, FinancialTransaction::overdue()->count());
        $this->assertSame(0, FinancialTransaction::whereNull('payment_method')->count());
        $this->assertSame(0, (int) DB::table('financial_transactions')->whereRaw('amount <> CAST(amount AS INTEGER)')->count());
        $this->assertTrue(FinancialTransaction::where('transaction_date', '<', now()->subMonths(4))->exists());

        $refund = FinancialTransaction::refunds()->with('refundOf')->firstOrFail();
        $this->assertSame('refunded', $refund->refundOf->status);
        $this->assertSame($refund->amount, $refund->refundOf->amount);

        foreach (FinancialTransaction::with('student.parents')->get() as $tx) {
            $parentId = $tx->student->parents->first()?->id;
            $this->assertSame($parentId, $tx->payer_id, "payer of {$tx->transaction_reference}");
        }
    }

    public function test_points_match_transactions_and_redemptions_exist(): void
    {
        foreach (UserPoint::all() as $row) {
            $sum = (int) PointTransaction::where('user_id', $row->user_id)->sum('points');
            $earned = (int) PointTransaction::where('user_id', $row->user_id)->where('points', '>', 0)->sum('points');
            $this->assertSame($sum, $row->points);
            $this->assertSame($earned, $row->total_earned);
        }

        $statuses = RewardRedemption::pluck('status')->unique()->all();
        $this->assertContains('pending', $statuses);
        $this->assertContains('approved', $statuses);

        $completed = Enrollment::where('status', 'completed')->count();
        $this->assertSame($completed, Certificate::count());
    }

    public function test_reseeding_the_demo_data_is_idempotent(): void
    {
        $tables = ['users', 'enrollments', 'schedules', 'attendance', 'class_reports', 'reports', 'financial_transactions', 'point_transactions', 'certificates', 'messages', 'class_progress'];
        $before = collect($tables)->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()])->all();

        $this->seed(DemoSeeder::class);

        $after = collect($tables)->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()])->all();
        $this->assertSame($before, $after);

        // The demo logins keep working after a re-seed.
        $hash = User::where('email', 'student@codingfirst.com')->value('password');
        $this->assertTrue(Hash::check(DemoBlueprint::PASSWORD, $hash));
    }
}
