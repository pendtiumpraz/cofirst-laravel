<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassName;
use App\Models\Material;
use App\Models\MaterialAccess;
use App\Services\Academic\AcademicScope;
use App\Services\Academic\MaterialAccessService;
use App\Services\Academic\MaterialFileService;
use App\Services\Academic\ProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Siswa › Materi: the materials of the student's classes. A material opens when its
 * meeting is unlocked (setting academic.material_unlock_mode); opening it is recorded
 * with data minimisation (MaterialAccessService).
 */
class MaterialController extends Controller
{
    public function __construct(
        private readonly AcademicScope $scope,
        private readonly ProgressService $progress,
        private readonly MaterialAccessService $access,
        private readonly MaterialFileService $files,
    ) {
    }

    /** Overview: one card per class with its open materials. */
    public function index(Request $request): View
    {
        $student = $request->user();

        $classes = ClassName::query()
            ->whereIn('id', $this->scope->studentEnrollments($student)->keys())
            ->with(['course:id,name', 'curriculum:id,title', 'teachers:id,name'])
            ->orderBy('name')
            ->get();

        $summaries = $this->progress->summariesFor($student, $classes, true);

        $recent = MaterialAccess::query()
            ->where('student_id', $student->id)
            ->whereHas('material')
            ->with('material:id,title,type,syllabus_id')
            ->latest('accessed_at')
            ->limit(5)
            ->get();

        return view('student.materials.index', [
            'classes' => $classes,
            'summaries' => $summaries,
            'unlocked' => $summaries->map(fn ($summary) => $this->progress->unlockedMaterialIds($summary)),
            'recent' => $recent,
        ]);
    }

    /** Materials of one class grouped by meeting (locked ones greyed out). */
    public function byClass(Request $request, ClassName $class): View
    {
        $student = $request->user();
        abort_unless($this->scope->studentEnrollment($student, $class), 403, 'Anda tidak terdaftar di kelas ini.');

        $class->load(['course:id,name', 'curriculum:id,title']);
        $summary = $this->progress->summary($student, $class, true);
        $materialIds = $summary->meetings->flatMap(fn ($m) => $m['syllabus']->activeMaterials->pluck('id'));

        return view('student.materials.by-class', [
            'class' => $class,
            'summary' => $summary,
            'unlockedIds' => $this->progress->unlockedMaterialIds($summary),
            'accessStats' => $this->access->statsFor($student->id, $materialIds),
        ]);
    }

    /** Material viewer (records the visit). */
    public function show(Request $request, Material $material): View
    {
        $this->authorize('view', $material);

        $student = $request->user();
        $this->access->record($student, $material);

        $material->load('syllabus.curriculum:id,title');
        $classId = $this->scope->studentClassIdsForCurriculum($student, (int) $material->syllabus->curriculum_id)->first();

        return view('student.materials.show', [
            'material' => $material,
            'class' => $classId ? ClassName::find($classId, ['id', 'name']) : null,
            'stats' => $this->access->statsFor($student->id, [$material->id])->get($material->id),
            'hasFile' => $this->files->exists($material),
            'canInline' => $this->files->canInline($material),
            'fileSize' => $this->files->size($material),
        ]);
    }

    /** Download (or ?inline=1 view) the file (records the visit). */
    public function download(Request $request, Material $material): StreamedResponse
    {
        $this->authorize('download', $material);

        if (! $request->boolean('inline')) {
            $this->access->record($request->user(), $material);
        }

        return $this->files->response($material, $request->boolean('inline'));
    }

    /** Progress in one class: meetings, status and attendance summary. */
    public function progress(Request $request, ClassName $class): View
    {
        $student = $request->user();
        abort_unless($this->scope->studentEnrollment($student, $class), 403, 'Anda tidak terdaftar di kelas ini.');

        $class->load(['course:id,name', 'curriculum:id,title', 'teachers:id,name']);

        $attendance = Attendance::query()
            ->where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('student.progress.class', [
            'class' => $class,
            'summary' => $this->progress->summary($student, $class),
            'attendance' => $attendance,
            'statuses' => ProgressService::statuses(),
        ]);
    }
}
