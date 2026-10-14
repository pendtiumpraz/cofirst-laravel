<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassName;
use App\Services\Academic\AcademicScope;
use App\Services\Academic\MaterialAccessService;
use App\Services\Academic\ProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Siswa › Kurikulum: what each class teaches, meeting by meeting, with the
 * student's own progress and the materials that are already open.
 */
class CurriculumController extends Controller
{
    public function __construct(
        private readonly AcademicScope $scope,
        private readonly ProgressService $progress,
        private readonly MaterialAccessService $access,
    ) {
    }

    /** One card per class the student follows. */
    public function index(Request $request): View
    {
        $student = $request->user();
        $enrollments = $this->scope->studentEnrollments($student);

        $classes = ClassName::query()
            ->whereIn('id', $enrollments->keys())
            ->with(['course:id,name', 'curriculum:id,title,type', 'teachers:id,name'])
            ->orderByRaw("case when status = 'active' then 0 else 1 end")
            ->orderBy('name')
            ->get();

        return view('student.curriculum.index', [
            'classes' => $classes,
            'enrollments' => $enrollments,
            'summaries' => $this->progress->summariesFor($student, $classes),
        ]);
    }

    /** Meeting timeline of one class. */
    public function show(Request $request, ClassName $class): View
    {
        $student = $request->user();
        $enrollment = $this->scope->studentEnrollment($student, $class);

        abort_unless($enrollment, 403, 'Anda tidak terdaftar di kelas ini.');

        $class->load(['course:id,name', 'curriculum:id,title,type,description,duration_weeks,objectives', 'teachers:id,name']);
        $summary = $this->progress->summary($student, $class, true);
        $materialIds = $summary->meetings->flatMap(fn ($m) => $m['syllabus']->activeMaterials->pluck('id'));

        return view('student.curriculum.show', [
            'class' => $class,
            'enrollment' => $enrollment,
            'summary' => $summary,
            'accessStats' => $this->access->statsFor($student->id, $materialIds),
            'unlockedIds' => $this->progress->unlockedMaterialIds($summary),
        ]);
    }
}
