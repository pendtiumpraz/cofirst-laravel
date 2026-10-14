<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ClassName;
use App\Models\ClassReport;
use App\Models\User;
use App\Services\Academic\AcademicScope;
use App\Services\Academic\AcademicSettings;
use App\Services\Academic\ProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Orang Tua › Kurikulum: what each child learns in each class, meeting by meeting.
 * Parents only ever see their own children (AcademicScope::isParentOf).
 */
class CurriculumController extends Controller
{
    public function __construct(
        private readonly AcademicScope $scope,
        private readonly ProgressService $progress,
    ) {
    }

    /** Per child, one card per class with its curriculum and progress. */
    public function index(Request $request): View
    {
        $children = $request->user()->children()->orderBy('name')->get(['users.id', 'users.name']);

        $rows = $children->map(function (User $child) {
            $classes = ClassName::query()
                ->whereIn('id', $this->scope->studentEnrollments($child)->keys())
                ->with(['course:id,name', 'curriculum:id,title,type', 'teachers:id,name'])
                ->orderBy('name')
                ->get();

            return [
                'child' => $child,
                'classes' => $classes,
                'summaries' => $this->progress->summariesFor($child, $classes),
            ];
        });

        return view('parent.curriculum.index', ['rows' => $rows]);
    }

    /** Read-only meeting timeline of one child's class, with the latest meeting notes. */
    public function showChildProgress(Request $request, User $child, ClassName $class): View
    {
        abort_unless($this->scope->isParentOf($request->user(), $child), 403, 'Data siswa ini tidak terhubung dengan akun Anda.');
        abort_unless($this->scope->studentEnrollment($child, $class), 404, 'Anak Anda tidak terdaftar di kelas ini.');

        $class->load(['course:id,name', 'curriculum:id,title,type,description,objectives,duration_weeks', 'teachers:id,name']);
        $summary = $this->progress->summary($child, $class, true);
        $reportsVisible = AcademicSettings::reportsVisibleToParents();

        $latestReports = $reportsVisible
            ? ClassReport::query()->forStudent($child)
                ->where('class_id', $class->id)
                ->with(['teacher:id,name', 'syllabus:id,title'])
                ->orderByDesc('report_date')
                ->limit(3)
                ->get()
            : collect();

        return view('parent.curriculum.child-progress', [
            'child' => $child,
            'class' => $class,
            'summary' => $summary,
            'unlockedIds' => $this->progress->unlockedMaterialIds($summary),
            'latestReports' => $latestReports,
            'reportsVisible' => $reportsVisible,
        ]);
    }
}
