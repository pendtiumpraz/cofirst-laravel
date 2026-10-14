<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\EnrollmentRequest;
use App\Models\ClassName;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\Admin\AdminAudit;
use App\Services\Admin\ClassRosterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin › Pendaftaran: enrollments, the approval queue for pending
 * registrations, status changes and class moves.
 */
class EnrollmentController extends Controller
{
    private const SORTS = ['enrollment_date', 'created_at', 'status'];

    public function __construct(private readonly ClassRosterService $roster)
    {
    }

    /**
     * List enrollments with search and filters.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Enrollment::class);

        $enrollments = $this->filtered($request)
            ->with(['student:id,name,email,profile_photo_path', 'className:id,name,course_id,status', 'className.course:id,name', 'course:id,name'])
            ->paginate(20)
            ->withQueryString();

        $counts = Enrollment::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        AdminAudit::log('enrollment.list_viewed', null, ['rows' => $enrollments->count()]);

        return view('admin.enrollments.index', [
            'enrollments' => $enrollments,
            'counts' => $counts,
            'statuses' => Enrollment::statuses(),
            'paymentStatuses' => EnrollmentRequest::PAYMENT_STATUSES,
            'classes' => ClassName::orderBy('name')->pluck('name', 'id'),
            'courses' => Course::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Export the filtered list as CSV (audited; minimum columns).
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Enrollment::class);

        $rows = $this->filtered($request)->with(['student:id,name', 'className:id,name', 'course:id,name'])->get();
        AdminAudit::log('enrollment.exported', null, ['rows' => $rows->count(), 'filters' => array_keys(array_filter($request->only(['search', 'status', 'payment', 'class', 'course'])))]);

        $statuses = Enrollment::statuses();
        $payments = EnrollmentRequest::PAYMENT_STATUSES;

        return response()->streamDownload(function () use ($rows, $statuses, $payments) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Indonesian text correctly
            fputcsv($out, ['ID', 'Siswa', 'Kelas', 'Kursus', 'Tanggal daftar', 'Status', 'Pembayaran']);
            foreach ($rows as $e) {
                fputcsv($out, [
                    $e->id,
                    $e->student?->name,
                    $e->className?->name,
                    $e->course?->name,
                    $e->enrollment_date?->format('Y-m-d'),
                    $statuses[$e->status] ?? $e->status,
                    $payments[$e->payment_status] ?? $e->payment_status,
                ]);
            }
            fclose($out);
        }, 'pendaftaran-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Create form.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Enrollment::class);

        $student = $request->filled('student') ? User::role('student')->find($request->integer('student')) : null;

        return view('admin.enrollments.create', [
            'enrollment' => new Enrollment([
                'class_id' => $request->integer('class') ?: null,
                'enrollment_date' => now(),
                'status' => Enrollment::STATUS_ACTIVE,
                'payment_status' => 'unpaid',
            ]),
            'student' => $student,
            'classOptions' => $this->classOptions(),
            'statuses' => Enrollment::statuses(),
            'paymentStatuses' => EnrollmentRequest::PAYMENT_STATUSES,
        ]);
    }

    /**
     * Store an enrollment (capacity-checked).
     */
    public function store(EnrollmentRequest $request): RedirectResponse
    {
        $class = ClassName::findOrFail($request->integer('class_id'));
        $student = User::findOrFail($request->integer('student_id'));

        $enrollment = $this->roster->enroll($class, $student, $request->safe()->only(['enrollment_date', 'status', 'payment_status', 'notes']));
        AdminAudit::log('enrollment.created', $enrollment);

        return redirect()->route('admin.enrollments.show', $enrollment)
            ->with('success', "{$student->name} terdaftar di kelas {$class->name}.");
    }

    /**
     * Enrollment detail.
     */
    public function show(Enrollment $enrollment): View
    {
        $this->authorize('view', $enrollment);

        $enrollment->load([
            'student:id,name,email,profile_photo_path', 'student.parents:id,name',
            'className.course:id,name', 'className.teachers:id,name', 'course:id,name',
            'schedules' => fn ($q) => $q->orderBy('day_of_week')->orderBy('start_time')->with('className:id,name'),
            'transactions' => fn ($q) => $q->latest('transaction_date'),
        ]);

        return view('admin.enrollments.show', [
            'enrollment' => $enrollment,
            'paymentStatuses' => EnrollmentRequest::PAYMENT_STATUSES,
        ]);
    }

    /**
     * Edit form (the student is fixed; the class may change).
     */
    public function edit(Enrollment $enrollment): View
    {
        $this->authorize('update', $enrollment);

        $enrollment->load(['student:id,name,email', 'className:id,name']);

        return view('admin.enrollments.edit', [
            'enrollment' => $enrollment,
            'classOptions' => $this->classOptions($enrollment->class_id),
            'statuses' => Enrollment::statuses(),
            'paymentStatuses' => EnrollmentRequest::PAYMENT_STATUSES,
        ]);
    }

