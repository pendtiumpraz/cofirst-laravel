<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\CurriculumRequest;
use App\Models\ClassName;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Material;
use App\Services\Academic\AcademicPrivacy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin › Kurikulum: curricula of each course (a course may have several variants,
 * e.g. fast-track and regular). A curriculum is an ordered list of meetings
 * (syllabuses), each with materials.
 */
class CurriculumController extends Controller
{
    /** List curricula with search and filters. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Curriculum::class);

        $curriculums = Curriculum::query()
            ->with('course:id,name')
            ->withCount(['syllabuses', 'materials', 'classes'])
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.trim((string) $request->query('search')).'%'))
            ->when($request->integer('course_id'), fn ($q, $id) => $q->where('course_id', $id))
            ->when(array_key_exists((string) $request->query('type'), Curriculum::getTypes()), fn ($q) => $q->where('type', $request->query('type')))
            ->when(in_array($request->query('status'), ['active', 'inactive'], true), fn ($q) => $q->where('status', $request->query('status')))
            ->orderBy('course_id')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        $courses = Course::query()->orderBy('name')->pluck('name', 'id');

        return view('admin.curriculums.index', [
            'curriculums' => $curriculums,
            'courses' => $courses,
            'types' => Curriculum::getTypes(),
        ]);
    }

    /** New curriculum form. */
    public function create(Request $request): View
    {
        $this->authorize('create', Curriculum::class);

        return view('admin.curriculums.create', [
            'curriculum' => new Curriculum(['status' => 'active', 'type' => 'regular', 'course_id' => $request->integer('course_id') ?: null]),
            'courses' => Course::query()->orderBy('name')->pluck('name', 'id'),
            'types' => Curriculum::getTypes(),
        ]);
    }

    /** Save a new curriculum. */
    public function store(CurriculumRequest $request): RedirectResponse
    {
        $curriculum = Curriculum::create($request->validated());

        return redirect()->route('admin.curriculums.show', $curriculum)
            ->with('success', 'Kurikulum dibuat. Lanjutkan dengan menambahkan pertemuan.');
    }

    /** Curriculum detail: meetings with materials, and the classes that follow it. */
    public function show(Curriculum $curriculum): View
    {
        $this->authorize('view', $curriculum);

        $curriculum->load([
            'course:id,name',
            'syllabuses' => fn ($q) => $q->withCount('materials'),
            'classes' => fn ($q) => $q->with('teachers:id,name')->withCount('activeEnrollments')->orderBy('name'),
        ]);

        return view('admin.curriculums.show', ['curriculum' => $curriculum]);
    }

    /** Edit form. */
    public function edit(Curriculum $curriculum): View
    {
        $this->authorize('update', $curriculum);

        return view('admin.curriculums.edit', [
            'curriculum' => $curriculum,
            'courses' => Course::query()->orderBy('name')->pluck('name', 'id'),
            'types' => Curriculum::getTypes(),
        ]);
    }

    /** Save changes. */
    public function update(CurriculumRequest $request, Curriculum $curriculum): RedirectResponse
    {
        $curriculum->update($request->validated());

        return redirect()->route('admin.curriculums.show', $curriculum)
            ->with('success', 'Kurikulum diperbarui.');
    }

    /**
     * Archive a curriculum (soft delete) with its meetings and materials.
     *
     * Reason: classes reference the curriculum and reports reference its meetings, so a
     * curriculum in use is never removed; deactivate it instead.
     */
    public function destroy(Curriculum $curriculum): RedirectResponse
    {
        $this->authorize('delete', $curriculum);

        $classCount = ClassName::where('curriculum_id', $curriculum->id)->count();

        if ($classCount > 0) {
            return back()->with('error', "Kurikulum dipakai {$classCount} kelas. Nonaktifkan saja, atau ganti kurikulum kelas tersebut terlebih dahulu.");
        }

        DB::transaction(function () use ($curriculum) {
            $syllabusIds = $curriculum->syllabuses()->pluck('id');
            Material::whereIn('syllabus_id', $syllabusIds)->get()->each->delete();
            $curriculum->syllabuses()->get()->each->delete();
            $curriculum->delete();
        });

        AcademicPrivacy::audit('academic.curriculum.archived', $curriculum, ['title' => $curriculum->title]);

        return redirect()->route('admin.curriculums.index')
            ->with('success', 'Kurikulum diarsipkan beserta pertemuan dan materinya.');
    }

    /** Activate or deactivate. */
    public function toggleStatus(Curriculum $curriculum): RedirectResponse
    {
        $this->authorize('update', $curriculum);

        $curriculum->update(['status' => $curriculum->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', $curriculum->status === 'active' ? 'Kurikulum diaktifkan.' : 'Kurikulum dinonaktifkan.');
    }
}
