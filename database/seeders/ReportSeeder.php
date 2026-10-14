<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Report;
use Database\Seeders\Data\DemoBlueprint;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;

/**
 * Periodic progress reports (table `reports`) for demo enrollments:
 * a monthly report after meeting 4, a weekly report after the latest meeting of
 * running classes and a final report for completed classes. Keyed by
 * (class_id, student_id, title), so re-running updates the same rows.
 */
class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $count = 0;

        foreach (DemoBlueprint::classes() as $code => $plan) {
            $past = DemoContext::pastMeetings($plan);

            if ($past->isEmpty() || $plan['meetings'] < 2) {
                continue;
            }

            $class = DemoContext::classByCode($code);
            $teacher = DemoContext::user($plan['teachers'][0]);
            $enrollments = $class->enrollments()->with('student')
                ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])->orderBy('id')->get();

            foreach ($enrollments as $enrollment) {
                $name = strtok($enrollment->student->name, ' ');
                $base = ['class_id' => $class->id, 'student_id' => $enrollment->student_id];

                if ($past->has(4)) {
                    Report::updateOrCreate($base + ['title' => 'Laporan Bulanan: Pertemuan 1-4'], [
                        'teacher_id' => $teacher->id,
                        'type' => Report::TYPE_MONTHLY,
                        'content' => "{$name} mengikuti empat pertemuan pertama {$class->course->name} dengan baik dan aktif mencoba latihan.",
                        'recommendations' => 'Lanjutkan latihan mandiri 2x seminggu dan ajukan pertanyaan di chat bila ada kesulitan.',
                        'progress_percentage' => 50,
                        'report_date' => $past[4]->copy()->addDays(2)->toDateString(),
                    ]);
                    $count++;
                }

                if ($plan['status'] === 'active') {
                    $last = $past->keys()->last();
                    Report::updateOrCreate($base + ['title' => 'Laporan Mingguan Terbaru'], [
                        'teacher_id' => $teacher->id,
                        'type' => Report::TYPE_WEEKLY,
                        'content' => "Pada pertemuan ke-{$last}, {$name} mempelajari materi baru dan menyelesaikan latihan kelas.",
                        'recommendations' => 'Ulangi latihan pertemuan ini sebelum kelas berikutnya.',
                        'progress_percentage' => round($last / $plan['meetings'] * 100, 2),
                        'report_date' => $past[$last]->copy()->addDay()->toDateString(),
                    ]);
                    $count++;
                }

                if ($plan['status'] === 'completed') {
                    Report::updateOrCreate($base + ['title' => 'Laporan Akhir Kelas'], [
                        'teacher_id' => $teacher->id,
                        'type' => Report::TYPE_FINAL,
                        'content' => "{$name} telah menyelesaikan seluruh {$plan['meetings']} pertemuan dan mempresentasikan proyek akhir.",
                        'recommendations' => 'Siap melanjutkan ke kursus tingkat berikutnya.',
                        'progress_percentage' => 100,
                        'score' => 75 + DemoContext::pick($code.$enrollment->student_id, 21),
                        'report_date' => $class->end_date->copy()->addDays(3)->toDateString(),
                    ]);
                    $count++;
                }
            }
        }

        $this->command?->info("Demo progress reports: {$count}.");
    }
}
