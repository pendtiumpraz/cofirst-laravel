<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\MaterialRequest;
use App\Models\Curriculum;
use App\Models\Material;
use App\Models\MaterialAccess;
use App\Models\Syllabus;
use App\Services\Academic\AcademicPrivacy;
use App\Services\Academic\AcademicSettings;
use App\Services\Academic\MaterialFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin › Materi: learning materials per meeting, with ordering, private file
 * storage and access statistics.
 */
class MaterialController extends Controller
{
    public function __construct(private readonly MaterialFileService $files)
    {
    }

    /** List materials with filters. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Material::class);

        $materials = Material::query()
            ->with('syllabus:id,curriculum_id,meeting_number,title', 'syllabus.curriculum:id,title,course_id', 'syllabus.curriculum.course:id,name')
            ->when($request->integer('curriculum_id'), fn ($q, $id) => $q->whereHas('syllabus', fn ($s) => $s->where('curriculum_id', $id)))
            ->when($request->integer('syllabus_id'), fn ($q, $id) => $q->where('syllabus_id', $id))
            ->when(array_key_exists((string) $request->query('type'), Material::getTypes()), fn ($q) => $q->where('type', $request->query('type')))
            ->when(in_array($request->query('status'), ['active', 'inactive'], true), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.trim((string) $request->query('search')).'%'))
            ->orderBy('syllabus_id')
            ->orderBy('order')
            ->paginate(20)
            ->withQueryString();

        return view('admin.materials.index', [
            'materials' => $materials,
            'curriculumOptions' => Curriculum::query()->with('course:id,name')->orderBy('title')->get()
                ->mapWithKeys(fn ($c) => [$c->id => ($c->course?->name ? $c->course->name.' · ' : '').$c->title])->all(),
            'types' => Material::getTypes(),
        ]);
    }

    /** New material form (preselects ?syllabus_id). */
    public function create(Request $request): View
    {
        $this->authorize('create', Material::class);

        $syllabus = $request->integer('syllabus_id') ? Syllabus::find($request->integer('syllabus_id')) : null;

        return view('admin.materials.create', [
            'material' => new Material([
                'syllabus_id' => $syllabus?->id,
                'type' => 'document',
                'status' => 'active',
                'meeting_start' => $syllabus?->meeting_number,
                'meeting_end' => $syllabus?->meeting_number,
                'order' => $syllabus ? (int) Material::where('syllabus_id', $syllabus->id)->max('order') + 1 : 1,
            ]),
        ] + $this->formOptions());
    }

    /** Save a new material (file on the private disk). */
    public function store(MaterialRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except(['file', 'remove_file'])->all();

        if ($request->hasFile('file')) {
            $data['file_path'] = $this->files->store($request->file('file'));
        }

        $material = Material::create($data);

        return redirect()->route('admin.syllabuses.show', $material->syllabus_id)
            ->with('success', 'Materi "'.$material->title.'" ditambahkan.');
    }

    /** Material detail with preview and access statistics (names only). */
    public function show(Material $material): View
    {
        $this->authorize('view', $material);

        $material->load(['syllabus.curriculum.course:id,name']);

        $stats = MaterialAccess::query()
            ->where('material_id', $material->id)
            ->selectRaw('count(*) as views, count(distinct student_id) as students, max(accessed_at) as last_at')
            ->first();

        $recent = MaterialAccess::query()
            ->where('material_id', $material->id)
            ->with('student:id,name')
            ->latest('accessed_at')
            ->limit(10)
            ->get(['id', 'student_id', 'material_id', 'accessed_at']);

        return view('admin.materials.show', [
            'material' => $material,
            'stats' => $stats,
            'recent' => $recent,
            'hasFile' => $this->files->exists($material),
            'fileSize' => $this->files->size($material),
            'canInline' => $this->files->canInline($material),
            'reportCount' => $material->classReports()->count(),
        ]);
    }

    /** Edit form. */
    public function edit(Material $material): View
    {
        $this->authorize('update', $material);

        return view('admin.materials.edit', [
            'material' => $material,
            'hasFile' => $this->files->exists($material),
        ] + $this->formOptions());
    }

