<?php

namespace Tests\Feature\Academic;

use App\Models\ClassProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Teacher curriculum/syllabus pages follow class_names.curriculum_id, and the
 * per-class meeting progress can be set for the whole class.
 */
class TeacherCurriculumTest extends TestCase
{
    use AcademicFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    public function test_teacher_sees_curricula_of_their_classes(): void
    {
        $this->actingAs($this->teacher)->get(route('teacher.curriculums.index'))->assertOk()->assertSee('Python Dasar Reguler');
        $this->actingAs($this->teacher)->get(route('teacher.curriculums.show', $this->curriculum))->assertOk()->assertSee('Pertemuan nomor 2')->assertSee('Kelas Python A');
        $this->actingAs($this->teacher)->get(route('teacher.curriculums.syllabuses', $this->curriculum))->assertOk();
        $this->actingAs($this->teacher)->get(route('teacher.curriculums.materials', [$this->curriculum, 'type' => 'document']))->assertOk();
        $this->actingAs($this->teacher)->get(route('teacher.syllabuses.index'))->assertOk()->assertSee('Pertemuan nomor 1');
        $this->actingAs($this->teacher)->get(route('teacher.syllabuses.show', $this->meetings[2]))->assertOk()->assertSee('Materi 2.1');
    }

    public function test_teacher_without_classes_gets_empty_state_and_no_access(): void
    {
        $this->actingAs($this->otherTeacher)->get(route('teacher.curriculums.index'))->assertOk()->assertSee('Belum ada kurikulum untuk kelas Anda.');
        $this->actingAs($this->otherTeacher)->get(route('teacher.curriculums.show', $this->curriculum))->assertForbidden();
        $this->actingAs($this->otherTeacher)->get(route('teacher.syllabuses.show', $this->meetings[1]))->assertForbidden();
    }

    public function test_teacher_marks_a_meeting_for_the_whole_class(): void
    {
        $this->actingAs($this->teacher)->get(route('teacher.curriculums.class-progress', $this->class))->assertOk()->assertSee('Pertemuan nomor 1');

        $this->actingAs($this->teacher)->post(route('teacher.curriculums.class-progress.update', $this->class), [
            'meeting_number' => 2, 'status' => 'in_progress',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame('in_progress', ClassProgress::where('student_id', $this->student->id)->where('meeting_number', 2)->value('status'));
    }

    public function test_marking_progress_requires_teaching_the_class_and_a_valid_status(): void
    {
        $this->actingAs($this->otherTeacher)->post(route('teacher.curriculums.class-progress.update', $this->class), [
            'meeting_number' => 1, 'status' => 'completed',
        ])->assertForbidden();

        $this->actingAs($this->teacher)->post(route('teacher.curriculums.class-progress.update', $this->class), [
            'meeting_number' => 1, 'status' => 'done',
        ])->assertSessionHasErrors('status');

        $this->actingAs($this->teacher)->post(route('teacher.curriculums.class-progress.update', $this->class), [
            'meeting_number' => 9, 'status' => 'completed',
        ])->assertSessionHas('error');
    }
}
