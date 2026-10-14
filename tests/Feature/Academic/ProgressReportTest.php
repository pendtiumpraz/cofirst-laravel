<?php

namespace Tests\Feature\Academic;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Laporan perkembangan: teacher CRUD (previously SQL errors), enrollment checks,
 * student view without fabricated data, admin hub and CSV export.
 */
class ProgressReportTest extends TestCase
{
    use AcademicFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'class_id' => $this->class->id,
            'student_id' => $this->student->id,
            'type' => 'monthly',
            'title' => 'Laporan bulan Oktober',
            'report_date' => now()->subDay()->toDateString(),
            'progress_percentage' => 70,
            'content' => 'Sudah menguasai variabel.',
            'recommendations' => 'Latihan perulangan.',
        ], $overrides);
    }

    public function test_teacher_can_write_a_progress_report(): void
    {
        $this->actingAs($this->teacher)->get(route('teacher.reports.create'))->assertOk()->assertSee('Kelas Python A');

        $this->actingAs($this->teacher)->post(route('teacher.reports.store'), $this->payload())->assertRedirect();

        $report = Report::firstOrFail();
        $this->assertSame($this->teacher->id, $report->teacher_id);
        $this->assertSame('monthly', $report->type);

        $this->actingAs($this->teacher)->get(route('teacher.reports.index'))->assertOk()->assertSee('Laporan bulan Oktober');
        $this->actingAs($this->teacher)->get(route('teacher.reports.show', $report))->assertOk()->assertSee('Latihan perulangan.');
        $this->actingAs($this->teacher)->get(route('teacher.reports.edit', $report))->assertOk();
        $this->actingAs($this->teacher)->get(route('teacher.reports.by-class', $this->class))->assertOk()->assertSee('Siswa Satu');
        $this->actingAs($this->teacher)->get(route('teacher.reports.by-student', $this->student))->assertOk()->assertSee('Laporan bulan Oktober');
    }

    public function test_student_must_be_enrolled_in_the_class(): void
    {
        $stranger = User::factory()->student()->create();

        $this->actingAs($this->teacher)->post(route('teacher.reports.store'), $this->payload(['student_id' => $stranger->id]))
            ->assertSessionHasErrors('student_id');

        $this->actingAs($this->teacher)->post(route('teacher.reports.store'), $this->payload(['class_id' => $this->foreignClass()->id]))
            ->assertSessionHasErrors('class_id');
    }

    public function test_update_cannot_change_student_and_other_teacher_cannot_edit(): void
    {
        $report = Report::factory()->create(['teacher_id' => $this->teacher->id, 'class_id' => $this->class->id, 'student_id' => $this->student->id]);

        $this->actingAs($this->teacher)->put(route('teacher.reports.update', $report), $this->payload())
            ->assertSessionHasErrors(['class_id', 'student_id']);

        $data = collect($this->payload(['title' => 'Judul baru']))->except(['class_id', 'student_id'])->all();
        $this->actingAs($this->teacher)->put(route('teacher.reports.update', $report), $data)->assertRedirect();
        $this->assertSame('Judul baru', $report->fresh()->title);

        $this->actingAs($this->otherTeacher)->get(route('teacher.reports.edit', $report))->assertForbidden();
        $this->actingAs($this->otherTeacher)->delete(route('teacher.reports.destroy', $report))->assertForbidden();
    }

    public function test_student_sees_only_real_reports(): void
    {
        $this->actingAs($this->student)->get(route('student.reports.index'))
            ->assertOk()
            ->assertSee('Belum ada laporan perkembangan.')
            ->assertDontSee('John Doe');

        $mine = Report::factory()->create(['teacher_id' => $this->teacher->id, 'class_id' => $this->class->id, 'student_id' => $this->student->id, 'title' => 'Laporan milikku']);
        $theirs = Report::factory()->create(['class_id' => $this->class->id, 'title' => 'Laporan orang lain']);

        $this->actingAs($this->student)->get(route('student.reports.index'))->assertSee('Laporan milikku')->assertDontSee('Laporan orang lain');
        $this->actingAs($this->student)->get(route('student.reports.show', $mine))->assertOk();
        $this->actingAs($this->student)->get(route('student.reports.show', $theirs))->assertNotFound();
        $this->actingAs($this->student)->get(route('student.reports.index', ['tab' => 'pertemuan']))->assertOk();
    }

    public function test_admin_hub_tabs_and_csv_export(): void
    {
        Report::factory()->create([
            'teacher_id' => $this->teacher->id, 'class_id' => $this->class->id, 'student_id' => $this->student->id,
            'title' => '=HYPERLINK("http://jahat")',
        ]);

        $this->actingAs($this->admin)->get(route('admin.reports.index'))->assertOk()->assertSee('Siswa Satu');
        $this->actingAs($this->admin)->get(route('admin.reports.index', ['tab' => 'berita-acara']))->assertOk()->assertSee('Kelas Python A');
        $this->actingAs($this->admin)->get(route('admin.reports.index', ['tab' => 'progres']))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.reports.show', Report::first()))->assertOk();

        $csv = $this->actingAs($this->admin)->get(route('admin.reports.export'))->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString('Siswa Satu', $csv);
    }

    public function test_non_admin_cannot_open_the_hub(): void
    {
        $this->actingAs($this->teacher)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($this->parent)->get(route('admin.reports.export'))->assertForbidden();
    }
}
