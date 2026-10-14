<?php

namespace App\Services\Academic;

use App\Models\ClassName;
use App\Models\ClassReport;
use App\Models\Material;
use App\Models\MaterialAccess;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin › Laporan akademik: three server-side tabs.
 *
 *  - laporan:      progress reports with filters and a CSV export;
 *  - berita-acara: meeting-report compliance per class (reported meetings, last
 *                  report, meetings in the last days that nobody reported);
 *  - progres:      completion per class and the most/least opened materials.
 */
class AcademicReportHub
{
    public const TABS = ['laporan' => 'Laporan perkembangan', 'berita-acara' => 'Kepatuhan berita acara', 'progres' => 'Progres belajar'];

    public function __construct(
        private readonly ProgressReportService $reports,
        private readonly ClassReportService $classReports,
        private readonly ProgressService $progress,
    ) {
    }

    /** Render the hub for the requested tab. */
    public function index(Request $request): View
    {
        $tab = array_key_exists((string) $request->query('tab'), self::TABS) ? $request->query('tab') : 'laporan';

        $data = match ($tab) {
            'berita-acara' => $this->complianceTab(),
            'progres' => $this->progressTab(),
            default => $this->reportsTab($request),
        };

        return view('admin.reports.index', $data + ['tab' => $tab, 'tabs' => self::TABS]);
    }

    /**
     * Stream the filtered progress reports as CSV (UTF-8 with BOM for Excel).
     *
     * Reason: cells are neutralised against formula injection, and the export of
     * children's academic data is written to the audit log (UU PDP accountability).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->reports->applyFilters(Report::query(), $request)
            ->with(['student:id,name', 'teacher:id,name', 'className:id,name'])
            ->orderByDesc('report_date');

        AcademicPrivacy::audit('academic.reports.exported', null, [
            'filters' => $request->only(['class_id', 'teacher_id', 'type', 'from', 'to']),
            'rows' => (clone $query)->count(),
        ]);

        $types = Report::types();

        return response()->streamDownload(function () use ($query, $types) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal', 'Siswa', 'Kelas', 'Pengajar', 'Jenis', 'Judul', 'Progres (%)', 'Nilai']);

            foreach ($query->lazy(200) as $report) {
                fputcsv($out, array_map([self::class, 'csvCell'], [
                    $report->report_date?->toDateString(),
                    $report->student?->name,
                    $report->className?->name,
                    $report->teacher?->name,
                    $types[$report->type] ?? $report->type,
                    $report->title,
                    (string) (float) $report->progress_percentage,
                    $report->score !== null ? (string) (float) $report->score : '',
                ]));
            }

            fclose($out);
        }, 'laporan-perkembangan-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Prefix cells that a spreadsheet would execute as a formula. */
    public static function csvCell(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }

    /** Tab 1: progress reports. */
    private function reportsTab(Request $request): array
    {
        $reports = $this->reports->applyFilters(Report::query(), $request)
            ->with(['student:id,name', 'teacher:id,name', 'className:id,name'])
            ->orderByDesc('report_date')->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        AcademicPrivacy::audit('academic.reports.listed', null, ['page' => $reports->currentPage(), 'rows' => $reports->count()]);

        return [
            'reports' => $reports,
            'classOptions' => ClassName::query()->orderBy('name')->pluck('name', 'id')->all(),
            'teacherOptions' => User::role('teacher')->orderBy('name')->pluck('name', 'id')->all(),
            'types' => Report::types(),
        ];
    }

    /** Tab 2: meeting-report compliance of running classes. */
    private function complianceTab(): array
    {
        $classes = ClassName::query()
            ->where('is_active', true)
            ->whereIn('status', [ClassName::STATUS_ACTIVE, ClassName::STATUS_PLANNED])
            ->with(['teachers:id,name', 'course:id,name'])
            ->withCount('activeEnrollments')
            ->orderBy('name')
            ->get();

        $reported = ClassReport::query()
            ->whereIn('class_id', $classes->pluck('id'))
            ->selectRaw('class_id, count(distinct meeting_number) as meetings, max(report_date) as last_date')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        return [
            'classes' => $classes,
            'reported' => $reported,
            'pending' => $this->classReports->pendingSessionsFor($classes->pluck('id'), 14),
            'editDays' => AcademicSettings::classReportEditDays(),
        ];
    }

    /** Tab 3: completion per class and material usage. */
    private function progressTab(): array
    {
        $classes = ClassName::query()
            ->whereIn('status', [ClassName::STATUS_ACTIVE, ClassName::STATUS_COMPLETED])
            ->whereNotNull('curriculum_id')
            ->with(['course:id,name', 'curriculum:id,title'])
            ->orderBy('name')
            ->paginate(15, ['*'], 'kelas')
            ->withQueryString();

        $usage = MaterialAccess::query()
            ->where('accessed_at', '>=', now()->subDays(90))
            ->selectRaw('material_id, count(*) as views, count(distinct student_id) as students')
            ->groupBy('material_id')
            ->orderByDesc('views')
            ->limit(5)
            ->get();
        $usage->load('material:id,title,type,syllabus_id', 'material.syllabus:id,meeting_number,curriculum_id');

        $unopened = Material::query()->active()
            ->whereHas('syllabus', fn ($q) => $q->active()->whereHas('curriculum', fn ($c) => $c->where('status', 'active')))
            ->whereDoesntHave('materialAccess')
            ->with('syllabus:id,meeting_number,curriculum_id', 'syllabus.curriculum:id,title')
            ->orderBy('id')
            ->limit(5)
            ->get();

        return [
            'classes' => $classes,
            'rates' => $this->progress->classCompletionRates($classes->getCollection()),
            'topMaterials' => $usage,
            'unopenedMaterials' => $unopened,
        ];
    }
}
