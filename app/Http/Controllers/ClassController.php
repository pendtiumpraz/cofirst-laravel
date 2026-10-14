<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AddClassStudentRequest;
use App\Http\Requests\Admin\ClassRequest;
use App\Models\Attendance;
use App\Models\ClassName;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\User;
use App\Services\Admin\AdminAudit;
use App\Services\Admin\ClassRosterService;
use App\Services\Scheduling\ScheduleSlots;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin › Kelas: classes, their teachers (class_teacher pivot), roster and slots.
 */
class ClassController extends Controller
{
    private const SORTS = ['name', 'start_date', 'seats_taken', 'created_at'];

    private const PHOTO_DIR = 'class-photos';

    public function __construct(private readonly ClassRosterService $roster)
    {
    }

    /**
     * List classes with filters (status, type, course, teacher) and sorting.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ClassName::class);

        $sorted = in_array($request->query('sort'), self::SORTS, true);
        $sort = $sorted ? $request->query('sort') : 'start_date';
        $direction = $sorted ? ($request->query('direction') === 'desc' ? 'desc' : 'asc') : 'desc';

        $classes = ClassName::query()
            ->with(['course:id,name', 'teachers:id,name,profile_photo_path'])
            ->withSeatsTaken()
            ->when($request->query('status') === 'archived', fn ($q) => $q->onlyTrashed())
            ->when(in_array($request->query('status'), array_keys(ClassRequest::STATUSES), true), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('course'), fn ($q) => $q->where('course_id', (int) $request->query('course')))
            ->when($request->filled('teacher'), fn ($q) => $q->taughtBy((int) $request->query('teacher')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], trim((string) $request->query('search'))).'%';
                $q->where('name', 'like', $term);
            })
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        $counts = ClassName::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.classes.index', [
            'classes' => $classes,
            'statuses' => ClassRequest::STATUSES,
            'types' => ClassName::types(),
            'courses' => Course::orderBy('name')->pluck('name', 'id'),
            'teachers' => User::role('teacher')->orderBy('name')->pluck('name', 'id'),
            'counts' => $counts,
        ]);
    }

    /**
     * Create form.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', ClassName::class);

        $class = new ClassName([
            'status' => ClassName::STATUS_PLANNED,
            'course_id' => $request->integer('course') ?: null,
            'delivery_method' => 'offline',
        ]);

        return view('admin.classes.create', $this->formData($class));
    }

    /**
     * Store a class with its teachers.
     */
    public function store(ClassRequest $request): RedirectResponse
    {
        $data = $this->classData($request);
        $storedPhoto = $request->hasFile('photo') ? $request->file('photo')->store(self::PHOTO_DIR, 'public') : null;
        if ($storedPhoto) {
            $data['photo_path'] = $storedPhoto;
        }

        try {
            $class = DB::transaction(function () use ($data, $request) {
                $class = ClassName::create($data);
                $class->syncTeachers($request->input('teacher_ids', []));

                return $class;
            });
        } catch (\Throwable $e) {
            if ($storedPhoto) {
                Storage::disk('public')->delete($storedPhoto);
            }
            throw $e;
        }

        AdminAudit::log('class.created', $class, ['teachers' => count($request->input('teacher_ids', []))]);

        return redirect()->route('admin.classes.show', $class)->with('success', "Kelas {$class->name} berhasil dibuat.");
    }

    /**
     * Class detail: summary, students, weekly slots and teachers.
     */
    public function show(ClassName $class): View
    {
        $this->authorize('view', $class);

        $class->load(['course:id,name', 'curriculum:id,title', 'teachers:id,name,email,profile_photo_path']);
        $class->loadCount(['enrollments as seats_taken' => fn ($q) => $q->whereIn('status', Enrollment::SEAT_STATUSES)]);

        $students = $class->enrollments()
            ->whereIn('status', Enrollment::SEAT_STATUSES)
            ->with(['student:id,name,profile_photo_path', 'student.profile:id,user_id,birth_date'])
            ->orderBy('enrollment_date')
            ->limit(12)
            ->get();

        $rows = Schedule::where('class_id', $class->id)
            ->with(['teacherAssignment.teacher:id,name', 'enrollment.student:id,name'])
            ->get()
            ->each(fn (Schedule $row) => $row->setRelation('className', $class));
        $slots = ScheduleSlots::group($rows);

        $byStatus = Attendance::where('class_id', $class->id)
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $recorded = (int) $byStatus->sum();
        $attended = (int) $byStatus->only(Attendance::ATTENDED_STATUSES)->sum();

        return view('admin.classes.show', [
            'class' => $class,
            'students' => $students,
            'slots' => $slots,
            'attendanceRate' => $recorded === 0 ? null : round($attended / $recorded * 100, 1),
            'meetings' => $class->classReports()->distinct()->count('report_date'),
        ]);
    }

