<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassName;
use App\Models\ClassProgress;
use App\Models\ClassReport;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\Teacher\PrivacyAudit;
use App\Services\Teacher\TeachingSchedule;
use App\Services\Teacher\TeachingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Teacher portal pages: my classes, class detail, my students and the weekly
 * teaching schedule. Every query is scoped to the classes the signed-in teacher
 * is assigned to (class_teacher pivot); other classes return 403.
 */
class TeacherController extends Controller
{
    /** Status tabs of "Kelas saya" => class statuses. */
    private const CLASS_TABS = [
        'berlangsung' => [ClassName::STATUS_ACTIVE],
        'direncanakan' => [ClassName::STATUS_PLANNED],
        'selesai' => [ClassName::STATUS_COMPLETED, ClassName::STATUS_CANCELLED],
    ];

    /**
     * Args:
     *     schedule (TeachingSchedule): Timetable read model.
     */
    public function __construct(private readonly TeachingSchedule $schedule) {}

    /**
     * My classes: cards per class with progress and next meeting, tabbed by status.
     *
     * Args:
     *     request (Request): Query `status` = berlangsung|direncanakan|selesai.
     *
     * Returns:
     *     View: teacher.classes.index
     */
    public function classes(Request $request): View
    {
        $teacher = $request->user();
        $status = $request->query('status');
        $tab = is_string($status) && array_key_exists($status, self::CLASS_TABS) ? $status : 'berlangsung';

        $counts = $teacher->teachingClasses()
            ->selectRaw('class_names.status, count(*) as total')
            ->groupBy('class_names.status')
            ->pluck('total', 'status');

        $classes = $teacher->teachingClasses()
            ->whereIn('class_names.status', self::CLASS_TABS[$tab])
            ->with(['course:id,name', 'teachers:users.id,users.name,users.profile_photo_path', 'curriculum' => fn ($q) => $q->withCount('syllabuses')])
            ->withCount(['enrollments as active_students_count' => fn ($q) => $q->where('status', Enrollment::STATUS_ACTIVE)])
            ->orderByDesc('class_names.start_date')
            ->get();

        $meetingsHeld = $this->meetingsHeld($classes->modelKeys());
        $today = TeachingSchedule::today();
        $nextSessions = $this->schedule
            ->sessionsBetween($this->schedule->slots($classes), $today, $today->addDays(13))
            ->filter(fn (TeachingSession $s) => ! $s->hasEnded(TeachingSchedule::now()))
            ->groupBy(fn (TeachingSession $s) => $s->class->id)
            ->map(fn (Collection $sessions) => $sessions->first());

        $tabs = [];
        foreach (self::CLASS_TABS as $key => $statuses) {
            $tabs[$key] = [
                'label' => ['berlangsung' => 'Berlangsung', 'direncanakan' => 'Direncanakan', 'selesai' => 'Selesai'][$key],
                'count' => (int) collect($statuses)->sum(fn ($status) => $counts[$status] ?? 0),
                'href' => route('teacher.classes', ['status' => $key]),
            ];
        }

        return view('teacher.classes.index', compact('classes', 'tab', 'tabs', 'meetingsHeld', 'nextSessions', 'teacher'));
    }

