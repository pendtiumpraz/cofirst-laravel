<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassName;
use App\Models\ClassReport;
use App\Models\MaterialAccess;
use App\Models\User;
use App\Services\Academic\AcademicScope;
use App\Services\Academic\AcademicSettings;
use App\Services\Academic\MaterialAccessService;
use App\Services\Academic\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Orang Tua › Progres Anak: progress bars per child and class, the materials a child
 * has opened, and the teachers' meeting notes (when
 * academic.reports_visible_to_parents is on).
 */
class ProgressController extends Controller
{
    public function __construct(
        private readonly AcademicScope $scope,
        private readonly ProgressService $progress,
        private readonly MaterialAccessService $access,
    ) {
    }

    /** All children with a progress bar per class. */
    public function index(Request $request): View
    {
        $children = $request->user()->children()->orderBy('name')->get(['users.id', 'users.name']);

        $rows = $children->map(fn (User $child) => [
            'child' => $child,
            'classes' => $classes = $this->classesOf($child),
            'summaries' => $this->progress->summariesFor($child, $classes),
        ]);

        return view('parent.progress.index', ['rows' => $rows]);
    }

    /** One child: per class progress, attendance and recent activity. */
    public function child(Request $request, User $child): View
    {
        $this->authorizeChild($request, $child);

        $classes = $this->classesOf($child);
        $reportsVisible = AcademicSettings::reportsVisibleToParents();

        $attendance = Attendance::query()
            ->where('student_id', $child->id)
            ->whereIn('class_id', $classes->pluck('id'))
            ->selectRaw('class_id, status, count(*) as total')
            ->groupBy('class_id', 'status')
            ->get()
            ->groupBy('class_id')
            ->map(fn ($rows) => $rows->pluck('total', 'status'));

        $recentMaterials = MaterialAccess::query()
            ->where('student_id', $child->id)
            ->whereHas('material')
            ->with('material:id,title,type')
            ->latest('accessed_at')
            ->limit(5)
            ->get();

        $latestReports = $reportsVisible
            ? ClassReport::query()->forStudent($child)
                ->with(['className:id,name', 'syllabus:id,title', 'teacher:id,name'])
                ->orderByDesc('report_date')->limit(5)->get()
            : collect();

        return view('parent.progress.child', [
            'child' => $child,
            'classes' => $classes,
            'summaries' => $this->progress->summariesFor($child, $classes),
            'attendance' => $attendance,
            'recentMaterials' => $recentMaterials,
            'latestReports' => $latestReports,
            'reportsVisible' => $reportsVisible,
        ]);
    }

    /** Materials of one class that are open for the child, with when they were opened. */
    public function childMaterials(Request $request, User $child, ClassName $class): View
    {
        $this->authorizeChild($request, $child);
        abort_unless($this->scope->studentEnrollment($child, $class), 404, 'Anak Anda tidak terdaftar di kelas ini.');

        $class->load(['course:id,name', 'curriculum:id,title']);
        $summary = $this->progress->summary($child, $class, true);
        $unlockedIds = $this->progress->unlockedMaterialIds($summary);

        return view('parent.progress.child-materials', [
            'child' => $child,
            'class' => $class,
            'summary' => $summary,
            'unlockedIds' => $unlockedIds,
            'accessStats' => $this->access->statsFor($child->id, $unlockedIds),
            'trackingMode' => AcademicSettings::trackingMode(),
        ]);
    }

    /** Meeting notes of one child (filterable by class). */
    public function meetings(Request $request, User $child): View
    {
        $this->authorizeChild($request, $child);

        $classes = $this->classesOf($child);
        $visible = AcademicSettings::reportsVisibleToParents();

        $reports = $visible
            ? ClassReport::query()->forStudent($child)
                ->when($classes->pluck('id')->contains($request->integer('class_id')), fn ($q) => $q->where('class_id', $request->integer('class_id')))
                ->with(['className:id,name', 'syllabus:id,title', 'teacher:id,name'])
                ->orderByDesc('report_date')->orderByDesc('id')
                ->paginate(10)->withQueryString()
            : null;

        return view('parent.progress.meetings', compact('child', 'classes', 'reports', 'visible'));
    }

    /** One meeting note of the child (teacher follow-up notes stay internal). */
    public function meeting(Request $request, User $child, ClassReport $classReport): View
    {
        $this->authorizeChild($request, $child);
        abort_unless((int) $classReport->student_id === (int) $child->id, 404);
        $this->authorize('view', $classReport);

        $classReport->load(['teacher:id,name', 'className:id,name,course_id', 'className.course:id,name', 'syllabus:id,title,meeting_number', 'material:id,title,type']);

        return view('parent.progress.meeting', ['child' => $child, 'report' => $classReport]);
    }

    /** Progress summary of a child as JSON (for widgets). */
    public function getChildProgressSummary(Request $request, User $child): JsonResponse
    {
        $this->authorizeChild($request, $child);

        $classes = $this->classesOf($child);
        $summaries = $this->progress->summariesFor($child, $classes);

        return response()->json([
            'child_name' => $child->name,
            'classes' => $classes->map(fn (ClassName $class) => [
                'class_name' => $class->name,
                'course_name' => $class->course?->name,
                'total_meetings' => $summaries[$class->id]->total(),
                'completed_meetings' => $summaries[$class->id]->completed(),
                'percentage' => $summaries[$class->id]->percent(),
                'status' => $summaries[$class->id]->label(),
            ])->values(),
        ]);
    }

    /** Abort unless the child belongs to the signed-in parent. */
    private function authorizeChild(Request $request, User $child): void
    {
        abort_unless($this->scope->isParentOf($request->user(), $child), 403, 'Data siswa ini tidak terhubung dengan akun Anda.');
    }

    /**
     * Classes of a child (active or completed enrollment).
     *
     * Returns:
     *     Collection<int, ClassName>
     */
    private function classesOf(User $child): Collection
    {
        return ClassName::query()
            ->whereIn('id', $this->scope->studentEnrollments($child)->keys())
            ->with(['course:id,name', 'curriculum:id,title', 'teachers:id,name'])
            ->orderBy('name')
            ->get();
    }
}
