<?php

namespace Tests\Feature\Admin;

use App\Models\ClassName;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\TeacherAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\Concerns\AdminTestHelpers;
use Tests\TestCase;

/**
 * Admin › Kelas: pages, create/update without curriculum, capacity, roster and archiving.
 */
class ClassManagementTest extends TestCase
{
    use AdminTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function payload(Course $course, array $teacherIds, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Python Remaja Sabtu',
            'course_id' => $course->id,
            'curriculum_id' => '',
            'type' => 'group_class_3_5_kids',
            'delivery_method' => 'offline',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'max_students' => 5,
            'status' => 'active',
            'teacher_ids' => $teacherIds,
        ], $overrides);
    }

    public function test_class_pages_render_for_admin(): void
    {
        $admin = $this->admin();
        $class = $this->activeClass();
        $this->enroll($class);
        Schedule::factory()->create(['class_id' => $class->id]);

        $this->actingAs($admin)->get(route('admin.classes.index'))->assertOk()->assertSee($class->name);
        $this->actingAs($admin)->get(route('admin.classes.show', $class))->assertOk()->assertSee($class->name);
        $this->actingAs($admin)->get(route('admin.classes.students', $class))->assertOk();
        $this->actingAs($admin)->get(route('admin.classes.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.classes.edit', $class))->assertOk();
    }

    public function test_class_can_be_created_for_a_course_without_curriculum(): void
    {
        $admin = $this->admin();
        $teacher = $this->teacher();
        $course = Course::factory()->create();

        $this->actingAs($admin)->getJson(route('admin.courses.curriculum', $course))
            ->assertOk()->assertExactJson(['curriculums' => []]);

        $this->actingAs($admin)->post(route('admin.classes.store'), $this->payload($course, [$teacher->id]))
            ->assertSessionHasNoErrors();

        $class = ClassName::where('name', 'Python Remaja Sabtu')->firstOrFail();
        $this->assertNull($class->curriculum_id);
        $this->assertTrue($class->is_active);
        $this->assertTrue($teacher->teachesClass($class));
        $this->assertSame(1, $class->activeTeacherAssignments()->count());
    }

    public function test_curriculum_must_belong_to_the_course_and_capacity_follows_type(): void
    {
        $admin = $this->admin();
        $teacher = $this->teacher();
        $course = Course::factory()->create();
        $foreign = Curriculum::factory()->create();

        $this->actingAs($admin)->post(route('admin.classes.store'), $this->payload($course, [$teacher->id], [
            'curriculum_id' => $foreign->id,
        ]))->assertSessionHasErrors('curriculum_id');

        $this->actingAs($admin)->post(route('admin.classes.store'), $this->payload($course, [$teacher->id], [
            'type' => 'private_online_1on1', 'max_students' => 3,
        ]))->assertSessionHasErrors('max_students');

        $this->actingAs($admin)->post(route('admin.classes.store'), $this->payload($course, [$this->student()->id]))
            ->assertSessionHasErrors('teacher_ids.0');
    }

    public function test_update_does_not_recreate_assignments_when_teachers_unchanged(): void
    {
        $admin = $this->admin();
        $teacher = $this->teacher();
        $class = $this->activeClass(5, $teacher);
        $before = TeacherAssignment::where('class_id', $class->id)->count();

        $this->actingAs($admin)->put(route('admin.classes.update', $class), $this->payload($class->course, [(string) $teacher->id], [
            'name' => 'Nama Baru',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('Nama Baru', $class->fresh()->name);
        $this->assertSame($before, TeacherAssignment::where('class_id', $class->id)->count());
    }

    public function test_capacity_cannot_drop_below_seats_taken(): void
    {
        $admin = $this->admin();
        $teacher = $this->teacher();
        $class = $this->activeClass(5, $teacher);
        $this->enroll($class);
        $this->enroll($class);

        $this->actingAs($admin)->put(route('admin.classes.update', $class), $this->payload($class->course, [$teacher->id], [
            'max_students' => 1,
        ]))->assertSessionHasErrors('max_students');
    }

    public function test_roster_add_student_respects_capacity_and_role(): void
    {
        $admin = $this->admin();
        $class = $this->activeClass(1);
        $first = $this->student();

        $this->actingAs($admin)->post(route('admin.classes.add-student', $class), ['student_id' => $first->id])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('enrollments', ['class_id' => $class->id, 'student_id' => $first->id, 'status' => 'active', 'course_id' => $class->course_id]);

        // Edge: the class is now full.
        $this->actingAs($admin)->post(route('admin.classes.add-student', $class), ['student_id' => $this->student()->id])
            ->assertSessionHasErrors('student_id');

        // Failure: a teacher is not a student.
        $this->actingAs($admin)->post(route('admin.classes.add-student', $this->activeClass(3)), ['student_id' => $this->teacher()->id])
            ->assertSessionHasErrors('student_id');
    }

    public function test_new_student_joins_existing_weekly_slots_and_removal_keeps_history(): void
    {
        $admin = $this->admin();
        $class = $this->activeClass(5);
        $existing = $this->enroll($class);
        Schedule::factory()->create(['class_id' => $class->id, 'enrollment_id' => $existing->id, 'day_of_week' => 2, 'start_time' => '16:00:00', 'end_time' => '17:30:00', 'room' => 'Lab 1']);
        $student = $this->student();

        $this->actingAs($admin)->post(route('admin.classes.add-student', $class), ['student_id' => $student->id])->assertSessionHasNoErrors();
        $enrollment = Enrollment::where('class_id', $class->id)->where('student_id', $student->id)->firstOrFail();
        $this->assertSame(1, $enrollment->schedules()->where('day_of_week', 2)->where('is_active', true)->count());

        $this->actingAs($admin)->delete(route('admin.classes.remove-student', [$class, $student]))->assertSessionHasNoErrors();
        $this->assertSame('dropped', $enrollment->fresh()->status);
        $this->assertSame(0, $enrollment->schedules()->where('is_active', true)->count());
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id]);
    }

    public function test_archiving_requires_an_empty_class(): void
    {
        $super = $this->superadmin();
        $class = $this->activeClass();
        $this->enroll($class);

        $this->actingAs($super)->delete(route('admin.classes.destroy', $class))->assertSessionHas('error');
        $this->assertNotSoftDeleted('class_names', ['id' => $class->id]);

        $empty = $this->activeClass();
        $this->actingAs($super)->delete(route('admin.classes.destroy', $empty))->assertRedirect(route('admin.classes.index'));
        $this->assertSoftDeleted('class_names', ['id' => $empty->id]);
    }

    public function test_non_admin_cannot_manage_classes(): void
    {
        $class = $this->activeClass();

        $this->actingAs($this->teacher())->get(route('admin.classes.index'))->assertForbidden();
        $this->actingAs($this->student())->post(route('admin.classes.add-student', $class), ['student_id' => 1])->assertForbidden();
        // Admins lack "delete classes" by default.
        $this->actingAs($this->admin())->delete(route('admin.classes.destroy', $class))->assertForbidden();
    }
}
