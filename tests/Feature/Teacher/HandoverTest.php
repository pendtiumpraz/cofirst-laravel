<?php

namespace Tests\Feature\Teacher;

use App\Models\Schedule;
use App\Models\TeacherHandover;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Teacher\Concerns\BuildsTeachingData;
use Tests\TestCase;

/**
 * Serah terima kelas: request, accept/decline, cancel, complete and the
 * scheduled clean-up of finished temporary handovers.
 */
class HandoverTest extends TestCase
{
    use BuildsTeachingData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->freezeWednesdayMorning();
    }

    private function payload(int $classId, int $toTeacherId, array $overrides = []): array
    {
        return $overrides + [
            'class_id' => $classId,
            'to_teacher_id' => $toTeacherId,
            'type' => 'temporary',
            'effective_from' => '2026-10-17',
            'effective_until' => '2026-10-24',
            'reason' => 'Mengikuti pelatihan guru di Bandung selama satu minggu.',
            'handover_notes' => 'Pertemuan terakhir membahas perulangan.',
            'last_meeting_number' => 4,
        ];
    }

    public function test_teacher_requests_a_temporary_handover(): void
    {
        $from = $this->makeTeacher();
        $to = $this->makeTeacher(['name' => 'Sari Wulandari']);
        $class = $this->makeClass($from, 1);

        $this->actingAs($from)->get(route('teacher.handovers.create', ['class_id' => $class->id]))
            ->assertOk()
            ->assertSee('Sari Wulandari')
            ->assertSee($class->name);

        $response = $this->actingAs($from)->post(route('teacher.handovers.store'), $this->payload($class->id, $to->id));

        $handover = TeacherHandover::firstOrFail();
        $response->assertRedirect(route('teacher.handovers.show', $handover));
        $this->assertSame('pending', $handover->status);
        $this->assertSame($from->id, (int) $handover->from_teacher_id);
        $this->assertSame($from->id, (int) $handover->requested_by);
        $this->assertNull($handover->student_id);

        $this->actingAs($to)->get(route('teacher.handovers.index'))
            ->assertOk()
            ->assertSee($class->name)
            ->assertSee('Menunggu jawaban Anda');
    }

    public function test_request_validation_rules(): void
    {
        $from = $this->makeTeacher();
        $coTeacher = $this->makeTeacher();
        $class = $this->makeClass($from, 1);
        $class->syncTeachers([$from->id, $coTeacher->id]);
        $foreign = $this->makeClass($this->makeTeacher(), 1);
        $student = User::factory()->student()->create();
        $free = $this->makeTeacher();

        // Self, a co-teacher of the class, and a non-teacher are refused.
        $this->actingAs($from)->post(route('teacher.handovers.store'), $this->payload($class->id, $from->id))->assertSessionHasErrors('to_teacher_id');
        $this->actingAs($from)->post(route('teacher.handovers.store'), $this->payload($class->id, $coTeacher->id))->assertSessionHasErrors('to_teacher_id');
        $this->actingAs($from)->post(route('teacher.handovers.store'), $this->payload($class->id, $student->id))->assertSessionHasErrors('to_teacher_id');
        // A class the teacher does not teach.
        $this->actingAs($from)->post(route('teacher.handovers.store'), $this->payload($foreign->id, $free->id))->assertSessionHasErrors('class_id');
        // Temporary needs an end date; dates in the past are refused.
        $this->actingAs($from)->post(route('teacher.handovers.store'), $this->payload($class->id, $free->id, ['effective_until' => '']))->assertSessionHasErrors('effective_until');
        $this->actingAs($from)->post(route('teacher.handovers.store'), $this->payload($class->id, $free->id, ['effective_from' => '2026-10-01']))->assertSessionHasErrors('effective_from');

        $this->assertSame(0, TeacherHandover::count());

        // Only one pending request per class.
        $this->actingAs($from)->post(route('teacher.handovers.store'), $this->payload($class->id, $free->id))->assertSessionHasNoErrors();
        $this->actingAs($from)->post(route('teacher.handovers.store'), $this->payload($class->id, $this->makeTeacher()->id))->assertSessionHasErrors('class_id');
        $this->assertSame(1, TeacherHandover::count());
    }

    public function test_accepting_a_temporary_handover_adds_the_substitute_until_completed(): void
    {
        $from = $this->makeTeacher();
        $to = $this->makeTeacher();
        $class = $this->makeClass($from, 1, 3, ['name' => 'Kelas Pengganti']);
        $handover = TeacherHandover::factory()->create(['class_id' => $class->id, 'from_teacher_id' => $from->id, 'to_teacher_id' => $to->id, 'requested_by' => $from->id]);

        $this->actingAs($to)->post(route('teacher.handovers.approve', $handover))
            ->assertRedirect(route('teacher.handovers.show', $handover));

        $handover->refresh();
        $this->assertSame('approved', $handover->status);
        $this->assertSame($to->id, (int) $handover->approved_by);
        $this->assertTrue($to->teachesClass($class));
        $this->assertTrue($from->teachesClass($class));
        $this->actingAs($to)->get(route('teacher.schedules.index'))->assertSee('Kelas Pengganti');

        $this->actingAs($from)->post(route('teacher.handovers.complete', $handover))->assertSessionHasNoErrors();

        $this->assertSame('completed', $handover->refresh()->status);
        $this->assertFalse($to->teachesClass($class));
        $this->assertTrue($from->teachesClass($class));
    }

    public function test_accepting_a_permanent_handover_moves_the_class(): void
    {
        $from = $this->makeTeacher();
        $to = $this->makeTeacher();
        $class = $this->makeClass($from, 1);
        $handover = TeacherHandover::factory()->create([
            'class_id' => $class->id, 'from_teacher_id' => $from->id, 'to_teacher_id' => $to->id, 'requested_by' => $from->id,
            'type' => 'permanent', 'effective_until' => null,
        ]);

        $this->actingAs($to)->post(route('teacher.handovers.approve', $handover))->assertSessionHasNoErrors();

        $this->assertSame('completed', $handover->refresh()->status);
        $this->assertTrue($to->teachesClass($class));
        $this->assertFalse($from->teachesClass($class));
        // Schedules now point at the new teacher's assignment.
        $this->assertSame([$to->id], Schedule::where('class_id', $class->id)->with('teacherAssignment')->get()
            ->map(fn ($s) => (int) $s->teacherAssignment->teacher_id)->unique()->values()->all());
        $this->actingAs($from)->get(route('teacher.class-detail', $class))->assertForbidden();
    }

    public function test_decline_and_cancel(): void
    {
        $from = $this->makeTeacher();
        $to = $this->makeTeacher();
        $class = $this->makeClass($from, 1);
        $declined = TeacherHandover::factory()->create(['class_id' => $class->id, 'from_teacher_id' => $from->id, 'to_teacher_id' => $to->id, 'requested_by' => $from->id]);

        $this->actingAs($to)->post(route('teacher.handovers.reject', $declined))->assertSessionHasNoErrors();
        $this->assertSame('rejected', $declined->refresh()->status);
        $this->assertFalse($to->teachesClass($class));

        $cancelled = TeacherHandover::factory()->create(['class_id' => $class->id, 'from_teacher_id' => $from->id, 'to_teacher_id' => $to->id, 'requested_by' => $from->id]);
        $this->actingAs($from)->delete(route('teacher.handovers.destroy', $cancelled))
            ->assertRedirect(route('teacher.handovers.index', ['tab' => 'riwayat']));
        $this->assertSame('cancelled', $cancelled->refresh()->status);
        $this->assertDatabaseHas('teacher_handovers', ['id' => $cancelled->id]);

        // A decided request cannot be answered again.
        $this->actingAs($to)->post(route('teacher.handovers.approve', $declined))->assertForbidden();
    }

    public function test_only_the_teachers_involved_may_see_or_act(): void
    {
        $from = $this->makeTeacher();
        $to = $this->makeTeacher();
        $stranger = $this->makeTeacher();
        $class = $this->makeClass($from, 1);
        $handover = TeacherHandover::factory()->create(['class_id' => $class->id, 'from_teacher_id' => $from->id, 'to_teacher_id' => $to->id, 'requested_by' => $from->id]);

        $this->actingAs($stranger)->get(route('teacher.handovers.show', $handover))->assertForbidden();
        $this->actingAs($stranger)->post(route('teacher.handovers.approve', $handover))->assertForbidden();
        // The requester cannot accept their own request; the receiver cannot edit or cancel it.
        $this->actingAs($from)->post(route('teacher.handovers.approve', $handover))->assertForbidden();
        $this->actingAs($to)->get(route('teacher.handovers.edit', $handover))->assertForbidden();
        $this->actingAs($to)->delete(route('teacher.handovers.destroy', $handover))->assertForbidden();

        $this->assertSame('pending', $handover->refresh()->status);
        $this->assertFalse($stranger->teachesClass($class));

        $this->actingAs($from)->get(route('teacher.handovers.show', $handover))->assertOk()->assertSee('Ubah');
        $this->actingAs($to)->get(route('teacher.handovers.show', $handover))->assertOk()->assertSee('Terima');
    }

    public function test_requester_can_edit_a_pending_request(): void
    {
        $from = $this->makeTeacher();
        $to = $this->makeTeacher();
        $class = $this->makeClass($from, 1);
        $handover = TeacherHandover::factory()->create(['class_id' => $class->id, 'from_teacher_id' => $from->id, 'to_teacher_id' => $to->id, 'requested_by' => $from->id]);

        $this->actingAs($from)->get(route('teacher.handovers.edit', $handover))->assertOk();
        $this->actingAs($from)
            ->put(route('teacher.handovers.update', $handover), $this->payload($class->id, $to->id, ['type' => 'permanent', 'reason' => 'Pindah tugas ke cabang lain mulai bulan depan.']))
            ->assertRedirect(route('teacher.handovers.show', $handover));

        $handover->refresh();
        $this->assertSame('permanent', $handover->type);
        $this->assertNull($handover->effective_until);
    }

    public function test_approval_fails_when_the_class_has_ended(): void
    {
        $from = $this->makeTeacher();
        $to = $this->makeTeacher();
        $class = $this->makeClass($from, 1);
        $handover = TeacherHandover::factory()->create(['class_id' => $class->id, 'from_teacher_id' => $from->id, 'to_teacher_id' => $to->id, 'requested_by' => $from->id]);
        $class->update(['status' => 'completed']);

        $this->actingAs($to)->post(route('teacher.handovers.approve', $handover))->assertSessionHasErrors('status');

        $this->assertSame('pending', $handover->refresh()->status);
        $this->assertFalse($to->teachesClass($class));
    }

    public function test_expired_temporary_handovers_are_completed_by_the_scheduler(): void
    {
        $from = $this->makeTeacher();
        $to = $this->makeTeacher();
        $class = $this->makeClass($from, 1);
        $class->syncTeachers([$from->id, $to->id]);
        $expired = TeacherHandover::factory()->create([
            'class_id' => $class->id, 'from_teacher_id' => $from->id, 'to_teacher_id' => $to->id, 'requested_by' => $from->id,
            'status' => 'approved', 'effective_from' => '2026-10-01', 'effective_until' => '2026-10-10',
        ]);
        $running = TeacherHandover::factory()->create([
            'class_id' => $class->id, 'from_teacher_id' => $from->id, 'to_teacher_id' => $to->id, 'requested_by' => $from->id,
            'status' => 'approved', 'effective_from' => '2026-10-10', 'effective_until' => '2026-10-20',
        ]);

        $this->artisan('teacher:complete-handovers')->assertSuccessful();

        $this->assertSame('completed', $expired->refresh()->status);
        $this->assertSame('approved', $running->refresh()->status);
        $this->assertFalse($to->teachesClass($class));
        $this->assertTrue($from->teachesClass($class));
    }
}
