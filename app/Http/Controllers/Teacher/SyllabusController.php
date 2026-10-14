<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassName;
use App\Models\Curriculum;
use App\Models\Syllabus;
use App\Services\Academic\AcademicScope;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Guru › Silabus: the meetings of the curricula the teacher's classes use.
 */
class SyllabusController extends Controller
{
    public function __construct(private readonly AcademicScope $scope)
    {
    }

    /** Meetings with filters (curriculum, search). */
    public function index(Request $request): View
    {
        $curriculumIds = $this->scope->teacherCurriculumIds($request->user());
        $curriculumId = $curriculumIds->contains($request->integer('curriculum_id')) ? $request->integer('curriculum_id') : null;

        $syllabuses = Syllabus::query()->active()
            ->whereIn('curriculum_id', $curriculumId ? [$curriculumId] : $curriculumIds)
            ->with('curriculum:id,title,course_id', 'curriculum.course:id,name')
            ->withCount(['materials as active_materials_count' => fn ($q) => $q->where('status', 'active')])
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.trim((string) $request->query('search')).'%'))
            ->orderBy('curriculum_id')
            ->orderBy('meeting_number')
            ->paginate(20)
            ->withQueryString();

        return view('teacher.syllabuses.index', [
            'syllabuses' => $syllabuses,
            'curriculumOptions' => Curriculum::query()->whereIn('id', $curriculumIds)->orderBy('title')->pluck('title', 'id'),
        ]);
    }

    /** One meeting: objectives, activities, materials and a report shortcut. */
    public function show(Request $request, Syllabus $syllabus): View
    {
        $this->authorize('view', $syllabus);

        $syllabus->load(['curriculum.course:id,name', 'activeMaterials']);

        $siblings = Syllabus::query()->active()
            ->where('curriculum_id', $syllabus->curriculum_id)
            ->orderBy('meeting_number')
            ->get(['id', 'meeting_number', 'title']);
        $position = $siblings->search(fn ($s) => $s->id === $syllabus->id);

        $classes = ClassName::query()
            ->whereIn('id', $this->scope->teacherClassIds($request->user()))
            ->where('curriculum_id', $syllabus->curriculum_id)
            ->whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('teacher.syllabuses.show', [
            'syllabus' => $syllabus,
            'previous' => $position > 0 ? $siblings[$position - 1] : null,
            'next' => $siblings[$position + 1] ?? null,
            'classes' => $classes,
        ]);
    }
}