    /**
     * Edit form.
     */
    public function edit(ClassName $class): View
    {
        $this->authorize('update', $class);

        $class->load('teachers:id');

        return view('admin.classes.edit', $this->formData($class));
    }

    /**
     * Update a class; teacher changes go through syncTeachers().
     */
    public function update(ClassRequest $request, ClassName $class): RedirectResponse
    {
        $data = $this->classData($request);
        $old = $class->photo_path;

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store(self::PHOTO_DIR, 'public');
        } elseif ($request->boolean('remove_photo')) {
            $data['photo_path'] = null;
        }

        DB::transaction(function () use ($class, $data, $request) {
            $class->update($data);

            // Reason: compare as sorted ints — the old code compared int vs string
            // arrays and re-created every assignment on each save.
            $current = $class->teachers()->pluck('users.id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $new = collect($request->input('teacher_ids', []))->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
            if ($current !== $new) {
                $class->syncTeachers($new);
            }
        });

        if ($old && array_key_exists('photo_path', $data) && $data['photo_path'] !== $old) {
            Storage::disk('public')->delete($old);
        }

        AdminAudit::log('class.updated', $class, ['fields' => array_keys($class->getChanges())]);

        return redirect()->route('admin.classes.show', $class)->with('success', "Perubahan kelas {$class->name} disimpan.");
    }

    /**
     * Archive (soft delete) a class that has no students holding a seat.
     */
    public function destroy(ClassName $class): RedirectResponse
    {
        $this->authorize('delete', $class);

        $seats = $class->seatsTaken();
        if ($seats > 0) {
            return back()->with('error', "Kelas ini masih punya {$seats} siswa aktif/menunggu. Pindahkan atau keluarkan siswanya, atau ubah status kelas menjadi Selesai/Dibatalkan.");
        }

        DB::transaction(function () use ($class) {
            $class->schedules()->update(['is_active' => false]);
            $class->update(['is_active' => false]);
            $class->delete();
        });
        AdminAudit::log('class.archived', $class);

        return redirect()->route('admin.classes.index')->with('success', "Kelas {$class->name} diarsipkan. Absensi, laporan dan sertifikatnya tetap tersimpan.");
    }

    /**
     * Restore an archived class.
     */
    public function restore(ClassName $class): RedirectResponse
    {
        $this->authorize('delete', $class);

        $class->restore();
        $class->update(['is_active' => $class->status !== ClassName::STATUS_CANCELLED]);
        AdminAudit::log('class.restored', $class);

        return redirect()->route('admin.classes.show', $class)->with('success', "Kelas {$class->name} dipulihkan.");
    }

    /**
     * Full roster with status filter.
     */
    public function students(Request $request, ClassName $class): View
    {
        $this->authorize('view', $class);

        $class->load('course:id,name');
        $class->loadCount(['enrollments as seats_taken' => fn ($q) => $q->whereIn('status', Enrollment::SEAT_STATUSES)]);

        $filter = $request->query('status', 'seat');
        $enrollments = $class->enrollments()
            ->with(['student:id,name,email,profile_photo_path', 'student.profile:id,user_id,birth_date'])
            ->when($filter === 'seat', fn ($q) => $q->whereIn('status', Enrollment::SEAT_STATUSES))
            ->when(array_key_exists($filter, Enrollment::statuses()), fn ($q) => $q->where('status', $filter))
            ->orderByRaw("case status when 'active' then 0 when 'pending' then 1 else 2 end")
            ->orderBy('enrollment_date')
            ->paginate(25)
            ->withQueryString();

        // Attendance rate per student in this class (one grouped query).
        $rates = Attendance::where('class_id', $class->id)
            ->whereIn('student_id', $enrollments->pluck('student_id'))
            ->get(['student_id', 'status'])
            ->groupBy('student_id')
            ->map(fn ($rows) => Attendance::rateFor($rows));

        AdminAudit::log('class.roster_viewed', $class, ['rows' => $enrollments->count()]);

        return view('admin.classes.students', compact('class', 'enrollments', 'rates', 'filter'));
    }

