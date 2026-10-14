<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassReport;
use App\Models\TeacherHandover;
use App\Services\Teacher\TeachingSchedule;
use App\Services\Teacher\TeachingSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Teacher home: today's sessions, this week's timeline, students that need
 * attention, berita acara still to write and incoming handover requests.
 */
class DashboardController extends Controller
{
    /**
     * Args:
     *     schedule (TeachingSchedule): Timetable read model.
     */
    public function __construct(private readonly TeachingSchedule $schedule) {}

    /**
     * Show the teacher dashboard.
     *
     * Args:
     *     request (Request): Current request (the authenticated teacher).
     *
     * Returns:
     *     View: teacher.dashboard
     */
    public function index(Request $request): View
    {
        $teacher = $request->user();
        $now = TeachingSchedule::now();
        $today = $now->startOfDay();
        $weekStart = $today->startOfWeek();

        $classes = $this->schedule->activeClasses($teacher);
        $slots = $this->schedule->slots($classes);
        $week = $this->schedule->withRecordStatus(
            $this->schedule->sessionsBetween($slots, $weekStart, $weekStart->addDays(6))
        );
        $todaySessions = $week->filter(fn (TeachingSession $s) => $s->isOn($today))->values();
        $pending = $this->schedule->pendingSessions($slots, 7);

        $nextSession = $todaySessions->first(fn (TeachingSession $s) => ! $s->hasEnded($now));
        $studentCount = $classes->sum('active_students_count');
        $weeklyMinutes = $week->sum(fn (TeachingSession $s) => $s->minutes());

        $stats = [
            [
                'label' => 'Sesi hari ini',
                'value' => (string) $todaySessions->count(),
                'hint' => $nextSession ? 'berikutnya '.$nextSession->start : ($todaySessions->isEmpty() ? 'tidak ada jadwal' : 'semua sesi selesai'),
                'icon' => 'calendar-days',
                'tone' => 'primary',
                'href' => route('teacher.attendance.today'),
            ],
            [
                'label' => 'Siswa aktif',
                'value' => (string) $studentCount,
                'hint' => 'di '.$classes->count().' kelas',
                'icon' => 'user-group',
                'tone' => 'info',
                'href' => route('teacher.students.index'),
            ],
            [
                'label' => 'Laporan tertunda',
                'value' => (string) $pending->count(),
                'hint' => $pending->isEmpty() ? 'semua sudah dicatat' : 'sesi 7 hari terakhir',
                'icon' => 'clipboard-document-list',
                'tone' => $pending->isEmpty() ? 'success' : 'warning',
            ],
            [
                'label' => 'Jam mengajar',
                'value' => $weeklyMinutes > 0 ? rtrim(rtrim(number_format($weeklyMinutes / 60, 1, ',', '.'), '0'), ',') : '0',
                'unit' => 'jam',
                'hint' => 'minggu ini',
                'icon' => 'clock',
                'tone' => 'neutral',
                'href' => route('teacher.schedules.index'),
            ],
        ];

        $incomingHandovers = TeacherHandover::query()
            ->pending()
            ->where('to_teacher_id', $teacher->id)
            ->with(['className:id,name', 'fromTeacher:id,name'])
            ->latest()
            ->get();

        $recentReports = ClassReport::query()
            ->byTeacher($teacher)
            ->with(['className:id,name', 'student:id,name'])
            ->latest('report_date')
            ->latest('id')
            ->take(5)
            ->get();

        return view('teacher.dashboard', [
            'teacher' => $teacher,
            'now' => $now,
            'today' => $today,
            'weekStart' => $weekStart,
            'stats' => $stats,
            'classes' => $classes,
            'todaySessions' => $todaySessions,
            'week' => $week->groupBy(fn (TeachingSession $s) => $s->date->toDateString()),
            'pending' => $pending->take(5),
            'pendingCount' => $pending->count(),
            'attention' => $this->schedule->studentsNeedingAttention($classes, 5),
            'incomingHandovers' => $incomingHandovers,
            'recentReports' => $recentReports,
        ]);
    }
}
