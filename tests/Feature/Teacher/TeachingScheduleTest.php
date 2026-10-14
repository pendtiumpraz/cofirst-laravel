<?php

namespace Tests\Feature\Teacher;

use App\Models\Attendance;
use App\Models\ClassReport;
use App\Models\User;
use App\Services\Teacher\TeachingSchedule;
use App\Services\Teacher\TeachingSession;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Teacher\Concerns\BuildsTeachingData;
use Tests\TestCase;

/**
 * The timetable read model behind the dashboard, schedule and attendance pages.
 */
class TeachingScheduleTest extends TestCase
{
    use BuildsTeachingData, RefreshDatabase;

    private TeachingSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->freezeWednesdayMorning();
        $this->schedule = app(TeachingSchedule::class);
    }

    public function test_today_uses_the_school_timezone(): void
    {
        // 18:30 UTC on Tuesday is already Wednesday 01:30 in Jakarta.
        $this->travelTo(CarbonImmutable::parse('2026-10-13 18:30:00', 'UTC'));

        $this->assertSame('2026-10-14', TeachingSchedule::today()->toDateString());
        $this->assertSame('Rabu, 14 Okt 2026', TeachingSchedule::format(TeachingSchedule::today()));
    }

    public function test_one_session_per_slot_even_with_many_enrollments(): void
    {
        $teacher = $this->makeTeacher();
        $this->makeClass($teacher, 4, 3);

        $classes = $this->schedule->activeClasses($teacher);
        $slots = $this->schedule->slots($classes);
        $today = TeachingSchedule::today();

        $this->assertCount(1, $slots);
        $sessions = $this->schedule->sessionsBetween($slots, $today, $today);
        $this->assertCount(1, $sessions);
        $this->assertSame(4, $sessions->first()->rosterCount());
        $this->assertSame(90, $sessions->first()->minutes());
    }

    public function test_sessions_respect_the_class_period(): void
    {
        $teacher = $this->makeTeacher();
        $this->makeClass($teacher, 1, 3, ['start_date' => '2026-10-10', 'end_date' => '2026-10-20']);

        $slots = $this->schedule->slots($this->schedule->activeClasses($teacher));
        $dates = $this->schedule
            ->sessionsBetween($slots, CarbonImmutable::parse('2026-09-28'), CarbonImmutable::parse('2026-11-01'))
            ->map(fn (TeachingSession $s) => $s->date->toDateString())
            ->all();

        $this->assertSame(['2026-10-14'], $dates);
    }

    public function test_dropped_only_slots_and_finished_classes_are_ignored(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 0);
        $this->enroll($class, User::factory()->student()->create(), 3, 'dropped');
        $this->makeClass($teacher, 1, 3, ['status' => 'completed']);

        $this->assertCount(0, $this->schedule->slots($this->schedule->activeClasses($teacher)));
    }

    public function test_record_status_and_pending_sessions(): void
    {
        $teacher = $this->makeTeacher();
        $class = $this->makeClass($teacher, 2, 1); // Senin
        [$a, $b] = $this->studentIds($class);

        // Monday 12 Oct: both attended, one berita acara written.
        Attendance::factory()->create(['class_id' => $class->id, 'student_id' => $a, 'attendance_date' => '2026-10-12']);
        Attendance::factory()->create(['class_id' => $class->id, 'student_id' => $b, 'attendance_date' => '2026-10-12', 'status' => 'late']);
        ClassReport::factory()->create(['class_id' => $class->id, 'student_id' => $a, 'teacher_id' => $teacher->id, 'report_date' => '2026-10-12']);

        $slots = $this->schedule->slots($this->schedule->activeClasses($teacher));
        $pending = $this->schedule->pendingSessions($slots, 14);

        $this->assertCount(2, $pending); // 12 Oct (one report missing) and 5 Oct (nothing recorded); 7 days back would stop at 7 Oct.
        $latest = $pending->first();
        $this->assertSame('2026-10-12', $latest->date->toDateString());
        $this->assertSame(2, $latest->attendedCount);
        $this->assertSame(1, $latest->missingReports());
        $this->assertFalse($pending->last()->hasAttendance());
        $this->assertCount(1, $this->schedule->pendingSessions($slots, 7));
    }
}
