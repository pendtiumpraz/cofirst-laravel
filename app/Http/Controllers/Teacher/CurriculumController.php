<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\MeetingProgressRequest;
use App\Models\ClassName;
use App\Models\Curriculum;
use App\Models\Material;
use App\Models\Syllabus;
use App\Services\Academic\AcademicScope;
use App\Services\Academic\ProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Guru › Kurikulum: the curricula of the teacher's classes (read-only), plus the
 * per-class meeting progress the teacher keeps up to date.
 */
class CurriculumController extends Controller
{
    public function __construct(
        private readonly AcademicScope $scope,
        private readonly ProgressService $progress,
    ) {
    }

    /** Curricula used by the teacher's classes. */
    public function index(Request $request): View
    {
        $teacher = $request->user();
        $curriculumIds = $this->scope->teacherCurriculumIds($teacher);

        $curriculums = Curriculum::query()
            ->whereIn('id', $curriculumIds)
            ->with('course:id,name')
            ->withCount(['activeSyllabuses', 'activeMaterials'])
            ->orderBy('title')
            ->paginate(12);

        $classes = ClassName::query()
            ->whereIn('id', $this->scope->teacherClassIds($teacher))
            ->whereIn('curriculum_id', $curriculums->pluck('id'))
            ->withCount('activeEnrollments')
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'curriculum_id'])
            ->groupBy('curriculum_id');

        return view('teacher.curriculums.index', compact('curriculums', 'classes'));
    }

    /** Curriculum detail: meetings with materials and the teacher's classes. */
    public function show(Request $request, Curriculum $curriculum): View
    {
        $this->authorize('view', $curriculum);

        $curriculum->load(['course:id,name', 'activeSyllabuses.activeMaterials']);

        $classes = ClassName::query()
            ->whereIn('id', $this->scope->teacherClassIds($request->user()))
            ->where('curriculum_id', $curriculum->id)
            ->withCount('activeEnrollments')
            ->orderBy('name')
            ->get();

        return view('teacher.curriculums.show', [
            'curriculum' => $curriculum,
            'classes' => $classes,
            'rates' => $this->progress->classCompletionRates($classes),
        ]);
    }

    /** Table of the curriculum's meetings. */
    public function syllabuses(Curriculum $curriculum): View
    {
        $this->authorize('view', $curriculum);

        $syllabuses = $curriculum->activeSyllabuses()
            ->withCount(['materials as active_materials_count' => fn ($q) => $q->where('status', 'active')])
            ->get();

        return view('teacher.curriculums.syllabuses', compact('curriculum', 'syllabuses'));
    }

    /** The curriculum's materials grouped by meeting, filterable by type. */
    public function materials(Request $request, Curriculum $curriculum): View
    {
        $this->authorize('view', $curriculum);

        $type = array_key_exists((string) $request->query('type'), Material::getTypes()) ? $request->query('type') : null;

        $syllabuses = $curriculum->activeSyllabuses()
            ->with(['activeMaterials' => fn ($q) => $q->when($type, fn ($m) => $m->where('type', $type))])
            ->get()
            ->filter(fn (Syllabus $s) => $s->activeMaterials->isNotEmpty());

        return view('teacher.curriculums.materials', [
            'curriculum' => $curriculum,
            'syllabuses' => $syllabuses,
            'types' => Material::getTypes(),
            'type' => $type,
        ]);
    }

    /** Meeting-by-meeting progress of one of the teacher's classes. */
    public function classProgress(Request $request, ClassName $class): View
    {
        abort_unless($this->scope->teacherTeaches($request->user(), $class), 403, 'Anda tidak mengajar kelas ini.');

        $class->load(['course:id,name', 'curriculum:id,title']);

        return view('teacher.curriculums.class-progress', [
            'class' => $class,
            'meetings' => $this->progress->classOverview($class),
            'rate' => $this->progress->classCompletionRates(collect([$class]))->get($class->id),
            'statuses' => ProgressService::statuses(),
        ]);
    }

    /** Set one meeting's status for every active student of the class. */
    public function updateClassProgress(MeetingProgressRequest $request, ClassName $class): RedirectResponse
    {
        abort_unless($this->scope->teacherTeaches($request->user(), $class), 403, 'Anda tidak mengajar kelas ini.');

        $syllabus = Syllabus::query()
            ->where('curriculum_id', $class->curriculum_id)
            ->where('meeting_number', $request->integer('meeting_number'))
            ->first();

        if (! $class->curriculum_id || ! $syllabus) {
            return back()->with('error', 'Pertemuan tidak ditemukan di kurikulum kelas ini.');
        }

        $count = $this->progress->markMeeting($class, $syllabus, $request->validated('status'));
        $label = mb_strtolower(ProgressService::statuses()[$request->validated('status')]);

        return back()->with('success', "Pertemuan {$syllabus->meeting_number} ditandai {$label} untuk {$count} siswa.");
    }
}
