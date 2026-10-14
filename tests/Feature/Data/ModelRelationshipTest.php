<?php

namespace Tests\Feature\Data;

use App\Models\ClassName;
use App\Models\ClassReport;
use App\Models\Conversation;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Enrollment;
use App\Models\FinancialTransaction;
use App\Models\Material;
use App\Models\Report;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\Schedule;
use App\Models\Syllabus;
use App\Models\User;
use App\Models\UserPoint;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Relationships, integrity rules and model helpers against the real schema.
 */
class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_teacher_relation_is_eager_loadable_and_sync_keeps_tables_consistent(): void
    {
        [$first, $second] = User::factory()->teacher()->count(2)->create();
        $class = ClassName::factory()->active()->withTeacher($first)->create();
        $schedule = Schedule::factory()->create(['class_id' => $class->id]);

        $loaded = ClassName::with('teacher')->find($class->id);
        $this->assertTrue($loaded->relationLoaded('teacher'));
        $this->assertTrue($loaded->teacher->is($first));
        $this->assertTrue($schedule->teacherAssignment->teacher->is($first));

        $class->syncTeachers([$second->id]);

        $this->assertSame([$second->id], $class->teachers()->pluck('users.id')->all());
        $this->assertSame([$second->id], $class->activeTeacherAssignments()->pluck('teacher_id')->all());
        $this->assertNotNull($class->teacherAssignments()->where('teacher_id', $first->id)->value('unassigned_at'));
        $this->assertSame($second->id, $schedule->fresh()->teacherAssignment->teacher_id);
        $this->assertTrue($second->teachesClass($class));
        $this->assertFalse($first->teachesClass($class));
    }

    public function test_capacity_helpers_count_pending_and_active_only(): void
    {
        $class = ClassName::factory()->active()->create(['max_students' => 2]);
        Enrollment::factory()->create(['class_id' => $class->id, 'status' => 'active']);
        Enrollment::factory()->create(['class_id' => $class->id, 'status' => 'dropped']);

        $this->assertSame(1, $class->seatsTaken());
        $this->assertTrue($class->hasCapacity());
        $this->assertTrue(ClassName::withAvailableSeats()->whereKey($class->id)->exists());

        // Edge: a pending registration also takes the last seat.
        Enrollment::factory()->pending()->create(['class_id' => $class->id]);

        $this->assertTrue($class->isFull());
        $this->assertFalse($class->hasCapacity());
        $this->assertSame(0, $class->availableSeats());
        $this->assertFalse(ClassName::withAvailableSeats()->whereKey($class->id)->exists());
        $this->assertSame(2, ClassName::withSeatsTaken()->find($class->id)->seatsTaken());
    }

    public function test_enrollment_accepts_application_statuses_and_syncs_is_active(): void
    {
        $enrollment = Enrollment::factory()->create(['status' => 'pending']);
        $this->assertTrue($enrollment->is_active);

        $enrollment->update(['status' => 'suspended', 'notes' => 'Cuti sakit']);

        $this->assertFalse($enrollment->fresh()->is_active);
        $this->assertSame('suspended', $enrollment->fresh()->status);
        $this->assertSame('Cuti sakit', $enrollment->fresh()->notes);
    }

    public function test_enrollment_status_outside_the_enum_is_rejected(): void
    {
        $this->expectException(QueryException::class);

        Enrollment::factory()->create(['status' => 'graduated']);
    }

    public function test_report_can_be_created_with_the_teacher_form_payload(): void
    {
        $class = ClassName::factory()->active()->create();
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();

        $report = Report::create([
            'student_id' => $student->id, 'teacher_id' => $teacher->id, 'class_id' => $class->id,
            'report_type' => 'incident', 'title' => 'Kejadian', 'content' => 'Catatan', 'score' => 90, 'report_date' => now(),
        ]);

        $this->assertSame('incident', $report->fresh()->type);
        $this->assertSame('90.00', $report->fresh()->score);
        $this->assertTrue($student->studentReports()->whereKey($report->id)->exists());
    }

    public function test_transaction_without_payment_method_gets_defaults(): void
    {
        $tx = FinancialTransaction::create([
            'student_id' => User::factory()->student()->create()->id,
            'course_id' => Course::factory()->create()->id,
            'amount' => 1250000,
            'description' => 'Tagihan baru',
        ]);

        $tx->refresh();
        $this->assertNull($tx->payment_method);
        $this->assertSame('pending', $tx->status);
        $this->assertSame('payment', $tx->type);
        $this->assertNotNull($tx->transaction_date);
        $this->assertSame(1250000, $tx->amount);
    }

    public function test_deleting_a_user_or_course_keeps_payment_history(): void
    {
        $tx = FinancialTransaction::factory()->paid()->create();
        $student = $tx->student;

        $student->delete();
        $tx->course->forceDelete();

        $this->assertSoftDeleted($student);
        $this->assertNotNull($tx->fresh());
        $this->assertNull($tx->fresh()->course_id);
        $this->assertSame($student->id, $tx->fresh()->student_id);
    }

    public function test_force_deleting_a_student_with_payments_is_refused(): void
    {
        $tx = FinancialTransaction::factory()->create();

        $this->expectException(QueryException::class);

        $tx->student->forceDelete();
    }

    public function test_deleting_curriculum_content_keeps_meeting_reports(): void
    {
        $curriculum = Curriculum::factory()->create();
        $syllabus = Syllabus::factory()->create(['curriculum_id' => $curriculum->id, 'meeting_number' => 1]);
        $material = Material::factory()->create(['syllabus_id' => $syllabus->id]);
        $report = ClassReport::factory()->create([
            'curriculum_id' => $curriculum->id, 'syllabus_id' => $syllabus->id, 'material_id' => $material->id,
        ]);

        $material->delete();
        $this->assertSoftDeleted($material);
        $this->assertTrue($report->fresh()->material->is($material));

        $material->forceDelete();
        $this->assertNull($report->fresh()->material_id);
        $this->assertSame('09:00', $report->fresh()->start_time->format('H:i'));
    }

    public function test_course_slugs_stay_unique_including_archived_courses(): void
    {
        $first = Course::factory()->create(['name' => 'Kelas Robot', 'slug' => null]);
        $first->delete();
        $second = Course::factory()->create(['name' => 'Kelas Robot', 'slug' => null]);

        $this->assertSame('kelas-robot', $first->slug);
        $this->assertSame('kelas-robot-2', $second->slug);
        $this->assertTrue($second->curriculums()->doesntExist());
    }

    public function test_redemption_cancel_refunds_points_without_raising_total_earned(): void
    {
        $student = User::factory()->student()->create();
        $student->addPoints(300, 'attendance');
        $reward = Reward::factory()->limited(5)->create(['points_cost' => 200]);

        $redemption = $reward->redeemFor($student);
        $this->assertSame(100, $student->points->fresh()->points);
        $this->assertSame(1, $reward->fresh()->quantity_redeemed);

        $redemption->cancel(User::factory()->admin()->create()->id, 'Stok habis');

        $points = $student->points->fresh();
        $this->assertSame(300, $points->points);
        $this->assertSame(300, $points->total_earned);
        $this->assertSame(0, $points->total_spent);
        $this->assertSame(0, $reward->fresh()->quantity_redeemed);
        $this->assertSame('refund', $student->pointTransactions()->latest('id')->value('type'));
    }

    public function test_invalid_redemption_transition_is_rejected(): void
    {
        $redemption = RewardRedemption::factory()->create(['status' => RewardRedemption::STATUS_DELIVERED]);

        $this->expectException(ValidationException::class);

        $redemption->approve(User::factory()->admin()->create()->id);
    }

    public function test_redeeming_without_enough_points_fails_and_changes_nothing(): void
    {
        $student = User::factory()->student()->create();
        $reward = Reward::factory()->create(['points_cost' => 500]);

        try {
            $reward->redeemFor($student);
            $this->fail('Redeeming without points must throw.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Poin', $e->getMessage());
        }

        // The whole redemption was rolled back: no balance change, no redemption.
        $this->assertSame(0, (int) UserPoint::where('user_id', $student->id)->value('points'));
        $this->assertSame(0, RewardRedemption::where('user_id', $student->id)->count());
        $this->assertSame(0, $reward->fresh()->quantity_redeemed);
    }

    public function test_private_conversation_is_reused(): void
    {
        [$a, $b] = User::factory()->count(2)->create();

        $first = Conversation::getPrivateConversation($a->id, $b->id);
        $second = Conversation::getPrivateConversation($b->id, $a->id);

        $this->assertTrue($first->is($second));
        $this->assertSame(2, $first->participants()->count());
    }
}
