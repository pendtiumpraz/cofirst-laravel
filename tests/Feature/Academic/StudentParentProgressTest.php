<?php

namespace Tests\Feature\Academic;

use App\Models\ClassName;
use App\Models\ClassReport;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Student curriculum/progress pages and the parent progress pages: real progress
 * figures, ownership (own child only) and the report-visibility setting.
 */
class StudentParentProgressTest extends TestCase
{
    use AcademicFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    public function test_student_curriculum_and_progress_pages_show_real_progress(): void
    {
        $this->completeMeeting(1);

        $this->actingAs($this->student)->get(route('student.curriculum.index'))
            ->assertOk()->assertSee('Kelas Python A')->assertSee('1 dari 3 pertemuan selesai');
        $this->actingAs($this->student)->get(route('student.curriculum.show', $this->class))
            ->assertOk()->assertSee('Pertemuan nomor 3')->assertSee('Materi 2.1');
        $this->actingAs($this->student)->get(route('student.progress.class', $this->class))
            ->assertOk()->assertSee('1 / 3');
    }

    public function test_class_without_curriculum_shows_an_empty_state(): void
    {
        $class = ClassName::factory()->active()->withTeacher($this->teacher)->create(['course_id' => $this->course->id, 'curriculum_id' => null]);
        Enrollment::factory()->create(['class_id' => $class->id, 'course_id' => $this->course->id, 'student_id' => $this->student->id]);

        $this->actingAs($this->student)->get(route('student.curriculum.show', $class))
            ->assertOk()->assertSee('Kurikulum kelas ini belum ditetapkan.');
        $this->actingAs($this->parent)->get(route('parent.curriculum.child-progress', [$this->student, $class]))->assertOk();
    }

    public function test_student_cannot_open_a_class_they_are_not_enrolled_in(): void
    {
        $this->actingAs($this->student)->get(route('student.curriculum.show', $this->foreignClass()))->assertForbidden();
        $this->actingAs($this->student)->get(route('student.progress.class', $this->foreignClass()))->assertForbidden();
    }

    public function test_parent_sees_progress_of_their_child(): void
    {
        $this->completeMeeting(1);
        $this->completeMeeting(2);

        $this->actingAs($this->parent)->get(route('parent.progress.index'))->assertOk()->assertSee('Siswa Satu')->assertSee('2/3 pertemuan');
        $this->actingAs($this->parent)->get(route('parent.progress.child', $this->student))->assertOk()->assertSee('Kelas Python A');
        $this->actingAs($this->parent)->get(route('parent.curriculum.index'))->assertOk()->assertSee('Python Dasar Reguler');
        $this->actingAs($this->parent)->get(route('parent.curriculum.child-progress', [$this->student, $this->class]))->assertOk()->assertSee('Pertemuan nomor 2');
        $this->actingAs($this->parent)->get(route('parent.progress.child-materials', [$this->student, $this->class]))->assertOk()->assertSee('Materi 2.2');
        $this->actingAs($this->parent)->getJson(route('parent.progress.child-summary', $this->student))
            ->assertOk()->assertJsonPath('classes.0.completed_meetings', 2);
    }

    public function test_parent_cannot_view_another_child(): void
    {
        $other = User::factory()->student()->create();

        $this->actingAs($this->parent)->get(route('parent.progress.child', $other))->assertForbidden();
        $this->actingAs($this->parent)->get(route('parent.curriculum.child-progress', [$other, $this->class]))->assertForbidden();
        $this->actingAs($this->parent)->get(route('parent.progress.meetings', $other))->assertForbidden();
    }

    public function test_parent_reads_meeting_notes_without_internal_notes(): void
    {
        $report = ClassReport::factory()->create([
            'teacher_id' => $this->teacher->id, 'class_id' => $this->class->id, 'student_id' => $this->student->id,
            'notes_recommendations' => 'Pertahankan semangat', 'follow_up_notes' => 'Rahasia guru',
        ]);

        $this->actingAs($this->parent)->get(route('parent.progress.meetings', $this->student))->assertOk()->assertSee('Pertemuan 1');
        $this->actingAs($this->parent)->get(route('parent.progress.meeting', [$this->student, $report]))
            ->assertOk()->assertSee('Pertahankan semangat')->assertDontSee('Rahasia guru');

        $this->actingAs($this->student)->get(route('student.reports.meeting', $report))
            ->assertOk()->assertSee('Pertahankan semangat')->assertDontSee('Rahasia guru');
    }

    public function test_meeting_notes_are_hidden_from_parents_when_the_setting_is_off(): void
    {
        $report = ClassReport::factory()->create([
            'teacher_id' => $this->teacher->id, 'class_id' => $this->class->id, 'student_id' => $this->student->id,
        ]);
        setting()->set('academic.reports_visible_to_parents', false);

        $this->actingAs($this->parent)->get(route('parent.progress.meetings', $this->student))
            ->assertOk()->assertSee('Catatan pertemuan belum dibagikan');
        $this->actingAs($this->parent)->get(route('parent.progress.meeting', [$this->student, $report]))->assertForbidden();
    }
}
