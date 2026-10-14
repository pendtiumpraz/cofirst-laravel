<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreHandoverRequest;
use App\Models\TeacherHandover;
use App\Models\User;
use App\Services\Teacher\HandoverWorkflow;
use App\Services\Teacher\PrivacyAudit;
use App\Services\Teacher\TeachingSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Serah terima kelas: a teacher asks another teacher to substitute
 * (temporary) or take over a class (permanent); the receiving teacher accepts
 * or declines. Only the two teachers involved can see a request.
 */
class HandoverController extends Controller
{
    /** Tabs of the list => statuses shown. */
    private const TABS = [
        'masuk' => [TeacherHandover::STATUS_PENDING, TeacherHandover::STATUS_APPROVED],
        'dikirim' => [TeacherHandover::STATUS_PENDING, TeacherHandover::STATUS_APPROVED],
        'riwayat' => [TeacherHandover::STATUS_REJECTED, TeacherHandover::STATUS_COMPLETED, TeacherHandover::STATUS_CANCELLED],
    ];

    /**
     * Args:
     *     workflow (HandoverWorkflow): Status transitions and permissions.
     */
    public function __construct(private readonly HandoverWorkflow $workflow) {}

    /**
     * List handovers involving the teacher: incoming, sent and history.
     *
     * Args:
     *     request (Request): Query `tab` = masuk|dikirim|riwayat.
     *
     * Returns:
     *     View: teacher.handovers.index
     */
    public function index(Request $request): View
    {
        $teacher = $request->user();
        $requested = $request->query('tab');
        $tab = is_string($requested) && array_key_exists($requested, self::TABS) ? $requested : null;

        $counts = [];
        foreach (array_keys(self::TABS) as $key) {
            $counts[$key] = $this->tabQuery($key, $teacher)->count();
        }

        // Reason: open the tab that needs action first (incoming requests), else sent ones.
        $tab ??= $counts['masuk'] > 0 ? 'masuk' : ($counts['dikirim'] > 0 ? 'dikirim' : 'masuk');

        $handovers = $this->tabQuery($tab, $teacher)
            ->with(['className:id,name,course_id', 'className.course:id,name', 'fromTeacher:id,name,profile_photo_path', 'toTeacher:id,name,profile_photo_path'])
            ->orderByRaw('case when status = ? then 0 else 1 end', [TeacherHandover::STATUS_PENDING])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('teacher.handovers.index', [
            'handovers' => $handovers,
            'tab' => $tab,
            'counts' => $counts,
            'pendingIncoming' => $this->tabQuery('masuk', $teacher)->pending()->count(),
            'workflow' => $this->workflow,
            'teacher' => $teacher,
        ]);
    }

    /**
     * Form to request a handover.
     *
     * Args:
     *     request (Request): Optional query `class_id` to preselect.
     *
     * Returns:
     *     View: teacher.handovers.create
     */
    public function create(Request $request): View
    {
        return view('teacher.handovers.create', $this->formData($request->user()) + [
            'handover' => new TeacherHandover([
                'class_id' => is_scalar($request->query('class_id')) ? ((int) $request->query('class_id') ?: null) : null,
                'type' => TeacherHandover::TYPE_TEMPORARY,
                'effective_from' => TeachingSchedule::today()->toDateString(),
            ]),
        ]);
    }

    /**
     * Store a new handover request.
     *
     * Args:
     *     request (StoreHandoverRequest): Validated data.
     *
     * Returns:
     *     RedirectResponse: To the request detail.
     */
    public function store(StoreHandoverRequest $request): RedirectResponse
    {
        $handover = $this->workflow->request($request->user(), $request->validated());
        PrivacyAudit::log('teacher.handover.requested', $handover, ['class_id' => $handover->class_id, 'to_teacher_id' => $handover->to_teacher_id]);

        return redirect()->route('teacher.handovers.show', $handover)
            ->with('success', 'Permintaan serah terima terkirim. Kami akan menampilkan jawabannya di sini.');
    }

    /**
     * Show one handover.
     *
     * Args:
     *     request (Request): Current request.
     *     handover (TeacherHandover): Must involve the teacher (403 otherwise).
     *
     * Returns:
     *     View: teacher.handovers.show
     */
    public function show(Request $request, TeacherHandover $handover): View
    {
        $teacher = $request->user();
        abort_unless($this->workflow->canView($handover, $teacher), 403, 'Permintaan ini tidak melibatkan Anda.');

        $handover->load([
            'className' => fn ($q) => $q->withTrashed()->with('course:id,name')
                ->withCount(['enrollments as active_students_count' => fn ($e) => $e->where('status', 'active')]),
            'fromTeacher:id,name,profile_photo_path',
            'toTeacher:id,name,profile_photo_path',
            'approver:id,name',
        ]);

        return view('teacher.handovers.show', [
            'handover' => $handover,
            'teacher' => $teacher,
            'canRespond' => $this->workflow->canRespond($handover, $teacher),
            'canEdit' => $this->workflow->canEdit($handover, $teacher),
            'canComplete' => $this->workflow->canComplete($handover, $teacher),
            'overdue' => $this->workflow->isOverdue($handover),
        ]);
    }

