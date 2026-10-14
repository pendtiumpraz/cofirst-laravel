<?php

namespace Tests\Feature\Academic;

use App\Models\ClassProgress;
use App\Models\ClassReport;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\User;
use App\Services\Academic\ClassReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Berita acara: one form per meeting creates a report per present student, writes
 * class_progress, and is protected against IDOR / mass assignment / late edits.
 */
class ClassReportFlowTest extends TestCase
{
    use AcademicFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    /** Valid payload for meeting 1 of the fixture class. */
    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'class_id' => $this->class->id,
            'report_date' => now('Asia/Jakarta')->subDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:30',
            'meeting_number' => 1,
            'learning_concept' => 'Variabel dan tipe data',
            'students' => [
                0 => ['student_id' => $this->student->id, 'include' => '1', 'notes_recommendations' => 'Bagus sekali', 'apply_application' => 'Latihan selesai'],
            ],
        ], $overrides);
    }

    public function test_teacher_reports_a_meeting_and_progress_is_completed(): void
    {
        $this->actingAs($this->teacher)->get(route('class-reports.create', ['class_id' => $this->class->id]))
            ->assertOk()->assertSee('Siswa Satu')->assertSee('Pertemuan 1 — Pertemuan nomor 1');

        $this->actingAs($this->teacher)->post(route('class-reports.store'), $this->payload())
            ->assertRedirect(route('class-reports.index'))
            ->assertSessionHasNoErrors();

        $report = ClassReport::firstOrFail();
        $this->assertSame($this->teacher->id, $report->teacher_id);
        $this->assertSame($this->curriculum->id, $report->curriculum_id);
        $this->assertSame($this->meetings[1]->id, $report->syllabus_id);
        $this->assertDatabaseHas('class_progress', [
            'student_id' => $this->student->id, 'class_id' => $this->class->id, 'meeting_number' => 1, 'status' => 'completed',
        ]);

        // The material of meeting 1 is now open for the student.
        $this->actingAs($this->student)->get(route('student.materials.show', $this->materialOf(1)))->assertOk();
    }

    public function test_unticked_students_get_no_report(): void
    {
        $absent = User::factory()->student()->create();
        Enrollment::factory()->create(['class_id' => $this->class->id, 'course_id' => $this->course->id, 'student_id' => $absent->id]);

        $this->actingAs($this->teacher)->post(route('class-reports.store'), $this->payload([
            'students' => [1 => ['student_id' => $absent->id]],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(1, ClassReport::count());
        $this->assertSame($this->student->id, ClassReport::first()->student_id);
    }

    public function test_teacher_cannot_report_a_class_they_do_not_teach(): void
    {
        $foreign = $this->foreignClass();

        $this->actingAs($this->teacher)->post(route('class-reports.store'), $this->payload(['class_id' => $foreign->id]))
            ->assertSessionHasErrors('class_id');
        $this->assertSame(0, ClassReport::count());
    }

    public function test_student_outside_the_class_is_rejected(): void
    {
        $stranger = User::factory()->student()->create();

        $this->actingAs($this->teacher)->post(route('class-reports.store'), $this->payload([
            'students' => [0 => ['student_id' => $stranger->id, 'include' => '1']],
        ]))->assertSessionHasErrors('students');
    }

    public function test_material_must_belong_to_the_chosen_meeting_and_link_host_is_restricted(): void
    {
        $this->actingAs($this->teacher)->post(route('class-reports.store'), $this->payload([
            'material_id' => $this->materialOf(2)->id,
            'learning_media_link' => 'https://example.com/foto-anak',
        ]))->assertSessionHasErrors(['material_id', 'learning_media_link']);
    }

    public function test_future_dates_are_rejected(): void
    {
        $this->actingAs($this->teacher)->post(route('class-reports.store'), $this->payload([
            'report_date' => now()->addDays(3)->toDateString(),
        ]))->assertSessionHasErrors('report_date');
    }

    public function test_update_cannot_move_a_report_to_another_student_or_teacher(): void
    {
        $report = ClassReport::factory()->create([
            'teacher_id' => $this->teacher->id, 'class_id' => $this->class->id, 'student_id' => $this->student->id,
            'report_date' => now()->subDay()->toDateString(), 'meeting_number' => 1, 'syllabus_id' => $this->meetings[1]->id,
        ]);
        $other = User::factory()->student()->create();

        $this->actingAs($this->teacher)->put(route('class-reports.update', $report), [
            'report_date' => now()->subDay()->toDateString(),
            'start_time' => '09:00', 'end_time' => '10:30', 'meeting_number' => 1,
            'learning_concept' => 'Diperbarui',
            'student_id' => $other->id, 'teacher_id' => $this->otherTeacher->id, 'class_id' => 999,
        ])->assertRedirect(route('class-reports.show', $report));

        $report->refresh();
        $this->assertSame('Diperbarui', $report->learning_concept);
        $this->assertSame($this->student->id, $report->student_id);
        $this->assertSame($this->teacher->id, $report->teacher_id);
        $this->assertSame($this->class->id, $report->class_id);
    }

    public function test_edit_window_is_enforced(): void
    {
        $old = ClassReport::factory()->create([
            'teacher_id' => $this->teacher->id, 'class_id' => $this->class->id, 'student_id' => $this->student->id,
            'report_date' => now()->subDays(30)->toDateString(),
        ]);

        $this->actingAs($this->teacher)->get(route('class-reports.edit', $old))
            ->assertRedirect(route('class-reports.show', $old))->assertSessionHas('error');
        $this->actingAs($this->teacher)->put(route('class-reports.update', $old), ['learning_concept' => 'x'])->assertForbidden();
        $this->actingAs($this->teacher)->delete(route('class-reports.destroy', $old))->assertForbidden();
    }

    public function test_deleting_a_report_reverts_the_meeting_progress(): void
    {
        $this->actingAs($this->teacher)->post(route('class-reports.store'), $this->payload())->assertSessionHasNoErrors();
        $report = ClassReport::firstOrFail();

        $this->actingAs($this->teacher)->delete(route('class-reports.destroy', $report))->assertRedirect(route('class-reports.index'));

        $this->assertSame('not_started', ClassProgress::where('student_id', $this->student->id)->where('meeting_number', 1)->value('status'));
    }

    public function test_other_teacher_cannot_read_and_admin_reads_but_cannot_edit_by_default(): void
    {
        $report = ClassReport::factory()->create([
            'teacher_id' => $this->teacher->id, 'class_id' => $this->class->id, 'student_id' => $this->student->id,
            'report_date' => now()->toDateString(), 'follow_up_notes' => 'Catatan internal guru',
        ]);

        $this->actingAs($this->otherTeacher)->get(route('class-reports.show', $report))->assertForbidden();
        $this->actingAs($this->admin)->get(route('class-reports.show', $report))->assertOk()->assertSee('Catatan internal guru');
        $this->actingAs($this->admin)->get(route('class-reports.edit', $report))->assertForbidden();

        setting()->set('academic.admin_can_edit_class_reports', true);
        $this->actingAs($this->admin)->get(route('class-reports.edit', $report))->assertOk();
    }

    public function test_json_helper_is_authorized_and_minimal(): void
    {
        $foreign = $this->foreignClass();

        $this->actingAs($this->teacher)->getJson(route('class-reports.get-schedules-and-students', ['class_id' => $foreign->id]))
            ->assertForbidden();

        $response = $this->actingAs($this->teacher)->getJson(route('class-reports.get-schedules-and-students', ['class_id' => $this->class->id]))
            ->assertOk()
            ->assertJsonPath('students.0.name', 'Siswa Satu');
        $this->assertArrayNotHasKey('email', $response->json('students.0'));
    }

    public function test_class_without_curriculum_can_still_be_reported(): void
    {
        $this->class->update(['curriculum_id' => null]);

        $this->actingAs($this->teacher)->get(route('class-reports.create', ['class_id' => $this->class->id]))
            ->assertOk()->assertSee('Kelas belum memiliki kurikulum');

        $this->actingAs($this->teacher)->post(route('class-reports.store'), $this->payload(['meeting_number' => 5]))
            ->assertSessionHasNoErrors();

        $this->assertNull(ClassReport::first()->syllabus_id);
        $this->assertSame(0, ClassProgress::count());
    }

    public function test_documentation_link_requires_parental_consent_when_the_consent_module_is_present(): void
    {
        if (! class_exists(\App\Services\Privacy\ConsentService::class) || ! class_exists(\Database\Seeders\PrivacySeeder::class)) {
            $this->markTestSkipped('Modul persetujuan belum terpasang.');
        }

        $this->seed(\Database\Seeders\PrivacySeeder::class);
        $link = 'https://drive.google.com/drive/folders/abc';

        $this->actingAs($this->teacher)->post(route('class-reports.store'), $this->payload(['learning_media_link' => $link]))
            ->assertSessionHasErrors('learning_media_link');

        app(\App\Services\Privacy\ConsentService::class)->grant($this->student, 'child_data_processing', 'parental', $this->parent);

        $this->actingAs($this->teacher)->post(route('class-reports.store'), $this->payload(['learning_media_link' => $link]))
            ->assertSessionHasNoErrors();
        $this->assertSame($link, ClassReport::first()->learning_media_link);
    }

    public function test_pending_meetings_follow_the_weekly_schedule(): void
    {
        $yesterday = now('Asia/Jakarta')->subDay();
        Schedule::create([
            'class_id' => $this->class->id, 'day_of_week' => $yesterday->dayOfWeekIso,
            'start_time' => '09:00', 'end_time' => '10:30', 'is_active' => true,
        ]);

        $pending = app(ClassReportService::class)->pendingSessions($this->teacher);
        $this->assertTrue($pending->contains(fn ($p) => $p['date']->toDateString() === $yesterday->toDateString()));

        $this->actingAs($this->teacher)->post(route('class-reports.store'), $this->payload([
            'report_date' => $yesterday->toDateString(),
        ]))->assertSessionHasNoErrors();

        request()->attributes->replace([]);
        $pending = app(ClassReportService::class)->pendingSessions($this->teacher);
        $this->assertFalse($pending->contains(fn ($p) => $p['date']->toDateString() === $yesterday->toDateString()));

        $this->actingAs($this->teacher)->get(route('class-reports.index'))->assertOk();
    }
}
