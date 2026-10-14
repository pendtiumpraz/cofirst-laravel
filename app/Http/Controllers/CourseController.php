<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\CourseRequest;
use App\Models\ClassName;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\FinancialTransaction;
use App\Services\Admin\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Admin › Kursus: the course catalogue.
 */
class CourseController extends Controller
{
    private const SORTS = ['name', 'price', 'created_at', 'classes_count'];

    private const THUMB_DIR = 'course-thumbnails';

    /**
     * List courses with search, level/status filters and sorting.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Course::class);

        $sorted = in_array($request->query('sort'), self::SORTS, true);
        $sort = $sorted ? $request->query('sort') : 'name';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $courses = Course::query()
            ->withCount([
                'classes',
                'classes as running_classes_count' => fn ($q) => $q->whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE]),
            ])
            ->when($request->query('status') === 'archived', fn ($q) => $q->onlyTrashed())
            ->when($request->query('status') === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->query('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->filled('level'), fn ($q) => $q->where('level', $request->query('level')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], trim((string) $request->query('search'))).'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('description', 'like', $term));
            })
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.courses.index', [
            'courses' => $courses,
            'levels' => CourseRequest::LEVELS,
            'stats' => [
                'total' => Course::count(),
                'active' => Course::where('is_active', true)->count(),
                'running_classes' => ClassName::whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE])->count(),
                'archived' => Course::onlyTrashed()->count(),
            ],
        ]);
    }

    /**
     * Create form.
     */
    public function create(): View
    {
        $this->authorize('create', Course::class);

        return view('admin.courses.create', ['course' => new Course(['is_active' => true, 'duration_weeks' => 12]), 'levels' => CourseRequest::LEVELS]);
    }

    /**
     * Store a course.
     */
    public function store(CourseRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['thumbnail', 'remove_thumbnail']);
        $data['slug'] = $data['slug'] ?: Course::uniqueSlug($data['name']);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store(self::THUMB_DIR, 'public');
        }

        $course = Course::create($data);
        AdminAudit::log('course.created', $course);

        return redirect()->route('admin.courses.show', $course)->with('success', "Kursus {$course->name} berhasil dibuat.");
    }

    /**
     * Course detail with real statistics.
     */
    public function show(Course $course): View
    {
        $this->authorize('view', $course);

        $classes = $course->classes()
            ->with('teachers:id,name')
            ->withSeatsTaken()
            ->orderByRaw("case status when 'active' then 0 when 'planned' then 1 when 'completed' then 2 else 3 end")
            ->orderByDesc('start_date')
            ->get();

        $classIds = $classes->pluck('id');
        $enrollmentQuery = Enrollment::query()->where(fn ($q) => $q->where('course_id', $course->id)->orWhereIn('class_id', $classIds));

        $paid = FinancialTransaction::query()->where('course_id', $course->id)->settled();
        $revenue = (int) (clone $paid)->payments()->sum('amount') - (int) (clone $paid)->refunds()->sum('amount');

        $stats = [
            'enrollments' => (clone $enrollmentQuery)->count(),
            'active_students' => (clone $enrollmentQuery)->where('status', Enrollment::STATUS_ACTIVE)->distinct()->count('student_id'),
            'running_classes' => $classes->whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE])->count(),
            'revenue' => $revenue,
        ];

        $curriculums = $course->curriculums()->orderBy('id')->get();

        return view('admin.courses.show', compact('course', 'classes', 'stats', 'curriculums'));
    }

    /**
     * Edit form.
     */
    public function edit(Course $course): View
    {
        $this->authorize('update', $course);

        return view('admin.courses.edit', ['course' => $course, 'levels' => CourseRequest::LEVELS]);
    }

    /**
     * Update a course.
     */
    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $data = $request->safe()->except(['thumbnail', 'remove_thumbnail']);
        $data['slug'] = $data['slug'] ?: ($course->name !== $data['name'] ? Course::uniqueSlug($data['name'], $course->id) : $course->slug);

        $old = $course->thumbnail;
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store(self::THUMB_DIR, 'public');
        } elseif ($request->boolean('remove_thumbnail')) {
            $data['thumbnail'] = null;
        }

        $course->update($data);

        if ($old && array_key_exists('thumbnail', $data) && $data['thumbnail'] !== $old) {
            Storage::disk('public')->delete($old);
        }

        AdminAudit::log('course.updated', $course, ['fields' => array_keys($course->getChanges())]);

        return redirect()->route('admin.courses.show', $course)->with('success', "Perubahan kursus {$course->name} disimpan.");
    }

    /**
     * Archive (soft delete) a course that has no running classes.
     */
    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $running = $course->classes()->whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE])->count();
        if ($running > 0) {
            return back()->with('error', "Kursus ini masih punya {$running} kelas yang direncanakan/berlangsung. Selesaikan atau batalkan kelasnya terlebih dahulu.");
        }

        DB::transaction(function () use ($course) {
            $course->update(['is_active' => false]);
            $course->delete();
        });
        AdminAudit::log('course.archived', $course);

        return redirect()->route('admin.courses.index')->with('success', "Kursus {$course->name} diarsipkan. Riwayat kelas dan pembayaran tetap tersimpan.");
    }

    /**
     * Restore an archived course (inactive until re-activated).
     */
    public function restore(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $course->restore();
        AdminAudit::log('course.restored', $course);

        return redirect()->route('admin.courses.show', $course)->with('success', "Kursus {$course->name} dipulihkan (masih nonaktif).");
    }

    /**
     * Show or hide a course from the catalogue.
     */
    public function toggleStatus(Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $course->update(['is_active' => ! $course->is_active]);
        AdminAudit::log($course->is_active ? 'course.activated' : 'course.deactivated', $course);

        return back()->with('success', $course->is_active
            ? "Kursus {$course->name} diaktifkan."
            : "Kursus {$course->name} dinonaktifkan dan tidak tampil di katalog.");
    }
}