    /**
     * Edit a pending request (requesting teacher only).
     *
     * Returns:
     *     View: teacher.handovers.edit
     */
    public function edit(Request $request, TeacherHandover $handover): View
    {
        abort_unless($this->workflow->canEdit($handover, $request->user()), 403, 'Permintaan ini tidak dapat diubah lagi.');

        return view('teacher.handovers.edit', $this->formData($request->user()) + ['handover' => $handover]);
    }

    /**
     * Update a pending request.
     *
     * Returns:
     *     RedirectResponse: To the request detail.
     */
    public function update(StoreHandoverRequest $request, TeacherHandover $handover): RedirectResponse
    {
        abort_unless($this->workflow->canEdit($handover, $request->user()), 403, 'Permintaan ini tidak dapat diubah lagi.');

        $this->workflow->revise($handover, $request->validated());

        return redirect()->route('teacher.handovers.show', $handover)->with('success', 'Permintaan serah terima diperbarui.');
    }

    /**
     * Cancel a pending request. Requests are never deleted, only cancelled (history).
     *
     * Returns:
     *     RedirectResponse: To the list.
     */
    public function destroy(Request $request, TeacherHandover $handover): RedirectResponse
    {
        abort_unless($this->workflow->canEdit($handover, $request->user()), 403, 'Permintaan ini tidak dapat dibatalkan lagi.');

        $this->workflow->cancel($handover);
        PrivacyAudit::log('teacher.handover.cancelled', $handover, ['class_id' => $handover->class_id]);

        return redirect()->route('teacher.handovers.index', ['tab' => 'riwayat'])->with('success', 'Permintaan serah terima dibatalkan.');
    }

    /**
     * Accept a request (receiving teacher): the class teachers change immediately.
     *
     * Returns:
     *     RedirectResponse: To the request detail.
     */
    public function approve(Request $request, TeacherHandover $handover): RedirectResponse
    {
        abort_unless($this->workflow->canRespond($handover, $request->user()), 403, 'Hanya pengajar penerima yang dapat menjawab permintaan ini.');

        $this->workflow->approve($handover, $request->user());
        PrivacyAudit::log('teacher.handover.approved', $handover, ['class_id' => $handover->class_id, 'type' => $handover->type]);

        $message = $handover->type === TeacherHandover::TYPE_PERMANENT
            ? 'Serah terima diterima. Kelas kini tercatat atas nama Anda.'
            : 'Serah terima diterima. Kelas tampil di jadwal Anda sampai periode pengganti selesai.';

        return redirect()->route('teacher.handovers.show', $handover)->with('success', $message);
    }

    /**
     * Decline a request (receiving teacher).
     *
     * Returns:
     *     RedirectResponse: To the request detail.
     */
    public function reject(Request $request, TeacherHandover $handover): RedirectResponse
    {
        abort_unless($this->workflow->canRespond($handover, $request->user()), 403, 'Hanya pengajar penerima yang dapat menjawab permintaan ini.');

        $this->workflow->reject($handover, $request->user());
        PrivacyAudit::log('teacher.handover.rejected', $handover, ['class_id' => $handover->class_id]);

        return redirect()->route('teacher.handovers.show', $handover)->with('success', 'Permintaan serah terima ditolak.');
    }

    /**
     * Close an approved temporary handover: the substitute leaves the class.
     *
     * Returns:
     *     RedirectResponse: To the request detail.
     */
    public function complete(Request $request, TeacherHandover $handover): RedirectResponse
    {
        abort_unless($this->workflow->canComplete($handover, $request->user()), 403, 'Serah terima ini tidak dapat diselesaikan.');

        $this->workflow->complete($handover);
        PrivacyAudit::log('teacher.handover.completed', $handover, ['class_id' => $handover->class_id]);

        return redirect()->route('teacher.handovers.show', $handover)->with('success', 'Serah terima selesai. Pengajar pengganti sudah dilepas dari kelas.');
    }

    /**
     * Handovers of one tab for the teacher.
     *
     * Args:
     *     tab (string): masuk|dikirim|riwayat.
     *     teacher (User): The teacher.
     */
    private function tabQuery(string $tab, User $teacher): Builder
    {
        $query = TeacherHandover::query()->whereIn('status', self::TABS[$tab]);

        return match ($tab) {
            'masuk' => $query->where('to_teacher_id', $teacher->id),
            'dikirim' => $query->where(fn (Builder $q) => $q->where('from_teacher_id', $teacher->id)->orWhere('requested_by', $teacher->id)),
            default => $query->involving($teacher),
        };
    }

    /**
     * Options of the request form: the teacher's classes and the other active teachers (names only).
     *
     * Args:
     *     teacher (User): The requesting teacher.
     *
     * Returns:
     *     array{classes: \Illuminate\Support\Collection, teachers: \Illuminate\Support\Collection, classTeachers: array<int, array<int, int>>, types: array<string, string>}
     */
    private function formData(User $teacher): array
    {
        $classes = $teacher->taughtClasses()
            ->with(['course:id,name', 'teachers:users.id'])
            ->orderBy('class_names.name')
            ->get();

        $teachers = User::role('teacher')
            ->where('is_active', true)
            ->whereKeyNot($teacher->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return [
            'classes' => $classes,
            'teachers' => $teachers,
            // Reason: lets the form disable teachers who already teach the chosen class.
            'classTeachers' => $classes->mapWithKeys(fn ($class) => [$class->id => $class->teachers->pluck('id')->map(fn ($id) => (int) $id)->all()])->all(),
            'types' => TeacherHandover::types(),
        ];
    }
}
