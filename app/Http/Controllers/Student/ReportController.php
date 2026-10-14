<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassReport;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Siswa › Laporan belajar: the student's own progress reports (from teachers) and
 * meeting notes (berita acara). Only real records are shown — no sample data.
 *
 * Reason: "incident" reports are restricted data meant for parents and staff, so
 * they are not listed in the student portal.
 */
class ReportController extends Controller
{
    /** Two tabs: progress reports and meeting notes. */
    public function index(Request $request): View
    {
        $student = $request->user();
        $tab = $request->query('tab') === 'pertemuan' ? 'pertemuan' : 'perkembangan';

        $visible = fn () => Report::query()->forStudent($student)->where('type', '!=', Report::TYPE_INCIDENT);

        $stats = $visible()
            ->selectRaw('count(*) as total, avg(progress_percentage) as average, max(report_date) as latest')
            ->first();
        $byType = $visible()
            ->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');
        $meetingCount = ClassReport::query()->forStudent($student)->count();

        $reports = $tab === 'perkembangan'
            ? $visible()
                ->with(['teacher:id,name', 'className:id,name,course_id', 'className.course:id,name'])
                ->orderByDesc('report_date')->orderByDesc('id')
                ->paginate(10)->withQueryString()
            : null;

        $meetings = $tab === 'pertemuan'
            ? ClassReport::query()->forStudent($student)
                ->with(['teacher:id,name', 'className:id,name', 'syllabus:id,title'])
                ->orderByDesc('report_date')->orderByDesc('id')
                ->paginate(10)->withQueryString()
            : null;

        return view('student.reports.index', compact('tab', 'stats', 'byType', 'meetingCount', 'reports', 'meetings'));
    }

    /** One progress report. */
    public function show(Request $request, Report $report): View
    {
        abort_unless((int) $report->student_id === (int) $request->user()->id && $report->type !== Report::TYPE_INCIDENT, 404);

        $report->load(['teacher:id,name', 'className:id,name,course_id', 'className.course:id,name']);

        return view('student.reports.show', compact('report'));
    }

    /** One meeting note (teacher follow-up notes are internal and not shown). */
    public function meeting(ClassReport $classReport): View
    {
        $this->authorize('view', $classReport);
        abort_unless((int) $classReport->student_id === (int) auth()->id(), 404);

        $classReport->load(['teacher:id,name', 'className:id,name,course_id', 'className.course:id,name', 'syllabus:id,title,meeting_number', 'material:id,title,type']);

        return view('student.reports.meeting', ['report' => $classReport]);
    }
}