    /**
     * Add a student to the class (capacity-checked, transactional).
     */
    public function addStudent(AddClassStudentRequest $request, ClassName $class): RedirectResponse
    {
        $student = User::findOrFail($request->integer('student_id'));

        $this->roster->enroll($class, $student, [
            'status' => $request->input('status') ?: Enrollment::STATUS_ACTIVE,
            'payment_status' => $request->input('payment_status') ?: 'unpaid',
        ]);
        AdminAudit::log('class.student_added', $class, ['student_id' => $student->id]);

        return back()->with('success', "{$student->name} ditambahkan ke kelas {$class->name}.");
    }

    /**
     * Remove a student: the enrollment becomes "Keluar" (dropped) and their
     * schedule rows are deactivated; history is kept.
     */
    public function removeStudent(ClassName $class, User $student): RedirectResponse
    {
        $this->authorize('manageRoster', $class);

        $enrollment = Enrollment::where('class_id', $class->id)->where('student_id', $student->id)
            ->whereIn('status', Enrollment::SEAT_STATUSES)->first();

        if (! $enrollment) {
            return back()->with('error', 'Siswa ini tidak terdaftar aktif di kelas ini.');
        }

        $this->roster->changeStatus($enrollment, Enrollment::STATUS_DROPPED);
        AdminAudit::log('class.student_removed', $class, ['student_id' => $student->id]);

        return back()->with('success', "{$student->name} dikeluarkan dari kelas. Riwayat absensi dan laporannya tetap tersimpan.");
    }

    /**
     * Change the status of one roster entry (approve, suspend, complete…).
     */
    public function updateStudentStatus(Request $request, ClassName $class, Enrollment $enrollment): RedirectResponse
    {
        $this->authorize('manageRoster', $class);
        abort_unless((int) $enrollment->class_id === (int) $class->id, 404);

        $validated = $request->validate(['status' => ['required', Rule::in(array_keys(Enrollment::statuses()))]], [], ['status' => 'status']);
        $this->roster->changeStatus($enrollment, $validated['status']);
        AdminAudit::log('enrollment.status_changed', $enrollment, ['status' => $validated['status']]);

        return back()->with('success', 'Status siswa diperbarui menjadi '.Enrollment::statuses()[$validated['status']].'.');
    }

    /**
     * Curricula of a course for the class form (JSON; empty list when none).
     */
    public function getCurriculumByCourse(Course $course): JsonResponse
    {
        $this->authorize('viewAny', ClassName::class);

        return response()->json([
            'curriculums' => $course->curriculums()->orderBy('id')->get(['id', 'title'])
                ->map(fn ($c) => ['id' => $c->id, 'title' => $c->title])->values(),
        ]);
    }

    /**
     * Shared data for the create/edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(ClassName $class): array
    {
        $courses = Course::orderBy('name')->get(['id', 'name', 'is_active']);
        $courseId = old('course_id', $class->course_id);

        return [
            'class' => $class,
            'courses' => $courses,
            'curriculums' => $courseId ? \App\Models\Curriculum::where('course_id', $courseId)->orderBy('id')->pluck('title', 'id') : collect(),
            'teachers' => User::role('teacher')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'profile_photo_path']),
            'types' => ClassName::types(),
            'statuses' => ClassRequest::STATUSES,
            'delivery' => ClassRequest::DELIVERY,
            'suggested' => ClassRequest::TYPE_SUGGESTED,
            'typeMax' => ClassRequest::TYPE_CAPACITY,
        ];
    }

    /**
     * Column data from a validated class request.
     *
     * @return array<string, mixed>
     */
    private function classData(ClassRequest $request): array
    {
        $data = $request->safe()->only(['name', 'course_id', 'curriculum_id', 'type', 'delivery_method', 'description', 'start_date', 'end_date', 'max_students', 'status']);

        // Reason: one source of truth — a cancelled class is inactive, every other status is active.
        $data['is_active'] = $data['status'] !== ClassName::STATUS_CANCELLED;

        return $data;
    }
}
