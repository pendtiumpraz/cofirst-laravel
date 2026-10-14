<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Material;
use App\Services\Academic\AcademicScope;
use App\Services\Academic\MaterialFileService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Guru › Materi: active materials of the curricula the teacher's classes use.
 */
class MaterialController extends Controller
{
    public function __construct(
        private readonly AcademicScope $scope,
        private readonly MaterialFileService $files,
    ) {
    }

    /** Materials with filters (curriculum, type, search). */
    public function index(Request $request): View
    {
        $curriculumIds = $this->scope->teacherCurriculumIds($request->user());
        $curriculumId = $curriculumIds->contains($request->integer('curriculum_id')) ? $request->integer('curriculum_id') : null;

        $materials = Material::query()->active()
            ->whereHas('syllabus', fn ($q) => $q->active()->whereIn('curriculum_id', $curriculumId ? [$curriculumId] : $curriculumIds))
            ->with('syllabus:id,curriculum_id,meeting_number,title', 'syllabus.curriculum:id,title')
            ->when(array_key_exists((string) $request->query('type'), Material::getTypes()), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.trim((string) $request->query('search')).'%'))
            ->join('syllabuses', 'syllabuses.id', '=', 'materials.syllabus_id')
            ->orderBy('syllabuses.curriculum_id')
            ->orderBy('syllabuses.meeting_number')
            ->orderBy('materials.order')
            ->select('materials.*')
            ->paginate(20)
            ->withQueryString();

        return view('teacher.materials.index', [
            'materials' => $materials,
            'curriculumOptions' => Curriculum::query()->whereIn('id', $curriculumIds)->orderBy('title')->pluck('title', 'id'),
            'types' => Material::getTypes(),
        ]);
    }

    /** Material viewer. */
    public function show(Material $material): View
    {
        $this->authorize('view', $material);

        $material->load('syllabus.curriculum.course:id,name');

        return view('teacher.materials.show', [
            'material' => $material,
            'hasFile' => $this->files->exists($material),
            'canInline' => $this->files->canInline($material),
            'fileSize' => $this->files->size($material),
        ]);
    }

    /** Download (or ?inline=1 view) the file from the private disk. */
    public function download(Request $request, Material $material): StreamedResponse
    {
        $this->authorize('download', $material);

        return $this->files->response($material, $request->boolean('inline'));
    }
}
