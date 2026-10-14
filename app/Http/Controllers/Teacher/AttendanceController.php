<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\ClassName;
use App\Services\Teacher\AttendanceRecorder;
use App\Services\Teacher\PrivacyAudit;
use App\Services\Teacher\TeachingSchedule;
use App\Services\Teacher\TeachingSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Attendance (absensi) for the teacher's own classes: today's sessions, the
 * sheet of one meeting (class + date), history with filters and CSV export.
 */
class AttendanceController extends Controller
{
    /**
     * Args:
     *     schedule (TeachingSchedule): Timetable read model.
     *     recorder (AttendanceRecorder): Writes attendance and awards points.
     */
    public function __construct(
        private readonly TeachingSchedule $schedule,
        private readonly AttendanceRecorder $recorder,
    ) {}

    /**
     * Attendance history with filters (class, status, date range) and a summary.
     *
     * Args:
     *     request (Request): Query `class`, `status`, `from`, `to`.
     *
     * Returns:
     *     View: teacher.attendance.index
     */
    public function index(Request $request): View
    {
        $teacher = $request->user();
        $classes = $teacher->teachingClasses()->orderBy('class_names.name')->get(['class_names.id', 'class_names.name']);
        $filters = $this->filters($request, $classes->modelKeys());

        $records = $this->historyQuery($filters, $classes->modelKeys())
            ->with(['className:id,name,status,is_active', 'student:id,name,profile_photo_path'])
            ->orderByDesc('attendance_date')
            ->orderBy('class_id')
            ->paginate(25)
            ->withQueryString();

        $summary = $this->historyQuery($filters, $classes->modelKeys())
            ->reorder()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (int) $summary->sum();
        $attended = (int) collect(Attendance::ATTENDED_STATUSES)->sum(fn ($s) => $summary[$s] ?? 0);

        if ($records->total() > 0) {
            PrivacyAudit::log('teacher.attendance.history_viewed', null, ['filters' => array_filter($filters), 'rows' => $records->total()]);
        }

        return view('teacher.attendance.index', [
            'records' => $records,
            'classes' => $classes,
            'filters' => $filters,
            'summary' => $summary,
            'total' => $total,
            'rate' => $total > 0 ? round($attended / $total * 100, 1) : null,
            'statuses' => Attendance::statuses(),
            'tones' => AttendanceRecorder::tones(),
        ]);
    }

    /**
     * Today's sessions with their attendance state, plus sessions of the edit window
     * (AttendanceRecorder::EDIT_WINDOW_DAYS) that still have no attendance.
     *
     * Args:
     *     request (Request): Optional query `class` and `date`.
     *
     * Returns:
     *     View|RedirectResponse: teacher.attendance.today, or the sheet chosen in the
     *     "pertemuan di luar jadwal" form (`class` + `date` query).
     */
    public function today(Request $request): View|RedirectResponse
    {
        $now = TeachingSchedule::now();
        $classes = $this->schedule->activeClasses($request->user());

        if (is_scalar($request->query('class')) && $request->filled('class')) {
            $chosen = $classes->firstWhere('id', (int) $request->query('class'));

            if ($chosen) {
                $date = TeachingSchedule::parseDate($request->query('date')) ?? $now->startOfDay();

                return redirect()->route('teacher.attendance.take', [$chosen, 'date' => $date->toDateString()]);
            }
        }

        $slots = $this->schedule->slots($classes);

        $sessions = $this->schedule->withRecordStatus(
            $this->schedule->sessionsBetween($slots, $now->startOfDay(), $now->startOfDay())
        );

        $missed = $this->schedule->pendingSessions($slots, AttendanceRecorder::EDIT_WINDOW_DAYS)
            ->filter(fn (TeachingSession $s) => ! $s->hasAttendance() && ! $s->isOn($now->startOfDay()))
            ->values();

        return view('teacher.attendance.today', [
            'sessions' => $sessions,
            'missed' => $missed,
            'classes' => $classes,
            'now' => $now,
        ]);
    }

    /**
     * Attendance sheet of one meeting.
     *
     * Args:
     *     request (Request): Query `date` (Y-m-d, default today).
     *     class (ClassName): The class (403 unless the teacher teaches it now).
     *
     * Returns:
     *     View|RedirectResponse: teacher.attendance.take, or a redirect to today's sheet for a future date.
     */
    public function take(Request $request, ClassName $class): View|RedirectResponse
    {
        $teacher = $request->user();
        abort_unless($teacher->taughtClasses()->where('class_names.id', $class->id)->exists(), 403, 'Anda tidak mengajar kelas ini atau kelas sudah selesai.');

        $today = TeachingSchedule::today();
        $date = TeachingSchedule::parseDate($request->query('date')) ?? $today;
        $earliest = $today->subDays(AttendanceRecorder::EDIT_WINDOW_DAYS);

        if ($date->gt($today)) {
            return redirect()->route('teacher.attendance.take', [$class, 'date' => $today->toDateString()])
                ->with('warning', 'Absensi tidak dapat diisi untuk tanggal yang belum terjadi.');
        }

        $class->load('course:id,name')
            ->loadCount(['enrollments as active_students_count' => fn ($q) => $q->where('status', 'active')]);

        $roster = $this->recorder->roster($class, $date);
        $slot = $this->schedule->slots(collect([$class]))->firstWhere('day', $date->dayOfWeekIso);
        $locked = $date->lt($earliest);

        PrivacyAudit::log('teacher.attendance.sheet_viewed', $class, ['class_id' => $class->id, 'date' => $date->toDateString()]);

        return view('teacher.attendance.take', [
            'class' => $class,
            'date' => $date,
            'today' => $today,
            'earliest' => $earliest,
            'roster' => $roster,
            'slot' => $slot,
            'scheduled' => $slot !== null,
            'locked' => $locked,
            'recorded' => $roster->contains(fn (array $row) => $row['attendance'] !== null),
            'statuses' => Attendance::statuses(),
            'tones' => AttendanceRecorder::tones(),
            'lateTolerance' => (int) setting('academic.attendance_late_minutes', 15),
        ]);
    }

