<?php

namespace Tests\Feature\Academic;

use App\Models\ClassName;
use App\Models\ClassProgress;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Syllabus;
use App\Models\User;
use Database\Seeders\RoleSeeder;

/**
 * A small, deterministic academic world for the feature tests:
 * one course with a 3-meeting curriculum (2 materials per meeting), an active
 * class taught by $teacher with $student enrolled, and $parent linked to $student.
 */
trait AcademicFixtures
{
    protected User $admin;

    protected User $teacher;

    protected User $otherTeacher;

    protected User $student;

    protected User $parent;

    protected Course $course;

    protected Curriculum $curriculum;

    protected ClassName $class;

    /** @var array<int, Syllabus> keyed by meeting number */
    protected array $meetings = [];

    /** Build the fixture world. */
    protected function setUpAcademic(): void
    {
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->admin()->create(['name' => 'Admin Akademik']);
        $this->teacher = User::factory()->teacher()->create(['name' => 'Guru Utama']);
        $this->otherTeacher = User::factory()->teacher()->create(['name' => 'Guru Lain']);
        $this->student = User::factory()->student()->create(['name' => 'Siswa Satu']);
        $this->parent = User::factory()->parent()->create(['name' => 'Orang Tua Satu']);
        $this->parent->children()->attach($this->student->id);

        $this->course = Course::factory()->create(['name' => 'Python Dasar']);
        $this->curriculum = Curriculum::factory()->create([
            'course_id' => $this->course->id,
            'title' => 'Python Dasar Reguler',
            'type' => 'regular',
        ]);

        foreach ([1, 2, 3] as $number) {
            $syllabus = Syllabus::factory()->create([
                'curriculum_id' => $this->curriculum->id,
                'meeting_number' => $number,
                'title' => "Pertemuan nomor {$number}",
            ]);
            foreach ([1, 2] as $order) {
                Material::factory()->create([
                    'syllabus_id' => $syllabus->id,
                    'title' => "Materi {$number}.{$order}",
                    'order' => $order,
                    'meeting_start' => $number,
                    'meeting_end' => $number,
                    'content' => "# Materi {$number}.{$order}\n\nIsi materi.",
                ]);
            }
            $this->meetings[$number] = $syllabus;
        }

        $this->class = ClassName::factory()->active()->withTeacher($this->teacher)->create([
            'course_id' => $this->course->id,
            'curriculum_id' => $this->curriculum->id,
            'name' => 'Kelas Python A',
        ]);

        Enrollment::factory()->create([
            'class_id' => $this->class->id,
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => Enrollment::STATUS_ACTIVE,
        ]);
    }

    /** Mark a meeting completed for the fixture student. */
    protected function completeMeeting(int $number, ?User $student = null): void
    {
        ClassProgress::updateOrCreate(
            ['student_id' => ($student ?? $this->student)->id, 'class_id' => $this->class->id, 'meeting_number' => $number],
            ['syllabus_id' => $this->meetings[$number]->id, 'status' => 'completed', 'completed_at' => now()]
        );
    }

    /** First material of a meeting. */
    protected function materialOf(int $number): Material
    {
        return $this->meetings[$number]->materials()->orderBy('order')->first();
    }

    /** A class of another teacher with its own enrolled student. */
    protected function foreignClass(): ClassName
    {
        $class = ClassName::factory()->active()->withTeacher($this->otherTeacher)->create([
            'course_id' => $this->course->id,
            'curriculum_id' => $this->curriculum->id,
            'name' => 'Kelas Lain',
        ]);
        Enrollment::factory()->create(['class_id' => $class->id, 'course_id' => $this->course->id]);

        return $class;
    }
}
