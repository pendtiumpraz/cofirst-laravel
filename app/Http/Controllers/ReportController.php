<?php

namespace App\Http\Controllers;

use App\Http\Requests\Academic\ProgressReportRequest;
use App\Models\ClassName;
use App\Models\Report;
use App\Models\User;
use App\Services\Academic\AcademicPrivacy;
use App\Services\Academic\AcademicReportHub;
use App\Services\Academic\AcademicScope;
use App\Services\Academic\ProgressReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Laporan perkembangan (periodic progress reports about one student).
 *
 * Teachers write and manage their own reports for students of their classes. The
 * admin hub (progress reports, meeting-report compliance, learning progress, CSV
 * export) is delegated to App\Services\Academic\AcademicReportHub.
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly ProgressReportService $reports,
        private readonly AcademicScope $scope,
    ) {
    }

    /** Admin › Laporan akademik (hub with three tabs). */
    public function adminIndex(Request $request): View
    {
        return app(AcademicReportHub::class)->index($request);
    }

    /** Admin: one progress report. */
    public function adminShow(Report $report): View
    {
        $report->load(['student:id,name', 'teacher:id,name', 'className:id,name,course_id', 'className.course:id,name']);

        AcademicPrivacy::audit('academic.report.viewed', $report, ['student_id' => $report->student_id]);

        return view('admin.reports.show', compact('report'));
    }

    /** Admin: CSV export of the filtered progress reports. */
    public function adminExport(Request $request): StreamedResponse
    {
        return app(AcademicReportHub::class)->export($request);
    }

    /** Teacher: own reports with filters. */
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $reports = $this->reports->applyFilters(
            Report::query()->where('reports.teacher_id', $teacher->id),
            $request
        )
            ->with(['student:id,name', 'className:id,name'])
            ->orderByDesc('report_date')->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('teacher.reports.index', [
            'reports' => $reports,
            'classOptions' => $this->classOptions($teacher),
            'types' => Report::types(),
        ]);
    }

    /** Teacher: new report form (prefill ?class_id & ?student_id). */
    public function create(Request $request): View
    {
        $options = $this->reports->formOptions($request->user());
        $studentId = $request->integer('student_id') ?: null;
        // Reason: links from a student's page only know the student; preselect their class.
        $classId = $request->integer('class_id')
            ?: ($studentId ? ($options->first(fn ($o) => collect($o['students'])->contains('id', $studentId))['id'] ?? null) : null);

        return view('teacher.reports.create', [
            'report' => new Report([
                'class_id' => $classId,
                'student_id' => $studentId,
                'type' => Report::TYPE_MONTHLY,
                'report_date' => today(),
                'progress_percentage' => 0,
            ]),
            'options' => $options,
            'types' => Report::types(),
        ]);
    }

    /** Teacher: save a new report. */
    public function store(ProgressReportRequest $request): RedirectResponse
    {
        $report = new Report($request->safe()->except('report_type'));
        $report->teacher_id = $request->user()->id;
        $report->save();

        return redirect()->route('teacher.reports.show', $report)->with('success', 'Laporan perkembangan disimpan.');
    }

    /** Teacher: one report. */
    public function show(Request $request, Report $report): View
    {
        abort_unless($this->reports->canView($request->user(), $report), 403, 'Anda tidak dapat melihat laporan ini.');

        $report->load(['student:id,name', 'teacher:id,name', 'className:id,name,course_id', 'className.course:id,name']);

        return view('teacher.reports.show', [
            'report' => $report,
            'canManage' => $this->reports->canManage($request->user(), $report),
        ]);
    }

    /** Teacher: edit form (author only). */
    public function edit(Request $request, Report $report): View
    {
        abort_unless($this->reports->canManage($request->user(), $report), 403, 'Hanya penulis laporan yang dapat mengubahnya.');

        $report->load(['student:id,name', 'className:id,name']);

        return view('teacher.reports.edit', ['report' => $report, 'types' => Report::types()]);
    }

    /** Teacher: save changes (class and student cannot change). */
    public function update(ProgressReportRequest $request, Report $report): RedirectResponse
    {
        abort_unless($this->reports->canManage($request->user(), $report), 403, 'Hanya penulis laporan yang dapat mengubahnya.');

        $report->update($request->safe()->except(['report_type', 'class_id', 'student_id']));

        return redirect()->route('teacher.reports.show', $report)->with('success', 'Laporan perkembangan diperbarui.');
    }

    /** Teacher: delete (author only). */
    public function destroy(Request $request, Report $report): RedirectResponse
    {
        abort_unless($this->reports->canManage($request->user(), $report), 403, 'Hanya penulis laporan yang dapat menghapusnya.');

        $report->delete();
        AcademicPrivacy::audit('academic.report.deleted', $report, ['student_id' => $report->student_id]);

        return redirect()->route('teacher.reports.index')->with('success', 'Laporan perkembangan dihapus.');
    }

    /** Teacher: latest report per student of one class. */
    public function byClass(Request $request, ClassName $class): View
    {
        abort_unless($this->scope->teacherTeaches($request->user(), $class), 403, 'Anda tidak mengajar kelas ini.');

        $class->load('course:id,name');
        $students = User::query()
            ->whereIn('id', $class->activeEnrollments()->select('student_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $latest = Report::query()
            ->where('class_id', $class->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->orderByDesc('report_date')->orderByDesc('id')
            ->get(['id', 'student_id', 'title', 'type', 'progress_percentage', 'report_date'])
            ->groupBy('student_id');

        return view('teacher.reports.by-class', compact('class', 'students', 'latest'));
    }

    /** Teacher: report timeline of one student of the teacher's classes. */
    public function byStudent(Request $request, User $student): View
    {
        $classIds = $this->scope->teacherClassIds($request->user());

        abort_unless(
            $student->enrollments()->whereIn('class_id', $classIds)->exists(),
            403,
            'Siswa ini tidak ada di kelas Anda.'
        );

        $reports = Report::query()->forStudent($student)
            ->whereIn('class_id', $classIds)
            ->with(['className:id,name', 'teacher:id,name'])
            ->orderByDesc('report_date')->orderByDesc('id')
            ->paginate(10);

        return view('teacher.reports.by-student', [
            'student' => $student->only(['id', 'name']),
            'reports' => $reports,
        ]);
    }

    /**
     * The teacher's classes for the filter select.
     *
     * Returns:
     *     array<int, string>
     */
    private function classOptions(User $teacher): array
    {
        return ClassName::query()
            ->whereIn('id', $this->scope->teacherClassIds($teacher))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
