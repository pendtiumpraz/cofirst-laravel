<?php

namespace Tests\Feature\Admin;

use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\Concerns\AdminTestHelpers;
use Tests\TestCase;

/**
 * Admin › Jadwal: slots (one row per student), Sunday, clash detection,
 * slot editing, and the admin-only schedule-data API.
 */
class ScheduleManagementTest extends TestCase
{
    use AdminTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function payload($class, $teacher, array $enrollmentIds, array $overrides = []): array
    {
        return array_merge([
            'class_id' => $class->id,
            'day_of_week' => 7,
            'start_time' => '09:00',
            'end_time' => '10:30',
            'room' => 'Lab 1',
            'teacher_id' => $teacher->id,
            'enrollment_ids' => $enrollmentIds,
            'is_active' => '1',
        ], $overrides);
    }

    public function test_sunday_group_slot_creates_one_row_per_student(): void
    {
        $admin = $this->admin();
        $teacher = $this->teacher();
        $class = $this->activeClass(5, $teacher);
        $a = $this->enroll($class);
        $b = $this->enroll($class);

        $this->actingAs($admin)->post(route('admin.schedules.store'), $this->payload($class, $teacher, [$a->id, $b->id]))
            ->assertSessionHasNoErrors();

        $rows = Schedule::where('class_id', $class->id)->get();
        $this->assertCount(2, $rows);
        $this->assertSame([7], $rows->pluck('day_of_week')->unique()->values()->all());
        $this->assertTrue($rows->every(fn ($row) => $row->teacherAssignment->teacher_id === $teacher->id));
    }

