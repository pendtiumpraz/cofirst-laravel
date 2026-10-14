<?php

namespace Tests\Unit\Models;

use App\Models\Casts\MultilineText;
use App\Models\Casts\TimeOfDay;
use App\Models\ClassReport;
use App\Models\Curriculum;
use App\Models\Schedule;
use App\Models\Syllabus;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * The TimeOfDay and MultilineText casts (no database needed).
 */
class CastsTest extends TestCase
{
    public function test_time_of_day_stores_plain_time_and_reads_carbon(): void
    {
        $schedule = new Schedule(['start_time' => '9:00', 'end_time' => '10:30']);

        $this->assertSame('09:00:00', $schedule->getAttributes()['start_time']);
        $this->assertInstanceOf(Carbon::class, $schedule->start_time);
        $this->assertSame('09:00', $schedule->start_time->format('H:i'));
        $this->assertSame('09:00 - 10:30', $schedule->time_range);
        $this->assertSame('09:00', $schedule->toArray()['start_time']);
    }

    public function test_time_of_day_strips_the_date_from_legacy_datetimes(): void
    {
        $report = new ClassReport(['start_time' => '2026-01-01 14:30:00', 'end_time' => Carbon::parse('2026-01-01 16:05:09')]);

        $this->assertSame('14:30:00', $report->getAttributes()['start_time']);
        $this->assertSame('16:05:09', $report->getAttributes()['end_time']);
        $this->assertTrue($report->start_time->isToday());
    }

    public function test_time_of_day_keeps_empty_values_null(): void
    {
        $schedule = new Schedule(['start_time' => '', 'end_time' => null]);

        $this->assertNull(TimeOfDay::normalize(''));
        $this->assertNull($schedule->start_time);
        $this->assertSame('', $schedule->time_range);
    }

    public function test_multiline_text_accepts_arrays_and_reads_text(): void
    {
        $syllabus = new Syllabus(['learning_objectives' => ['Memahami loop', ' - Membuat game ', '']]);

        $this->assertSame("Memahami loop\nMembuat game", $syllabus->learning_objectives);
        $this->assertSame(['Memahami loop', 'Membuat game'], $syllabus->learning_objectives_list);
    }

    public function test_multiline_text_reads_legacy_json_values(): void
    {
        $syllabus = (new Syllabus)->setRawAttributes(['activities' => json_encode(['Demo', 'Latihan'])]);
        $curriculum = (new Curriculum)->setRawAttributes(['objectives' => json_encode('Satu baris')]);

        $this->assertSame("Demo\nLatihan", $syllabus->activities);
        $this->assertSame('Satu baris', $curriculum->objectives);
        $this->assertSame(['Satu baris'], $curriculum->objectives_list);
    }

    public function test_multiline_text_handles_null_and_alias(): void
    {
        $curriculum = new Curriculum(['learning_objectives' => "• Satu\r\n• Dua"]);

        $this->assertSame(['Satu', 'Dua'], $curriculum->objectives_list);
        $this->assertSame($curriculum->objectives, $curriculum->learning_objectives);
        $this->assertSame([], MultilineText::toList(null));
        $this->assertSame([], (new Syllabus)->activities_list);
    }
}
