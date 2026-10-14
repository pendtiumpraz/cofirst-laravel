<?php

namespace Tests\Feature\Teacher;

use App\Models\Attendance;
use App\Models\ClassName;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Teacher\Concerns\BuildsTeachingData;
use Tests\TestCase;

/**
 * Taking attendance for a meeting (class + date), today's view, history,
 * CSV export and gamification points.
 */
class AttendanceTest extends TestCase
{
    use BuildsTeachingData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->freezeWednesdayMorning();
    }

    /** Build a sheet payload: every student gets the same status unless overridden. */
    private function sheet(ClassName $class, string $status = 'present', array $overrides = []): array
    {
        $rows = [];
        foreach ($this->studentIds($class) as $id) {
            $rows[$id] = ($overrides[$id] ?? []) + ['status' => $status, 'notes' => ''];
        }

        return $rows;
    }

    public function test_teacher_records_attendance_and_students_earn_points_once(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 3);
        [$a, $b, $c] = $this->studentIds($class);

        $payload = $this->sheet($class, 'present', [
            $b => ['status' => 'late', 'minutes_late' => 12],
            $c => ['status' => 'excused', 'notes' => 'Izin sakit'],
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.attendance.store', $class), ['date' => '2026-10-14', 'attendance' => $payload])
            ->assertRedirect(route('teacher.attendance.take', [$class, 'date' => '2026-10-14']))
            ->assertSessionHas('success');

        $this->assertSame(3, Attendance::where('class_id', $class->id)->count());
        $late = Attendance::where('student_id', $b)->first();
        $this->assertSame('late', $late->status);
        $this->assertSame(12, $late->minutes_late);
        $this->assertSame($teacher->id, $late->teacher_id);
        $this->assertSame(1, $late->meeting_number);
        $this->assertNotNull($late->schedule_id);
        $this->assertSame('Izin sakit', Attendance::where('student_id', $c)->value('notes'));

        // Present and late earn points; excused does not.
        $this->assertSame(1, PointTransaction::where('user_id', $a)->where('reason', 'attendance')->count());
        $this->assertSame(1, PointTransaction::where('user_id', $b)->where('reason', 'attendance')->count());
        $this->assertSame(0, PointTransaction::where('user_id', $c)->where('reason', 'attendance')->count());

        // Saving the same sheet again updates rows and never pays twice.
        $this->actingAs($teacher)
            ->post(route('teacher.attendance.store', $class), ['date' => '2026-10-14', 'attendance' => $this->sheet($class)])
            ->assertSessionHasNoErrors();

        $this->assertSame(3, Attendance::where('class_id', $class->id)->count());
        $this->assertSame(1, PointTransaction::where('user_id', $a)->where('reason', 'attendance')->count());
        $this->assertSame(1, PointTransaction::where('user_id', $c)->where('reason', 'attendance')->count());
        $this->assertNull(Attendance::where('student_id', $b)->value('minutes_late'));
    }

    public function test_no_points_are_awarded_when_gamification_is_disabled(): void
    {
        setting()->set('gamification.enabled', false);
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 1);

        $this->actingAs($teacher)
            ->post(route('teacher.attendance.store', $class), ['date' => '2026-10-14', 'attendance' => $this->sheet($class)])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, PointTransaction::where('reason', 'attendance')->count());
        $this->assertSame(1, Attendance::count());
    }

    public function test_meeting_numbers_follow_previous_meetings(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 1);
        Attendance::factory()->create(['class_id' => $class->id, 'student_id' => $this->studentIds($class)[0], 'attendance_date' => '2026-10-07', 'meeting_number' => 1]);

        $this->actingAs($teacher)->post(route('teacher.attendance.store', $class), ['date' => '2026-10-14', 'attendance' => $this->sheet($class)]);

        $this->assertSame(2, Attendance::whereDate('attendance_date', '2026-10-14')->value('meeting_number'));
    }

    public function test_take_page_shows_roster_and_marks_off_schedule_days(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 2);
        $student = User::find($this->studentIds($class)[0]);

        $this->actingAs($teacher)->get(route('teacher.attendance.take', $class))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Tandai semua hadir')
            ->assertDontSee('Di luar jadwal rutin');

        // Tuesday is not a scheduled day for this class: allowed, with a note.
        $this->actingAs($teacher)->get(route('teacher.attendance.take', [$class, 'date' => '2026-10-13']))
            ->assertOk()
            ->assertSee('Di luar jadwal rutin');
    }

    public function test_old_meetings_are_read_only_and_future_dates_redirect(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 1);

        $this->actingAs($teacher)->get(route('teacher.attendance.take', [$class, 'date' => '2026-09-02']))
            ->assertOk()
            ->assertSee('Hanya dapat dilihat')
            ->assertDontSee('Simpan absensi');

        $this->actingAs($teacher)->get(route('teacher.attendance.take', [$class, 'date' => '2026-10-21']))
            ->assertRedirect(route('teacher.attendance.take', [$class, 'date' => '2026-10-14']));
    }

    public function test_future_and_too_old_dates_are_rejected(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 1);

        $this->actingAs($teacher)
            ->post(route('teacher.attendance.store', $class), ['date' => '2026-10-15', 'attendance' => $this->sheet($class)])
            ->assertSessionHasErrors('date');

        $this->actingAs($teacher)
            ->post(route('teacher.attendance.store', $class), ['date' => '2026-09-01', 'attendance' => $this->sheet($class)])
            ->assertSessionHasErrors('date');

        $this->assertSame(0, Attendance::count());
    }

    public function test_students_outside_the_roster_and_unknown_statuses_are_rejected(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 1);
        $outsider = User::factory()->student()->create();

        $this->actingAs($teacher)
            ->post(route('teacher.attendance.store', $class), [
                'date' => '2026-10-14',
                'attendance' => $this->sheet($class) + [$outsider->id => ['status' => 'present']],
            ])
            ->assertSessionHasErrors('attendance');

        $this->actingAs($teacher)
            ->post(route('teacher.attendance.store', $class), ['date' => '2026-10-14', 'attendance' => $this->sheet($class, 'bolos')])
            ->assertSessionHasErrors();

        $this->assertSame(0, Attendance::count());
    }

    public function test_other_teachers_and_finished_classes_are_forbidden(): void
    {
        $teacher = $this->makeTeacher();
        $foreign = $this->makeClass($this->makeTeacher(), 1);
        $finished = ClassName::factory()->completed()->withTeacher($teacher)->create();

        $this->actingAs($teacher)->get(route('teacher.attendance.take', $foreign))->assertForbidden();
        $this->actingAs($teacher)
            ->post(route('teacher.attendance.store', $foreign), ['date' => '2026-10-14', 'attendance' => $this->sheet($foreign)])
            ->assertForbidden();
        $this->actingAs($teacher)->get(route('teacher.attendance.take', $finished))->assertForbidden();

        $this->assertSame(0, Attendance::count());
    }

    public function test_today_page_lists_sessions_and_missed_meetings(): void
    {
        $teacher = $this->makeTeacher();
        $this->makeClass($teacher, 1, 3, ['name' => 'Kelas Rabu']);
        $this->makeClass($teacher, 1, 1, ['name' => 'Kelas Senin']);

        $this->actingAs($teacher)->get(route('teacher.attendance.today'))
            ->assertOk()
            ->assertSee('Kelas Rabu')
            ->assertSee('Mulai kelas')
            ->assertSee('Kelas Senin')
            ->assertSee('Absensi belum diisi');
    }

    public function test_extra_meeting_form_redirects_only_to_own_classes(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 1);
        $foreign = $this->makeClass($this->makeTeacher(), 1);

        $this->actingAs($teacher)->get(route('teacher.attendance.today', ['class' => $class->id, 'date' => '2026-10-12']))
            ->assertRedirect(route('teacher.attendance.take', [$class, 'date' => '2026-10-12']));

        $this->actingAs($teacher)->get(route('teacher.attendance.today', ['class' => $foreign->id]))->assertOk();
    }

    public function test_history_filters_and_export_are_scoped_to_own_classes(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 1, 3, ['name' => 'Kelas Saya']);
        $mine = User::find($this->studentIds($class)[0]);
        Attendance::factory()->create(['class_id' => $class->id, 'student_id' => $mine->id, 'attendance_date' => '2026-10-07']);
        Attendance::factory()->absent()->create(['class_id' => $class->id, 'student_id' => $mine->id, 'attendance_date' => '2026-09-30', 'notes' => '=HYPERLINK("x")']);

        $foreign = $this->makeClass($this->makeTeacher(), 1);
        $theirs = User::find($this->studentIds($foreign)[0]);
        Attendance::factory()->create(['class_id' => $foreign->id, 'student_id' => $theirs->id, 'attendance_date' => '2026-10-07']);

        $this->actingAs($teacher)->get(route('teacher.attendance.index'))
            ->assertOk()
            ->assertSee($mine->name)
            ->assertDontSee($theirs->name);

        $this->actingAs($teacher)->get(route('teacher.attendance.index', ['status' => 'absent']))
            ->assertOk()
            ->assertViewHas('records', fn ($records) => $records->total() === 1);

        // A foreign class filter is ignored (falls back to own classes only).
        $this->actingAs($teacher)->get(route('teacher.attendance.index', ['class' => $foreign->id]))
            ->assertOk()
            ->assertDontSee($theirs->name);

        $csv = $this->actingAs($teacher)->get(route('teacher.attendance.export'))->assertOk()->streamedContent();
        $this->assertStringContainsString($mine->name, $csv);
        $this->assertStringNotContainsString($theirs->name, $csv);
        $this->assertStringNotContainsString($mine->email, $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }
}
