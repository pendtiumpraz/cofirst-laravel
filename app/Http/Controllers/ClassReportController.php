<?php

namespace App\Http\Controllers;

use App\Http\Requests\Academic\StoreClassReportRequest;
use App\Http\Requests\Academic\UpdateClassReportRequest;
use App\Models\Attendance;
use App\Models\ClassName;
use App\Models\ClassReport;
use App\Models\Schedule;
use App\Models\Syllabus;
use App\Services\Academic\AcademicPrivacy;
use App\Services\Academic\AcademicScope;
use App\Services\Academic\ClassReportService;
use App\Services\Academic\Format;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Berita acara: per-meeting reports a teacher writes for each present student
 * (topic, Bloom's taxonomy observations, recommendations). Filing a report marks
 * the meeting completed in the student's progress, which unlocks materials.
 */
class ClassReportController extends Controller
{
    public function __construct(
        private readonly ClassReportService $service,
        private readonly AcademicScope $scope,
    ) {
    }

    /** List reports (role-scoped) with filters; teachers also see unreported meetings. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ClassReport::class);

        $user = $request->user();
        $role = $this->role($request);
        $isAdmin = $role === 'admin';

        $query = $this->service->visibleQuery($user, $isAdmin ? 'admin' : 'teacher');
        $classIds = $isAdmin ? null : $this->scope->teacherClassIds($user);

        $reports = $query
            ->when($request->integer('class_id'), fn ($q, $id) => $q->where('class_reports.class_id', $id))
            ->when($request->integer('meeting'), fn ($q, $n) => $q->where('class_reports.meeting_number', $n))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('student', fn ($s) => $s->where('name', 'like', '%'.trim((string) $request->query('search')).'%')))
            ->when($this->date($request->query('from')), fn ($q, $d) => $q->whereDate('class_reports.report_date', '>=', $d))
            ->when($this->date($request->query('to')), fn ($q, $d) => $q->whereDate('class_reports.report_date', '<=', $d))
            ->with(['teacher:id,name', 'className:id,name', 'student:id,name', 'syllabus:id,title'])
            ->orderByDesc('report_date')->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        if ($isAdmin) {
            AcademicPrivacy::audit('academic.class_reports.listed', null, ['page' => $reports->currentPage(), 'rows' => $reports->count()]);
        }

        return view('class-reports.index', [
            'reports' => $reports,
            'isAdmin' => $isAdmin,
            'pending' => $isAdmin ? collect() : $this->service->pendingSessions($user),
            'classOptions' => ClassName::query()
                ->when($classIds !== null, fn ($q) => $q->whereIn('id', $classIds))
                ->orderBy('name')->pluck('name', 'id')->all(),
            'service' => $this->service,
        ]);
    }

    /** One report, with links to the other students of the same meeting. */
    public function show(Request $request, ClassReport $classReport): View
    {
        $this->authorize('view', $classReport);

        $classReport->load(['teacher:id,name', 'className:id,name,course_id', 'className.course:id,name', 'student:id,name', 'schedule', 'curriculum:id,title', 'syllabus:id,title,meeting_number', 'material:id,title,type']);

        if ($this->role($request) === 'admin') {
            AcademicPrivacy::audit('academic.class_report.viewed', $classReport, ['student_id' => $classReport->student_id]);
        }

        return view('class-reports.show', [
            'report' => $classReport,
            'siblings' => $this->service->sessionSiblings($classReport, $this->service->visibleQuery($request->user(), $this->role($request))),
            'editableUntil' => $this->service->editableUntil($classReport),
            'isEditable' => $this->service->isEditable($classReport),
        ]);
    }

    /** Meeting report form: pick a class, then fill the meeting and each present student. */
    public function create(Request $request): View
    {
        $this->authorize('create', ClassReport::class);

        $teacher = $request->user();
        $classes = $this->service->teacherClasses($teacher);
        $schedule = $request->integer('schedule_id') ? Schedule::find($request->integer('schedule_id')) : null;
        $classId = $request->integer('class_id') ?: $schedule?->class_id;

        if (! $classId && $request->filled('student')) {
            // Reason: the teacher dashboard links here with ?student=<id>.
            $classId = $classes->first(fn ($c) => $c->activeEnrollments()->where('student_id', $request->integer('student'))->exists())?->id;
        }

        $class = $classId ? $classes->firstWhere('id', (int) $classId) : null;

        if (! $class && $classes->count() === 1) {
            $class = $classes->first();
        }

        $form = $class ? $this->formData($class, $request, $schedule?->class_id === $class->id ? $schedule : null) : null;

        return view('class-reports.create', [
            'classes' => $classes,
            'class' => $class,
            'form' => $form,
            'pending' => $this->service->pendingSessions($teacher),
            'bloom' => ClassReportService::bloomFields(),
        ]);
    }

    /** Save one report per present student. */
    public function store(StoreClassReportRequest $request): RedirectResponse
    {
        $class = $request->classModel();
        $session = $request->safe()->only(array_merge(ClassReportService::SESSION_FIELDS, ['schedule_id']));
        $reports = $this->service->createSession($request->user(), $class, $session, array_values($request->validated('students')));

        return redirect()->route('class-reports.index')->with('success', sprintf(
            'Berita acara pertemuan %d (%s) tersimpan untuk %d siswa. Progres siswa diperbarui.',
            $request->integer('meeting_number'), $class->name, $reports->count()
        ));
    }

    /** Edit form (owner within the edit window, or admin when corrections are enabled). */
    public function edit(Request $request, ClassReport $classReport): View|RedirectResponse
    {
        if ((int) $classReport->teacher_id === (int) $request->user()->id && ! $this->service->isEditable($classReport)) {
            return redirect()->route('class-reports.show', $classReport)
                ->with('error', 'Batas waktu mengubah berita acara ini sudah lewat ('.Format::dateTime($this->service->editableUntil($classReport)).').');
        }

        $this->authorize('update', $classReport);

        $classReport->load(['className.curriculum:id,title', 'student:id,name', 'teacher:id,name']);

        return view('class-reports.edit', [
            'report' => $classReport,
            'syllabuses' => $this->syllabusOptions($classReport->className),
            'bloom' => ClassReportService::bloomFields(),
            'editableUntil' => $this->service->editableUntil($classReport),
        ]);
    }

    /** Save changes (only meeting and narrative fields). */
    public function update(UpdateClassReportRequest $request, ClassReport $classReport): RedirectResponse
    {
        $this->service->update($classReport, $request->validated());

        if ((int) $classReport->teacher_id !== (int) $request->user()->id) {
            AcademicPrivacy::audit('academic.class_report.corrected', $classReport, ['student_id' => $classReport->student_id]);
        }

        return redirect()->route('class-reports.show', $classReport)->with('success', 'Berita acara diperbarui.');
    }

    /** Delete a report and revert the meeting progress it created. */
    public function destroy(ClassReport $classReport): RedirectResponse
    {
        $this->authorize('delete', $classReport);

        $this->service->delete($classReport);
        AcademicPrivacy::audit('academic.class_report.deleted', $classReport, ['student_id' => $classReport->student_id, 'class_id' => $classReport->class_id]);

        return redirect()->route('class-reports.index')->with('success', 'Berita acara dihapus dan progres pertemuan disesuaikan.');
    }

    /**
     * JSON for a class the user teaches: weekly slots, students (id + name only),
     * meetings and their materials.
     */
    public function getSchedulesAndStudents(Request $request): JsonResponse
    {
        $request->validate(['class_id' => ['required', 'integer']]);

        $class = ClassName::findOrFail($request->integer('class_id'));
        $user = $request->user();

        abort_unless($this->scope->isAdmin($user) || $this->scope->teacherTeaches($user, $class), 403);

        $syllabuses = $this->syllabusOptions($class);

        return response()->json([
            'schedules' => $class->schedules()->active()->get(['id', 'day_of_week', 'start_time', 'end_time'])
                ->unique(fn ($s) => $s->day_of_week.'|'.$s->start_time?->format('H:i'))
                ->map(fn ($s) => ['id' => $s->id, 'day_name' => $s->day_name, 'time_range' => $s->time_range])->values(),
            'students' => $this->service->roster($class)->map->only(['id', 'name'])->values(),
            'syllabuses' => $syllabuses->map(fn ($s) => ['id' => $s->id, 'meeting_number' => $s->meeting_number, 'title' => $s->title])->values(),
            'materials' => $syllabuses->flatMap(fn ($s) => $s->activeMaterials->map(fn ($m) => ['id' => $m->id, 'syllabus_id' => $s->id, 'title' => $m->title]))->values(),
            'next_meeting_number' => $this->service->nextMeetingNumber($class),
        ]);
    }

    /**
     * Data for the create form of one class.
     *
     * Returns:
     *     array<string, mixed>
     */
    private function formData(ClassName $class, Request $request, ?Schedule $schedule): array
    {
        $date = $this->date($request->query('date')) ?? Carbon::today(Format::timezone())->toDateString();
        $slot = $schedule ?? $class->schedules()->active()->where('day_of_week', Carbon::parse($date)->dayOfWeekIso)->first()
            ?? $class->schedules()->active()->orderBy('day_of_week')->first();
        $roster = $this->service->roster($class);
        $next = $this->service->nextMeetingNumber($class);
        $syllabuses = $this->syllabusOptions($class);

        $attendance = Attendance::query()
            ->where('class_id', $class->id)
            ->whereDate('attendance_date', $date)
            ->pluck('status', 'student_id');

        return [
            'date' => $date,
            'schedule_id' => $slot?->id,
            'start' => $slot?->start_time?->format('H:i') ?? '',
            'end' => $slot?->end_time?->format('H:i') ?? '',
            'meeting' => $request->integer('meeting')
                ?: ($syllabuses->isNotEmpty() ? min($next, (int) $syllabuses->max('meeting_number')) : $next),
            'roster' => $roster,
            'attendance' => $attendance,
            'syllabuses' => $syllabuses,
        ];
    }

    /** Active meetings (with active materials) of a class's curriculum. */
    private function syllabusOptions(?ClassName $class)
    {
        if (! $class?->curriculum_id) {
            return collect();
        }

        return Syllabus::query()->active()
            ->where('curriculum_id', $class->curriculum_id)
            ->with('activeMaterials:id,syllabus_id,title,order,status')
            ->orderBy('meeting_number')
            ->get(['id', 'curriculum_id', 'meeting_number', 'title', 'description']);
    }

    /** The acting role for this portal: admin or teacher. */
    private function role(Request $request): string
    {
        $user = $request->user();
        $active = (string) $request->session()->get('active_role');

        if (in_array($active, ['admin', 'superadmin'], true) && $this->scope->isAdmin($user)) {
            return 'admin';
        }

        if ($active === 'teacher' && $user->hasRole('teacher')) {
            return 'teacher';
        }

        return $this->scope->isAdmin($user) ? 'admin' : 'teacher';
    }

    /** Return the value when it is a Y-m-d date. */
    private function date(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value) ? $value : null;
    }
}