    /** Save changes; replace or remove the file. */
    public function update(MaterialRequest $request, Material $material): RedirectResponse
    {
        $data = collect($request->validated())->except(['file', 'remove_file'])->all();
        $oldPath = $material->file_path;

        if ($request->hasFile('file')) {
            $data['file_path'] = $this->files->store($request->file('file'));
        } elseif ($request->boolean('remove_file')) {
            $data['file_path'] = null;
        }

        $material->update($data);

        if ($oldPath && $oldPath !== $material->file_path) {
            $this->files->delete($oldPath);
        }

        return redirect()->route('admin.materials.show', $material)->with('success', 'Materi diperbarui.');
    }

    /** Archive (soft delete): reports that used it keep their history. */
    public function destroy(Material $material): RedirectResponse
    {
        $this->authorize('delete', $material);

        $syllabusId = $material->syllabus_id;
        $material->delete();

        AcademicPrivacy::audit('academic.material.archived', $material, ['title' => $material->title]);

        return redirect()->route('admin.syllabuses.show', $syllabusId)->with('success', 'Materi diarsipkan.');
    }

    /** Activate or deactivate. */
    public function toggleStatus(Material $material): RedirectResponse
    {
        $this->authorize('update', $material);

        $material->update(['status' => $material->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', $material->status === 'active' ? 'Materi diaktifkan.' : 'Materi dinonaktifkan.');
    }

    /** Move a material one place up or down inside its meeting. */
    public function move(Request $request, Material $material): RedirectResponse
    {
        $this->authorize('update', $material);

        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];

        DB::transaction(function () use ($material, $direction) {
            // Reason: normalise to 1..n first so duplicate/gapped orders cannot block a move.
            $ordered = Material::where('syllabus_id', $material->syllabus_id)->orderBy('order')->orderBy('id')->lockForUpdate()->get()->values();
            $index = $ordered->search(fn ($m) => $m->id === $material->id);
            $swap = $direction === 'up' ? $index - 1 : $index + 1;

            if ($swap >= 0 && $swap < $ordered->count()) {
                [$ordered[$index], $ordered[$swap]] = [$ordered[$swap], $ordered[$index]];
            }

            foreach ($ordered as $position => $item) {
                if ((int) $item->order !== $position + 1) {
                    $item->update(['order' => $position + 1]);
                }
            }
        });

        return back()->with('success', 'Urutan materi diperbarui.');
    }

    /** Download (or ?inline=1 view) the file from the private disk. */
    public function download(Request $request, Material $material): StreamedResponse
    {
        $this->authorize('view', $material);

        return $this->files->response($material, $request->boolean('inline'));
    }

    /** Active materials of a meeting (JSON for cascading selects). */
    public function getBySyllabus(Syllabus $syllabus): JsonResponse
    {
        $this->authorize('view', $syllabus);

        return response()->json(
            $syllabus->activeMaterials()->get(['id', 'title', 'type', 'meeting_start', 'meeting_end'])
        );
    }

    /**
     * Options shared by the create and edit forms.
     *
     * Returns:
     *     array<string, mixed>
     */
    private function formOptions(): array
    {
        $syllabusOptions = Syllabus::query()
            ->with('curriculum:id,title,course_id', 'curriculum.course:id,name')
            ->whereHas('curriculum')
            ->orderBy('curriculum_id')->orderBy('meeting_number')
            ->get(['id', 'curriculum_id', 'meeting_number', 'title'])
            ->groupBy(fn ($s) => ($s->curriculum->course?->name ? $s->curriculum->course->name.' · ' : '').$s->curriculum->title)
            ->map(fn ($group) => $group->mapWithKeys(fn ($s) => [$s->id => 'Pertemuan '.$s->meeting_number.' — '.$s->title])->all())
            ->all();

        return [
            'syllabusOptions' => $syllabusOptions,
            'types' => Material::getTypes(),
            'accept' => MaterialFileService::acceptAttribute(),
            'maxKb' => AcademicSettings::maxUploadKb(),
        ];
    }
}