    /**
     * Update an enrollment; class moves and seat-taking statuses re-check capacity.
     */
    public function update(EnrollmentRequest $request, Enrollment $enrollment): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($enrollment, $data) {
            if ((int) $data['class_id'] !== (int) $enrollment->class_id) {
                $this->roster->moveToClass($enrollment, ClassName::findOrFail($data['class_id']));
            }
            if ($data['status'] !== $enrollment->status) {
                $this->roster->changeStatus($enrollment, $data['status']);
            }
            $enrollment->update([
                'enrollment_date' => $data['enrollment_date'],
                'payment_status' => $data['payment_status'],
                'notes' => $data['notes'] ?? null,
            ]);
        });

        AdminAudit::log('enrollment.updated', $enrollment, ['fields' => array_keys($data)]);

        return redirect()->route('admin.enrollments.show', $enrollment)->with('success', 'Perubahan pendaftaran disimpan.');
    }

    /**
     * Delete an enrollment record that has no payments or attendance; anything
     * with history must change status instead (Keluar/Dibatalkan).
     */
    public function destroy(Enrollment $enrollment): RedirectResponse
    {
        $this->authorize('delete', $enrollment);

        $hasHistory = $enrollment->transactions()->exists()
            || ($enrollment->class_id && \App\Models\Attendance::where('class_id', $enrollment->class_id)->where('student_id', $enrollment->student_id)->exists());

        if ($hasHistory) {
            return back()->with('error', 'Pendaftaran ini sudah punya riwayat pembayaran atau absensi. Ubah statusnya menjadi Keluar atau Dibatalkan agar riwayat tetap tersimpan.');
        }

        DB::transaction(function () use ($enrollment) {
            $enrollment->schedules()->delete();
            $enrollment->delete();
        });
        AdminAudit::log('enrollment.deleted', $enrollment);

        return redirect()->route('admin.enrollments.index')->with('success', 'Pendaftaran dihapus.');
    }

    /**
     * Quick status action: approve a pending registration, suspend or re-activate.
     */
    public function toggleStatus(Enrollment $enrollment): RedirectResponse
    {
        $this->authorize('update', $enrollment);

        $next = $enrollment->status === Enrollment::STATUS_ACTIVE ? Enrollment::STATUS_SUSPENDED : Enrollment::STATUS_ACTIVE;
        $this->roster->changeStatus($enrollment, $next);
        AdminAudit::log('enrollment.status_changed', $enrollment, ['status' => $next]);

        return back()->with('success', $next === Enrollment::STATUS_ACTIVE
            ? 'Pendaftaran diaktifkan.'
            : 'Pendaftaran ditangguhkan; jadwal siswa ini dinonaktifkan sementara.');
    }

    /**
     * Base query with the list filters and sorting.
     */
    private function filtered(Request $request)
    {
        $sorted = in_array($request->query('sort'), self::SORTS, true);
        $sort = $sorted ? $request->query('sort') : 'enrollment_date';
        $direction = $sorted ? ($request->query('direction') === 'desc' ? 'desc' : 'asc') : 'desc';

        return Enrollment::query()
            ->when(array_key_exists((string) $request->query('status'), Enrollment::statuses()), fn ($q) => $q->where('status', $request->query('status')))
            ->when(array_key_exists((string) $request->query('payment'), EnrollmentRequest::PAYMENT_STATUSES), fn ($q) => $q->where('payment_status', $request->query('payment')))
            ->when($request->filled('class'), fn ($q) => $q->where('class_id', (int) $request->query('class')))
            ->when($request->filled('course'), fn ($q) => $q->where(fn ($w) => $w->where('course_id', (int) $request->query('course'))
                ->orWhereHas('className', fn ($c) => $c->where('course_id', (int) $request->query('course')))))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], trim((string) $request->query('search'))).'%';
                $q->whereHas('student', fn ($s) => $s->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term)));
            })
            ->orderBy($sort, $direction)
            ->orderByDesc('id');
    }

    /**
     * Open classes for the select, with remaining seats ("Sisa 3 dari 5").
     *
     * @return array<int, array{value: int, label: string, disabled: bool}>
     */
    private function classOptions(?int $keepClassId = null): array
    {
        return ClassName::query()
            ->where(fn ($q) => $q->whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE])
                ->when($keepClassId, fn ($w) => $w->orWhere('id', $keepClassId)))
            ->with('course:id,name')
            ->withSeatsTaken()
            ->orderBy('name')
            ->get()
            ->map(function (ClassName $class) use ($keepClassId) {
                $left = $class->availableSeats();

                return [
                    'value' => $class->id,
                    'label' => sprintf('%s · %s · sisa %d dari %d kursi', $class->name, $class->course?->name ?? 'Tanpa kursus', $left, $class->max_students),
                    'disabled' => $left === 0 && $class->id !== $keepClassId,
                ];
            })
            ->all();
    }
}