    /**
     * Class detail: overview, roster, schedule, curriculum progress, recent berita acara.
     *
     * Args:
     *     request (Request): Current request.
     *     class (ClassName): The class (403 unless the teacher teaches it).
     *
     * Returns:
     *     View: teacher.class-detail
     */
    public function classDetail(Request $request, ClassName $class): View
    {
        $teacher = $request->user();
        abort_unless($teacher->teachesClass($class), 403, 'Anda tidak mengajar kelas ini.');

        $class->load([
            'course:id,name',
            'teachers:users.id,users.name,users.profile_photo_path',
            'curriculum' => fn ($q) => $q->with(['syllabuses' => fn ($s) => $s->orderBy('meeting_number')]),
        ])->loadCount(['enrollments as active_students_count' => fn ($q) => $q->where('status', Enrollment::STATUS_ACTIVE)]);

        // Reason: data minimisation (UU PDP) — only id, name and photo of students, no contact data.
        $enrollments = $class->enrollments()
            ->where('status', Enrollment::STATUS_ACTIVE)
            ->with('student:id,name,profile_photo_path')
            ->get()
            ->filter(fn (Enrollment $e) => $e->student !== null)
            ->sortBy(fn (Enrollment $e) => mb_strtolower($e->student->name))
            ->values();
        $studentIds = $enrollments->pluck('student_id')->all();

        $attendance = Attendance::query()
            ->selectRaw('student_id, count(*) as total, sum(case when status in (?, ?) then 1 else 0 end) as attended', Attendance::ATTENDED_STATUSES)
            ->where('class_id', $class->id)
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->get()->keyBy('student_id');

        $lastStatus = Attendance::query()
            ->where('class_id', $class->id)
            ->whereIn('student_id', $studentIds)
            ->orderByDesc('attendance_date')
            ->get(['student_id', 'status', 'attendance_date'])
            ->unique('student_id')->keyBy('student_id');

        $lastReport = ClassReport::query()
            ->selectRaw('student_id, max(report_date) as last_date, count(*) as total')
            ->where('class_id', $class->id)
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->get()->keyBy('student_id');

        $progress = ClassProgress::query()
            ->where('class_id', $class->id)
            ->whereIn('student_id', $studentIds)
            ->get(['meeting_number', 'student_id', 'status']);

        $completedByStudent = $progress->where('status', 'completed')->countBy('student_id');
        $completedByMeeting = $progress->where('status', 'completed')->countBy('meeting_number');

        $slots = $this->schedule->slots(collect([$class]));
        $today = TeachingSchedule::today();
        $upcoming = $this->schedule->sessionsBetween($slots, $today, $today->addDays(41))
            ->filter(fn (TeachingSession $s) => ! $s->hasEnded(TeachingSchedule::now()))
            ->take(4)->values();

        $reports = ClassReport::query()
            ->where('class_id', $class->id)
            ->with(['student:id,name', 'teacher:id,name'])
            ->latest('report_date')->latest('id')
            ->paginate(10, ['*'], 'laporan')
            ->withQueryString()
            ->fragment('berita-acara');

        $totalMeetings = $class->curriculum?->syllabuses->count() ?: null;
        $meetingsHeld = $this->meetingsHeld([$class->id])[$class->id] ?? 0;
        $overallRate = ($sum = $attendance->sum('total')) > 0 ? round($attendance->sum('attended') / $sum * 100, 1) : null;

        PrivacyAudit::log('teacher.class_roster.viewed', $class, ['class_id' => $class->id, 'students' => count($studentIds)]);

        return view('teacher.class-detail', compact(
            'class', 'enrollments', 'attendance', 'lastStatus', 'lastReport', 'completedByStudent', 'completedByMeeting',
            'slots', 'upcoming', 'reports', 'totalMeetings', 'meetingsHeld', 'overallRate', 'teacher'
        ));
    }

