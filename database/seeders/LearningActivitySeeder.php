<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Syllabus;
use Database\Seeders\Data\DemoBlueprint;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Learning history of the demo classes for every meeting that already took place:
 * attendance, class_progress (so progress percentages are non-zero),
 * material_access and the teachers' per-meeting reports (class_reports).
 *
 * Rows are bulk inserted. Because their dates are relative to today, the rows of
 * the demo classes/students are deleted and rebuilt on every run (idempotent).
 */
class LearningActivitySeeder extends Seeder
{
    /** @var array<string, array<int, array<string, mixed>>> */
    private array $rows = ['attendance' => [], 'class_progress' => [], 'material_access' => [], 'class_reports' => []];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classIds = DemoContext::classIds();

        DB::table('attendance')->whereIn('class_id', $classIds)->delete();
        DB::table('class_progress')->whereIn('class_id', $classIds)->delete();
        DB::table('class_reports')->whereIn('class_id', $classIds)->delete();
        DB::table('material_access')->whereIn('student_id', DemoContext::studentIds())->delete();

        $studentKeys = [];

        foreach (DemoContext::studentKeys() as $key) {
            $studentKeys[DemoContext::user($key)->id] = $key;
        }

        foreach (DemoBlueprint::classes() as $code => $plan) {
            if ($plan['status'] === 'planned') {
                continue;
            }

            $this->seedClass($code, $plan, $studentKeys);
        }