    public function test_teacher_and_room_clashes_are_rejected_but_online_is_not(): void
    {
        $admin = $this->admin();
        $teacher = $this->teacher();
        $classA = $this->activeClass(5, $teacher);
        $classB = $this->activeClass(5, $teacher);
        $other = $this->teacher();
        $classC = $this->activeClass(5, $other);
        $a = $this->enroll($classA);
        $b = $this->enroll($classB);
        $c = $this->enroll($classC);

        $this->actingAs($admin)->post(route('admin.schedules.store'), $this->payload($classA, $teacher, [$a->id]))->assertSessionHasNoErrors();

        // Same teacher, overlapping time.
        $this->actingAs($admin)->post(route('admin.schedules.store'), $this->payload($classB, $teacher, [$b->id], [
            'start_time' => '10:00', 'end_time' => '11:00', 'room' => 'Lab 2',
        ]))->assertSessionHasErrors('teacher_id');

        // Other teacher, same physical room.
        $this->actingAs($admin)->post(route('admin.schedules.store'), $this->payload($classC, $other, [$c->id], [
            'start_time' => '09:30', 'end_time' => '10:00', 'room' => 'lab 1',
        ]))->assertSessionHasErrors('room');

        // Edge: back-to-back is fine, and "Online" never clashes.
        $this->actingAs($admin)->post(route('admin.schedules.store'), $this->payload($classC, $other, [$c->id], [
            'start_time' => '10:30', 'end_time' => '11:30', 'room' => 'Lab 1',
        ]))->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.schedules.store'), $this->payload($classC, $other, [$c->id], [
            'day_of_week' => 3, 'room' => 'Online',
        ]))->assertSessionHasNoErrors();
    }

    public function test_student_double_booking_and_foreign_enrollments_are_rejected(): void
    {
        $admin = $this->admin();
        $teacherA = $this->teacher();
        $teacherB = $this->teacher();
        $classA = $this->activeClass(5, $teacherA);
        $classB = $this->activeClass(5, $teacherB);
        $student = $this->student();
        $inA = $this->enroll($classA, $student);
        $inB = $this->enroll($classB, $student);

        $this->actingAs($admin)->post(route('admin.schedules.store'), $this->payload($classA, $teacherA, [$inA->id], ['room' => 'Online']))->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.schedules.store'), $this->payload($classB, $teacherB, [$inB->id], ['room' => 'Online']))
            ->assertSessionHasErrors('enrollment_ids');

        // An enrollment of another class cannot be scheduled here.
        $this->actingAs($admin)->post(route('admin.schedules.store'), $this->payload($classB, $teacherB, [$inA->id], ['day_of_week' => 1]))
            ->assertSessionHasErrors('enrollment_ids.0');
        // A teacher who does not teach the class is rejected.
        $this->actingAs($admin)->post(route('admin.schedules.store'), $this->payload($classB, $teacherA, [$inB->id], ['day_of_week' => 1]))
            ->assertSessionHasErrors('teacher_id');
    }

    public function test_updating_a_slot_moves_all_rows_and_syncs_students(): void
    {
        $admin = $this->admin();
        $teacher = $this->teacher();
        $class = $this->activeClass(5, $teacher);
        $a = $this->enroll($class);
        $b = $this->enroll($class);
        $c = $this->enroll($class);

        $this->actingAs($admin)->post(route('admin.schedules.store'), $this->payload($class, $teacher, [$a->id, $b->id]));
        $slot = Schedule::where('class_id', $class->id)->orderBy('id')->first();

        $this->actingAs($admin)->put(route('admin.schedules.update', $slot), $this->payload($class, $teacher, [$a->id, $c->id], [
            'day_of_week' => 2, 'start_time' => '15:00', 'end_time' => '16:30',
        ]))->assertSessionHasNoErrors();

        $rows = Schedule::where('class_id', $class->id)->get();
        $this->assertEqualsCanonicalizing([$a->id, $c->id], $rows->pluck('enrollment_id')->all());
        $this->assertSame([2], $rows->pluck('day_of_week')->unique()->values()->all());
        $this->assertSame(['15:00:00'], $rows->map(fn ($r) => $r->getRawOriginal('start_time'))->unique()->values()->all());
    }

    public function test_pages_render_and_slot_can_be_toggled_and_deleted(): void
    {
        $super = $this->superadmin();
        $teacher = $this->teacher();
        $class = $this->activeClass(5, $teacher);
        $a = $this->enroll($class);
        $this->actingAs($super)->post(route('admin.schedules.store'), $this->payload($class, $teacher, [$a->id]));
        $slot = Schedule::where('class_id', $class->id)->firstOrFail();

        $this->actingAs($super)->get(route('admin.schedules.index'))->assertOk()->assertSee($class->name);
        $this->actingAs($super)->get(route('admin.schedules.index', ['view' => 'list']))->assertOk()->assertSee($class->name);
        $this->actingAs($super)->get(route('admin.schedules.create'))->assertOk();
        $this->actingAs($super)->get(route('admin.schedules.show', $slot))->assertOk();
        $this->actingAs($super)->get(route('admin.schedules.edit', $slot))->assertOk();

        $this->actingAs($super)->post(route('admin.schedules.toggle-status', $slot))->assertRedirect();
        $this->assertFalse($slot->fresh()->is_active);

        $this->actingAs($super)->delete(route('admin.schedules.destroy', $slot))->assertRedirect(route('admin.schedules.index'));
        $this->assertSame(0, Schedule::where('class_id', $class->id)->count());
    }

    public function test_schedule_data_api_is_admin_only_and_minimal(): void
    {
        $class = $this->activeClass(5);
        $enrollment = $this->enroll($class);

        $response = $this->actingAs($this->admin())->getJson(route('admin.schedule-data.students', $class))->assertOk();
        $response->assertJsonPath('data.0.enrollment_id', $enrollment->id);
        $this->assertStringNotContainsString('@', $response->getContent());
        $this->actingAs($this->admin())->getJson(route('admin.schedule-data.teachers', $class))->assertOk()->assertJsonCount(1, 'data');

        $this->actingAs($this->student())->getJson(route('admin.schedule-data.students', $class))->assertForbidden();
        $this->actingAs($this->parentUser())->getJson(route('admin.schedule-data.teachers', $class))->assertForbidden();
        $this->actingAs($this->student())->get('/api/schedule-data/enrollments/'.$class->id)->assertNotFound();
    }
}
