<?php

namespace Tests\Feature\Teacher;

use App\Models\Attendance;
use App\Models\ClassName;
use App\Models\TeacherHandover;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Teacher\Concerns\BuildsTeachingData;
use Tests\TestCase;

/**
 * Teacher portal pages: dashboard, my classes, class detail, my students and
 * the weekly schedule — rendering, correct numbers and ownership.
 */
class TeacherPagesTest extends TestCase
{
    use BuildsTeachingData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->freezeWednesdayMorning();
    }

    public function test_dashboard_shows_todays_sessions_and_real_numbers(): void
    {
        $teacher = $this->makeTeacher(['name' => 'Budi Santoso']);
        $today = $this->makeClass($teacher, 3, 3, ['name' => 'Scratch Rabu']);
        $this->makeClass($teacher, 2, 6, ['name' => 'Python Sabtu']);

        $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));

        $response->assertOk()
            ->assertSee('Selamat pagi')
            ->assertSee('Budi')
            ->assertSee('Scratch Rabu')
            ->assertSee('Mulai kelas')
            ->assertSee(route('teacher.attendance.take', [$today, 'date' => '2026-10-14']), false)
            ->assertSee(route('teacher.schedules.index'), false)
            ->assertDontSee(route('admin.schedules.index'), false);

        $stats = collect($response->viewData('stats'))->keyBy('label');
        $this->assertSame('1', $stats['Sesi hari ini']['value']);
        $this->assertSame('5', $stats['Siswa aktif']['value']);
        // Two 90-minute slots this week.
        $this->assertSame('3', $stats['Jam mengajar']['value']);
    }

    public function test_dashboard_lists_students_needing_attention_and_incoming_handovers(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 1);
        $studentId = $this->studentIds($class)[0];

        foreach (['2026-09-30', '2026-10-07'] as $date) {
            Attendance::factory()->absent()->create(['class_id' => $class->id, 'student_id' => $studentId, 'attendance_date' => $date]);
        }

        $other = $this->makeTeacher(['name' => 'Sari Wulandari']);
        $otherClass = $this->makeClass($other, 1);
        TeacherHandover::factory()->create([
            'class_id' => $otherClass->id, 'from_teacher_id' => $other->id, 'to_teacher_id' => $teacher->id, 'requested_by' => $other->id,
        ]);

        $this->actingAs($teacher)->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Tidak hadir 2 pertemuan terakhir')
            ->assertSee('1 permintaan serah terima menunggu jawaban Anda')
            ->assertSee('Sari Wulandari');
    }

    public function test_dashboard_renders_for_a_teacher_without_classes(): void
    {
        $this->actingAs($this->makeTeacher())->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Tidak ada sesi hari ini.');
    }

    public function test_classes_page_groups_by_status_and_shows_progress(): void
    {
        $teacher = $this->makeTeacher();
        $running = $this->makeClass($teacher, 2, 6, ['name' => 'Kelas Berjalan']);
        ClassName::factory()->completed()->withTeacher($teacher)->create(['name' => 'Kelas Lama']);
        $this->makeClass($this->makeTeacher(), 1, 3, ['name' => 'Kelas Guru Lain']);

        Attendance::factory()->create(['class_id' => $running->id, 'student_id' => $this->studentIds($running)[0], 'attendance_date' => '2026-10-10']);

        $this->actingAs($teacher)->get(route('teacher.classes'))
            ->assertOk()
            ->assertSee('Kelas Berjalan')
            ->assertDontSee('Kelas Lama')
            ->assertDontSee('Kelas Guru Lain')
            ->assertSee('Sabtu, 17 Okt');

        $this->actingAs($teacher)->get(route('teacher.classes', ['status' => 'selesai']))
            ->assertOk()
            ->assertSee('Kelas Lama')
            ->assertDontSee('Kelas Berjalan');
    }

    public function test_class_detail_shows_roster_without_contact_data(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 2);
        $student = User::find($this->studentIds($class)[0]);

        $this->actingAs($teacher)->get(route('teacher.class-detail', $class))
            ->assertOk()
            ->assertSee($class->name)
            ->assertSee($student->name)
            ->assertDontSee($student->email)
            ->assertSee('Ajukan serah terima');
    }

    public function test_class_detail_of_another_teachers_class_is_forbidden(): void
    {
        $teacher = $this->makeTeacher();
        $foreign = $this->makeClass($this->makeTeacher(), 1);

        $this->actingAs($teacher)->get(route('teacher.class-detail', $foreign))->assertForbidden();
    }

    public function test_students_page_lists_only_own_active_students_and_searches(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 0);
        $mine = User::factory()->student()->create(['name' => 'Andi Pratama']);
        $dropped = User::factory()->student()->create(['name' => 'Citra Lestari']);
        $this->enroll($class, $mine);
        $this->enroll($class, $dropped, 3, 'dropped');
        $foreign = User::factory()->student()->create(['name' => 'Dewi Anggraini']);
        $this->enroll($this->makeClass($this->makeTeacher(), 0), $foreign);

        $this->actingAs($teacher)->get(route('teacher.students.index'))
            ->assertOk()
            ->assertSee('Andi Pratama')
            ->assertDontSee('Citra Lestari')
            ->assertDontSee('Dewi Anggraini')
            ->assertDontSee($mine->email);

        $this->actingAs($teacher)->get(route('teacher.students.index', ['search' => 'zzz']))
            ->assertOk()
            ->assertSee('Tidak ada siswa yang cocok.');
    }

    public function test_students_page_ignores_a_class_filter_for_a_foreign_class(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 1);
        $foreign = $this->makeClass($this->makeTeacher(), 1);
        $foreignStudent = User::find($this->studentIds($foreign)[0]);

        $this->actingAs($teacher)->get(route('teacher.students.index', ['class' => $foreign->id]))
            ->assertOk()
            ->assertSee(User::find($this->studentIds($class)[0])->name)
            ->assertDontSee($foreignStudent->name);
    }

    public function test_schedule_page_shows_the_week_and_navigates(): void
    {
        $teacher = $this->makeTeacher();
        $this->makeClass($teacher, 1, 3, ['name' => 'Scratch Rabu']);

        $this->actingAs($teacher)->get(route('teacher.schedules.index'))
            ->assertOk()
            ->assertSee('12 Okt – 18 Okt 2026')
            ->assertSee('Scratch Rabu')
            ->assertSee('Hari ini');

        $this->actingAs($teacher)->get(route('teacher.schedules.index', ['week' => '2026-10-21']))
            ->assertOk()
            ->assertSee('19 Okt – 25 Okt 2026');

        // Invalid input falls back to the current week.
        $this->actingAs($teacher)->get(route('teacher.schedules.index', ['week' => 'bukan-tanggal']))
            ->assertOk()
            ->assertSee('12 Okt – 18 Okt 2026');
    }

    public function test_a_removed_teacher_no_longer_sees_the_class_schedule(): void
    {
        $old = $this->makeTeacher();
        $new = $this->makeTeacher();
        $class = $this->makeClass($old, 1, 3, ['name' => 'Kelas Pindahan']);

        $class->syncTeachers([$new->id]);

        $this->actingAs($old)->get(route('teacher.schedules.index'))->assertOk()->assertDontSee('Kelas Pindahan');
        $this->actingAs($new)->get(route('teacher.schedules.index'))->assertOk()->assertSee('Kelas Pindahan');
        $this->actingAs($old)->get(route('teacher.class-detail', $class))->assertForbidden();
    }

    public function test_non_teachers_cannot_open_the_teacher_portal(): void
    {
        $student = User::factory()->student()->create();

        foreach (['teacher.dashboard', 'teacher.classes', 'teacher.students.index', 'teacher.schedules.index', 'teacher.attendance.index', 'teacher.handovers.index'] as $name) {
            $this->actingAs($student)->get(route($name))->assertForbidden();
        }
    }
}