    /**
     * My students: searchable list of active students across the teacher's classes.
     *
     * Args:
     *     request (Request): Query `search` (name) and `class` (class id).
     *
     * Returns:
     *     View: teacher.students.index
     */
    public function students(Request $request): View
    {
        $teacher = $request->user();
        $classes = $teacher->taughtClasses()->orderBy('class_names.name')->get(['class_names.id', 'class_names.name']);
        $classIds = $classes->modelKeys();

        $classFilter = is_scalar($request->query('class')) ? (int) $request->query('class') : 0;
        $scopeIds = in_array($classFilter, $classIds, true) ? [$classFilter] : $classIds;
        $search = is_string($request->query('search')) ? trim($request->query('search')) : '';

        $enrolled = fn ($q) => $q->where('enrollments.status', Enrollment::STATUS_ACTIVE)->whereIn('enrollments.class_id', $scopeIds);

        $students = User::query()
            ->select(['users.id', 'users.name', 'users.profile_photo_path'])
            ->whereHas('enrollments', $enrolled)
            ->when($search !== '', fn ($q) => $q->where('users.name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->with([
                'enrollments' => fn ($q) => $enrolled($q)->with('className:id,name,course_id', 'className.course:id,name'),
                'points:id,user_id,points,level',
            ])
            ->orderBy('users.name')
            ->paginate(20)
            ->withQueryString();

        $pageIds = $students->getCollection()->modelKeys();

        $rates = Attendance::query()
            ->selectRaw('student_id, count(*) as total, sum(case when status in (?, ?) then 1 else 0 end) as attended', Attendance::ATTENDED_STATUSES)
            ->whereIn('class_id', $scopeIds)
            ->whereIn('student_id', $pageIds)
            ->groupBy('student_id')
            ->get()->keyBy('student_id');

        $lastReports = ClassReport::query()
            ->selectRaw('student_id, max(report_date) as last_date')
            ->whereIn('class_id', $scopeIds)
            ->whereIn('student_id', $pageIds)
            ->groupBy('student_id')
            ->pluck('last_date', 'student_id');

        if ($pageIds !== []) {
            PrivacyAudit::log('teacher.students.listed', null, ['count' => count($pageIds), 'class_id' => $classFilter ?: null]);
        }

        return view('teacher.students.index', [
            'students' => $students,
            'classes' => $classes,
            'rates' => $rates,
            'lastReports' => $lastReports,
            'search' => $search,
            'classFilter' => in_array($classFilter, $classIds, true) ? $classFilter : null,
        ]);
    }

    /**
     * Weekly teaching calendar of the teacher's own classes.
     *
     * Args:
     *     request (Request): Query `week` = any Y-m-d inside the week to show (default this week).
     *
     * Returns:
     *     View: teacher.schedules.index
     */
    public function schedule(Request $request): View
    {
        $teacher = $request->user();
        $now = TeachingSchedule::now();
        $today = $now->startOfDay();
        $weekStart = (TeachingSchedule::parseDate($request->query('week')) ?? $today)->startOfWeek();

        // Reason: keep navigation within a sane range (one year back/forward).
        if (abs($weekStart->diffInDays($today->startOfWeek())) > 371) {
            $weekStart = $today->startOfWeek();
        }

        $classes = $this->schedule->activeClasses($teacher);
        $slots = $this->schedule->slots($classes);
        $sessions = $this->schedule->withRecordStatus(
            $this->schedule->sessionsBetween($slots, $weekStart, $weekStart->addDays(6))
        );

        $days = collect(range(0, 6))->map(fn (int $offset) => $weekStart->addDays($offset))
            ->mapWithKeys(fn ($day) => [$day->toDateString() => [
                'date' => $day,
                'sessions' => $sessions->filter(fn (TeachingSession $s) => $s->isOn($day))->values(),
            ]]);

        return view('teacher.schedules.index', [
            'days' => $days,
            'slots' => $slots,
            'weekStart' => $weekStart,
            'weekEnd' => $weekStart->addDays(6),
            'today' => $today,
            'now' => $now,
            'isCurrentWeek' => $weekStart->equalTo($today->startOfWeek()),
            'sessionCount' => $sessions->count(),
            'weeklyMinutes' => $sessions->sum(fn (TeachingSession $s) => $s->minutes()),
        ]);
    }

    /**
     * Number of distinct meeting dates with attendance, per class.
     *
     * Args:
     *     classIds (array<int, int>): Class ids.
     *
     * Returns:
     *     Collection<int, int>: class id => meetings held.
     */
    private function meetingsHeld(array $classIds): Collection
    {
        if ($classIds === []) {
            return collect();
        }

        return Attendance::query()
            ->selectRaw('class_id, count(distinct date(attendance_date)) as meetings')
            ->whereIn('class_id', $classIds)
            ->groupBy('class_id')
            ->pluck('meetings', 'class_id')
            ->map(fn ($n) => (int) $n);
    }
}
