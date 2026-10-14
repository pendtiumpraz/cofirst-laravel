<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\SyllabusRequest;
use App\Models\ClassProgress;
use App\Models\ClassReport;
use App\Models\Curriculum;
use App\Models\Syllabus;
use App\Services\Academic\AcademicPrivacy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin › Silabus: the meetings (pertemuan) of each curriculum, with objectives,
 * activities, duration and materials.
 */
class SyllabusController extends Controller
{
    /** List meetings with filters. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Syllabus::class);

        $syllabuses = Syllabus::query()
            ->with('curriculum:id,title,course_id', 'curriculum.course:id,name')
            ->withCount('materials')
            ->when($request->integer('curriculum_id'), fn ($q, $id) => $q->where('curriculum_id', $id))
            ->when(in_array($request->query('status'), ['active', 'inactive'], true), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.trim((string) $request->query('search')).'%'))
            ->orderBy('curriculum_id')
            ->orderBy('meeting_number')
            ->paginate(20)
            ->withQueryString();

        return view('admin.syllabuses.index', [
            'syllabuses' => $syllabuses,
            'curriculumOptions' => $this->curriculumOptions(),
        ]);
    }

    /** New meeting form (preselects ?curriculum_id and the next meeting number). */
    public function create(Request $request): View
    {
        $this->authorize('create', Syllabus::class);

        $curriculumId = $request->integer('curriculum_id') ?: null;
        $next = $curriculumId ? (int) Syllabus::where('curriculum_id', $curriculumId)->max('meeting_number') + 1 : 1;

        return view('admin.syllabuses.create', [
            'syllabus' => new Syllabus([
                'curriculum_id' => $curriculumId,
                'meeting_number' => $next,
                'duration_minutes' => (int) setting('academic.session_duration_minutes', 90),
                'status' => 'active',
            ]),
            'curriculumOptions' => $this->curriculumOptions(),
        ]);
    }

    /** Save a new meeting. */
    public function store(SyllabusRequest $request): RedirectResponse
    {
        $syllabus = Syllabus::create($request->validated());

        return redirect()->route('admin.syllabuses.show', $syllabus)
            ->with('success', "Pertemuan {$syllabus->meeting_number} dibuat. Tambahkan materinya di bawah.");
    }

    /** Meeting detail with materials (ordering) and neighbours. */
    public function show(Syllabus $syllabus): View
    {
        $this->authorize('view', $syllabus);

        $syllabus->load(['curriculum.course:id,name', 'materials']);

        $siblings = Syllabus::where('curriculum_id', $syllabus->curriculum_id)
            ->orderBy('meeting_number')
            ->get(['id', 'meeting_number', 'title']);
        $position = $siblings->search(fn ($s) => $s->id === $syllabus->id);

        return view('admin.syllabuses.show', [
            'syllabus' => $syllabus,
            'previous' => $position > 0 ? $siblings[$position - 1] : null,
            'next' => $siblings[$position + 1] ?? null,
            'reportCount' => ClassReport::where('syllabus_id', $syllabus->id)->count(),
        ]);
    }

    /** Edit form. */
    public function edit(Syllabus $syllabus): View
    {
        $this->authorize('update', $syllabus);

        return view('admin.syllabuses.edit', [
            'syllabus' => $syllabus,
            'curriculumOptions' => $this->curriculumOptions(),
        ]);
    }

    /** Save changes. */
    public function update(SyllabusRequest $request, Syllabus $syllabus): RedirectResponse
    {
        $syllabus->update($request->validated());

        return redirect()->route('admin.syllabuses.show', $syllabus)->with('success', 'Pertemuan diperbarui.');
    }

    /**
     * Archive a meeting and its materials.
     *
     * Reason: progress rows and meeting reports point to the meeting; a meeting that
     * was already taught is kept (deactivate it instead) so history stays intact.
     */
    public function destroy(Syllabus $syllabus): RedirectResponse
    {
        $this->authorize('delete', $syllabus);

        $used = ClassReport::where('syllabus_id', $syllabus->id)->count()
            + ClassProgress::where('syllabus_id', $syllabus->id)->where('status', '!=', 'not_started')->count();

        if ($used > 0) {
            return back()->with('error', 'Pertemuan ini sudah diajarkan dan tercatat di laporan/progres siswa. Nonaktifkan saja agar riwayat tetap utuh.');
        }

        $curriculum = $syllabus->curriculum;

        DB::transaction(function () use ($syllabus) {
            $syllabus->materials()->get()->each->delete();
            ClassProgress::where('syllabus_id', $syllabus->id)->delete();
            $syllabus->delete();
        });

        AcademicPrivacy::audit('academic.syllabus.archived', $syllabus, ['title' => $syllabus->title]);

        return $curriculum
            ? redirect()->route('admin.curriculums.show', $curriculum)->with('success', 'Pertemuan diarsipkan.')
            : redirect()->route('admin.syllabuses.index')->with('success', 'Pertemuan diarsipkan.');
    }

    /** Activate or deactivate. */
    public function toggleStatus(Syllabus $syllabus): RedirectResponse
    {
        $this->authorize('update', $syllabus);

        $syllabus->update(['status' => $syllabus->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', $syllabus->status === 'active' ? 'Pertemuan diaktifkan.' : 'Pertemuan dinonaktifkan.');
    }

    /** Active meetings of a curriculum (JSON for cascading selects). */
    public function getByCurriculum(Curriculum $curriculum): JsonResponse
    {
        $this->authorize('view', $curriculum);

        return response()->json(
            $curriculum->syllabuses()->active()->get(['id', 'title', 'meeting_number'])
        );
    }

    /**
     * Curricula grouped for a select, e.g. "Python · Regular".
     *
     * Returns:
     *     array<int, string>
     */
    private function curriculumOptions(): array
    {
        return Curriculum::query()
            ->with('course:id,name')
            ->orderBy('course_id')->orderBy('title')
            ->get(['id', 'title', 'course_id', 'status'])
            ->mapWithKeys(fn (Curriculum $c) => [$c->id => trim(($c->course?->name ? $c->course->name.' · ' : '').$c->title.($c->status === 'inactive' ? ' (nonaktif)' : ''))])
            ->all();
    }
}