        foreach ($this->rows as $table => $rows) {
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table($table)->insert($chunk);
            }
        }

        $this->command?->info(sprintf(
            'Learning activity: %d attendance, %d progress, %d material access, %d meeting reports.',
            count($this->rows['attendance']), count($this->rows['class_progress']),
            count($this->rows['material_access']), count($this->rows['class_reports'])
        ));
    }

    /**
     * Build the rows of one class.
     *
     * @param  array<int, string>  $studentKeys  user id => blueprint key
     */
    private function seedClass(string $code, array $plan, array $studentKeys): void
    {
        $class = DemoContext::classByCode($code);
        $teacherId = DemoContext::user($plan['teachers'][0])->id;
        $syllabuses = Syllabus::where('curriculum_id', $class->curriculum_id)
            ->with(['materials' => fn ($q) => $q->orderBy('order')])
            ->get()->keyBy('meeting_number');
        $dates = DemoContext::meetingDates($plan);
        $past = DemoContext::pastMeetings($plan);
        $completedClass = $plan['status'] === 'completed';

        $enrollments = $class->enrollments()
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED, Enrollment::STATUS_DROPPED])
            ->orderBy('id')->get();
        $schedules = Schedule::where('class_id', $class->id)->pluck('id', 'enrollment_id');

        foreach ($enrollments as $enrollment) {
            $key = $studentKeys[$enrollment->student_id] ?? (string) $enrollment->student_id;
            $name = strtok(DemoContext::user($key)->name, ' ');
            // Reason: a dropped student only attended the first two meetings.
            $lastMeeting = $enrollment->status === Enrollment::STATUS_DROPPED ? 2 : $plan['meetings'];

            foreach ($dates as $number => $date) {
                if ($number > $lastMeeting) {
                    break;
                }

                $syllabus = $syllabuses->get($number);
                $isPast = $past->has($number);
                $status = $isPast ? $this->attendanceStatus("{$key}:{$code}:{$number}") : null;
                $attended = in_array($status, Attendance::ATTENDED_STATUSES, true);

                $this->progressRow($enrollment, $class->id, $syllabus, $number, $date, $isPast, $attended || $completedClass);

                if (! $isPast) {
                    continue;
                }

                $this->attendanceRow($enrollment, $class->id, $schedules[$enrollment->id] ?? null, $teacherId, $number, $date, $plan, $status, $key);

                if ($attended && $syllabus) {
                    $this->materialAccessRows($enrollment->student_id, $syllabus, $date, "{$key}:{$code}:{$number}");
                    $this->classReportRow($enrollment, $class, $schedules[$enrollment->id] ?? null, $teacherId, $syllabus, $number, $date, $plan, $name);
                }
            }
        }
    }

    /**
     * Deterministic attendance status: ~82% present, 8% late, 6% absent, 4% excused.
     */
    private function attendanceStatus(string $seed): string
    {
        $roll = DemoContext::pick($seed, 100);

        return match (true) {
            $roll < 82 => Attendance::STATUS_PRESENT,
            $roll < 90 => Attendance::STATUS_LATE,
            $roll < 96 => Attendance::STATUS_ABSENT,
            default => Attendance::STATUS_EXCUSED,
        };
    }

    private function attendanceRow(Enrollment $enrollment, int $classId, ?int $scheduleId, int $teacherId, int $number, Carbon $date, array $plan, string $status, string $key): void
    {
        $start = Carbon::parse($date->toDateString().' '.$plan['start']);
        $late = $status === Attendance::STATUS_LATE ? 5 + DemoContext::pick("late:{$key}:{$number}", 16) : null;
        $checkIn = match ($status) {
            Attendance::STATUS_PRESENT => $start->copy()->subMinutes(DemoContext::pick("in:{$key}:{$number}", 6)),
            Attendance::STATUS_LATE => $start->copy()->addMinutes($late),
            default => null,
        };

        $this->rows['attendance'][] = [
            'class_id' => $classId,
            'student_id' => $enrollment->student_id,
            'schedule_id' => $scheduleId,
            'teacher_id' => $teacherId,
            'meeting_number' => $number,
            'attendance_date' => $date->toDateString(),
            'status' => $status,
            'check_in_time' => $checkIn?->format('H:i:s'),
            'minutes_late' => $late,
            'notes' => match ($status) {
                Attendance::STATUS_ABSENT => 'Tidak hadir tanpa keterangan.',
                Attendance::STATUS_EXCUSED => 'Izin (sakit), materi dikirim lewat chat.',
                default => null,
            },
            'created_at' => $start,
            'updated_at' => $start,
        ];
    }

    private function progressRow(Enrollment $enrollment, int $classId, ?Syllabus $syllabus, int $number, Carbon $date, bool $isPast, bool $done): void
    {
        if (! $syllabus) {
            return;
        }

        $status = match (true) {
            $isPast && $done => 'completed',
            $isPast => 'in_progress',
            default => 'not_started',
        };

        $this->rows['class_progress'][] = [
            'student_id' => $enrollment->student_id,
            'class_id' => $classId,
            'syllabus_id' => $syllabus->id,
            'material_id' => $status === 'completed' ? $syllabus->materials->first()?->id : null,
            'meeting_number' => $number,
            'status' => $status,
            'completed_at' => $status === 'completed' ? $date->copy()->setTime(20, 0) : null,
            'notes' => $status === 'in_progress' ? 'Perlu mengejar materi pertemuan ini.' : null,
            'created_at' => $date->copy()->subWeek(),
            'updated_at' => $isPast ? $date->copy()->setTime(20, 0) : $date->copy()->subWeek(),
        ];
    }

    private function materialAccessRows(int $studentId, Syllabus $syllabus, Carbon $date, string $seed): void
    {
        foreach ($syllabus->materials->take(1 + DemoContext::pick($seed, 2)) as $index => $material) {
            $at = $date->copy()->addDay()->setTime(18 + $index, DemoContext::pick($seed.$index, 60));

            $this->rows['material_access'][] = [
                'student_id' => $studentId,
                'material_id' => $material->id,
                'accessed_at' => $at,
                'access_duration_seconds' => 300 + DemoContext::pick($seed.'d'.$index, 1500),
                'created_at' => $at,
                'updated_at' => $at,
            ];
        }
    }

    private function classReportRow(Enrollment $enrollment, $class, ?int $scheduleId, int $teacherId, Syllabus $syllabus, int $number, Carbon $date, array $plan, string $name): void
    {
        $topic = mb_strtolower($syllabus->title);
        $variant = DemoContext::pick("{$enrollment->id}:{$number}", 3);
        $at = $date->copy()->setTime(21, 0);

        $this->rows['class_reports'][] = [
            'teacher_id' => $teacherId,
            'class_id' => $class->id,
            'student_id' => $enrollment->student_id,
            'schedule_id' => $scheduleId,
            'report_date' => $date->toDateString(),
            'start_time' => $plan['start'].':00',
            'end_time' => $plan['end'].':00',
            'meeting_number' => $number,
            'curriculum_id' => $class->curriculum_id,
            'syllabus_id' => $syllabus->id,
            'material_id' => $syllabus->materials->first()?->id,
            'learning_concept' => $syllabus->title.' — '.$syllabus->description,
            'remember_understanding' => "{$name} dapat menyebutkan kembali istilah penting tentang {$topic}.",
            'understand_comprehension' => "{$name} mampu menjelaskan ".mb_strtolower(rtrim((string) $syllabus->description, '.')).' dengan bahasa sendiri.',
            'apply_application' => ["{$name} menyelesaikan latihan inti tanpa bantuan.", "{$name} menyelesaikan latihan dengan sedikit bimbingan mentor.", "{$name} menerapkan konsep ke contoh baru buatan sendiri."][$variant],
            'analyze_analysis' => $number > 1 ? "{$name} mulai bisa menemukan dan memperbaiki kesalahan pada programnya." : null,
            'evaluate_evaluation' => $number > 3 ? "{$name} membandingkan dua cara penyelesaian dan memilih yang lebih rapi." : null,
            'create_creation' => $number > 5 ? "{$name} membuat variasi proyek sendiri dari contoh di kelas." : null,
            'notes_recommendations' => ['Pertahankan semangat bertanya di kelas.', 'Latih kembali materi ini 15 menit di rumah.', 'Sudah siap mencoba tantangan tingkat berikutnya.'][$variant],
            'follow_up_notes' => $variant === 1 ? 'Ulangi latihan di modul sebelum pertemuan berikutnya.' : null,
            'learning_media_link' => null,
            'created_at' => $at,
            'updated_at' => $at,
        ];
    }
}