    /**
     * Save the attendance sheet of one meeting.
     *
     * Args:
     *     request (StoreAttendanceRequest): Validated sheet (authorizes the teacher).
     *     class (ClassName): The class.
     *
     * Returns:
     *     RedirectResponse: Back to the sheet with a success message.
     */
    public function store(StoreAttendanceRequest $request, ClassName $class): RedirectResponse
    {
        $date = TeachingSchedule::parseDate($request->validated('date'));
        $result = $this->recorder->record($class, $date, $request->validated('attendance'), $request->user());

        $message = $result['saved'].' data kehadiran untuk '.TeachingSchedule::format($date, 'l, j M Y').' tersimpan.';
        if ($result['awarded'] > 0) {
            $message .= ' '.$result['awarded'].' siswa mendapat poin kehadiran.';
        }

        return redirect()
            ->route('teacher.attendance.take', [$class, 'date' => $date->toDateString()])
            ->with('success', $message);
    }

    /**
     * Download the filtered history as CSV (audit-logged; names only, no contact data).
     *
     * Args:
     *     request (Request): Same filters as index().
     *
     * Returns:
     *     StreamedResponse: text/csv download.
     */
    public function export(Request $request): StreamedResponse
    {
        $classIds = $request->user()->teachingClasses()->pluck('class_names.id')->map(fn ($id) => (int) $id)->all();
        $filters = $this->filters($request, $classIds);
        $query = $this->historyQuery($filters, $classIds)
            ->with(['className:id,name', 'student:id,name'])
            ->orderByDesc('attendance_date')
            ->orderBy('class_id');

        PrivacyAudit::log('teacher.attendance.exported', null, ['filters' => array_filter($filters), 'rows' => (clone $query)->count()]);

        $statuses = Attendance::statuses();
        $filename = 'absensi-'.TeachingSchedule::today()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($query, $statuses) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal', 'Pertemuan', 'Kelas', 'Siswa', 'Status', 'Menit terlambat', 'Catatan']);

            $query->chunk(500, function (Collection $rows) use ($out, $statuses) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->attendance_date?->format('Y-m-d'),
                        $row->meeting_number,
                        $this->csvSafe($row->className?->name),
                        $this->csvSafe($row->student?->name),
                        $statuses[$row->status] ?? $row->status,
                        $row->minutes_late,
                        $this->csvSafe($row->notes),
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store, private']);
    }

    /**
     * Sanitised history filters (unknown values are ignored, never trusted).
     *
     * Args:
     *     request (Request): Query string.
     *     classIds (array<int, int>): Classes the teacher may see.
     *
     * Returns:
     *     array{class: ?int, status: ?string, from: ?string, to: ?string}
     */
    private function filters(Request $request, array $classIds): array
    {
        $class = is_scalar($request->query('class')) ? (int) $request->query('class') : 0;
        $status = is_string($request->query('status')) ? $request->query('status') : '';
        $from = TeachingSchedule::parseDate($request->query('from'));
        $to = TeachingSchedule::parseDate($request->query('to'));

        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [
            'class' => in_array($class, $classIds, true) ? $class : null,
            'status' => array_key_exists($status, Attendance::statuses()) ? $status : null,
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
        ];
    }

    /**
     * Attendance rows of the teacher's classes matching the filters.
     *
     * Args:
     *     filters (array): Output of filters().
     *     classIds (array<int, int>): Classes the teacher may see.
     */
    private function historyQuery(array $filters, array $classIds): Builder
    {
        return Attendance::query()
            ->whereIn('class_id', $filters['class'] ? [$filters['class']] : $classIds)
            ->when($filters['status'], fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters['from'], fn (Builder $q, $from) => $q->whereRaw('date(attendance_date) >= ?', [$from]))
            ->when($filters['to'], fn (Builder $q, $to) => $q->whereRaw('date(attendance_date) <= ?', [$to]));
    }

    /**
     * Neutralise spreadsheet formulas in free text (CSV injection).
     *
     * Args:
     *     value (string|null): Cell text.
     */
    private function csvSafe(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}
