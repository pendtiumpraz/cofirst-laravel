<?php

namespace Tests\Feature\Admin;

use App\Models\Enrollment;
use App\Models\FinancialTransaction;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\Concerns\AdminTestHelpers;
use Tests\TestCase;

/**
 * Admin › Pendaftaran: capacity, statuses, approval queue, deletion guard and export.
 */
class EnrollmentManagementTest extends TestCase
{
    use AdminTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function payload(int $classId, int $studentId, array $overrides = []): array
    {
        return array_merge([
            'student_id' => $studentId,
            'class_id' => $classId,
            'enrollment_date' => now()->toDateString(),
            'status' => 'active',
            'payment_status' => 'unpaid',
        ], $overrides);
    }

    public function test_pages_render(): void
    {
        $admin = $this->admin();
        $enrollment = $this->enroll($this->activeClass());

        $this->actingAs($admin)->get(route('admin.enrollments.index'))->assertOk()->assertSee($enrollment->student->name);
        $this->actingAs($admin)->get(route('admin.enrollments.index', ['status' => 'pending']))->assertOk();
        $this->actingAs($admin)->get(route('admin.enrollments.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.enrollments.show', $enrollment))->assertOk();
        $this->actingAs($admin)->get(route('admin.enrollments.edit', $enrollment))->assertOk();
    }

    public function test_enrolling_into_an_empty_class_succeeds_and_joins_slots(): void
    {
        $admin = $this->admin();
        $class = $this->activeClass(3);
        $first = $this->enroll($class);
        Schedule::factory()->create(['class_id' => $class->id, 'enrollment_id' => $first->id, 'day_of_week' => 6, 'start_time' => '09:00:00', 'end_time' => '10:30:00']);
        $student = $this->student();

        $this->actingAs($admin)->post(route('admin.enrollments.store'), $this->payload($class->id, $student->id))
            ->assertSessionHasNoErrors();

        $enrollment = Enrollment::where('student_id', $student->id)->firstOrFail();
        $this->assertSame($class->course_id, $enrollment->course_id);
        $this->assertSame(1, $enrollment->schedules()->count());
    }

    public function test_full_class_and_duplicates_are_rejected(): void
    {
        $admin = $this->admin();
        $class = $this->activeClass(1);
        $student = $this->student();
        $this->enroll($class, $student);

        $this->actingAs($admin)->post(route('admin.enrollments.store'), $this->payload($class->id, $this->student()->id))
            ->assertSessionHasErrors('student_id');
        $this->actingAs($admin)->post(route('admin.enrollments.store'), $this->payload($this->activeClass(5)->id, $this->teacher()->id))
            ->assertSessionHasErrors('student_id');

        $this->assertSame(1, Enrollment::where('class_id', $class->id)->count());
    }

    public function test_suspended_status_and_approval_queue(): void
    {
        $admin = $this->admin();
        $class = $this->activeClass(5);
        $enrollment = $this->enroll($class, null, 'pending');

        $this->actingAs($admin)->post(route('admin.enrollments.toggle-status', $enrollment))->assertRedirect();
        $this->assertSame('active', $enrollment->fresh()->status);

        $this->actingAs($admin)->put(route('admin.enrollments.update', $enrollment), [
            'class_id' => $class->id, 'enrollment_date' => now()->toDateString(), 'status' => 'suspended', 'payment_status' => 'paid',
        ])->assertSessionHasNoErrors();
        $this->assertSame('suspended', $enrollment->fresh()->status);
        $this->assertFalse($enrollment->fresh()->is_active);
    }

    public function test_moving_to_a_full_class_is_rejected(): void
    {
        $admin = $this->admin();
        $enrollment = $this->enroll($this->activeClass(5));
        $full = $this->activeClass(1);
        $this->enroll($full);

        $this->actingAs($admin)->put(route('admin.enrollments.update', $enrollment), [
            'class_id' => $full->id, 'enrollment_date' => now()->toDateString(), 'status' => 'active', 'payment_status' => 'paid',
        ])->assertSessionHasErrors('class_id');
    }

    public function test_delete_is_refused_when_payments_exist(): void
    {
        $admin = $this->admin();
        $enrollment = $this->enroll($this->activeClass());
        FinancialTransaction::factory()->create(['student_id' => $enrollment->student_id, 'enrollment_id' => $enrollment->id]);

        $this->actingAs($admin)->delete(route('admin.enrollments.destroy', $enrollment))->assertSessionHas('error');
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id]);

        $clean = $this->enroll($this->activeClass());
        $this->actingAs($admin)->delete(route('admin.enrollments.destroy', $clean))->assertRedirect(route('admin.enrollments.index'));
        $this->assertDatabaseMissing('enrollments', ['id' => $clean->id]);
    }

    public function test_export_streams_csv(): void
    {
        $admin = $this->admin();
        $this->enroll($this->activeClass());

        $response = $this->actingAs($admin)->get(route('admin.enrollments.export'));
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Siswa', $response->streamedContent());
    }

    public function test_students_and_teachers_cannot_manage_enrollments(): void
    {
        $enrollment = $this->enroll($this->activeClass());

        $this->actingAs($this->teacher())->get(route('admin.enrollments.index'))->assertForbidden();
        $this->actingAs($enrollment->student)->post(route('admin.enrollments.toggle-status', $enrollment))->assertForbidden();
    }
}
