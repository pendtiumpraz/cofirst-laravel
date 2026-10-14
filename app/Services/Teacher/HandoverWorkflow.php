<?php

namespace App\Services\Teacher;

use App\Models\ClassName;
use App\Models\TeacherHandover;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Workflow of teacher handovers (serah terima kelas).
 *
 *   pending --(receiving teacher accepts)--> approved (temporary) / completed (permanent)
 *   pending --(receiving teacher declines)--> rejected
 *   pending --(requesting teacher cancels)--> cancelled
 *   approved --(period ends / either teacher closes it)--> completed
 *
 * Accepting changes the class_teacher pivot through ClassName::syncTeachers(),
 * which also keeps teacher_assignments and schedules consistent:
 *   - temporary: the substitute joins the class until the handover is completed;
 *   - permanent: the substitute replaces the requesting teacher.
 */
class HandoverWorkflow
{
    /** Whether the user may see the handover (either teacher or the requester). */
    public function canView(TeacherHandover $handover, User $user): bool
    {
        return $this->isOneOf($user, [$handover->from_teacher_id, $handover->to_teacher_id, $handover->requested_by]);
    }

    /** Whether the user may accept or decline (the receiving teacher, while pending). */
    public function canRespond(TeacherHandover $handover, User $user): bool
    {
        return $handover->status === TeacherHandover::STATUS_PENDING && $this->isOneOf($user, [$handover->to_teacher_id]);
    }

    /** Whether the user may edit or cancel (the requesting teacher, while pending). */
    public function canEdit(TeacherHandover $handover, User $user): bool
    {
        return $handover->status === TeacherHandover::STATUS_PENDING
            && $this->isOneOf($user, [$handover->from_teacher_id, $handover->requested_by]);
    }

    /** Whether the user may close an approved temporary handover. */
    public function canComplete(TeacherHandover $handover, User $user): bool
    {
        return $handover->status === TeacherHandover::STATUS_APPROVED
            && $handover->type === TeacherHandover::TYPE_TEMPORARY
            && $this->isOneOf($user, [$handover->from_teacher_id, $handover->to_teacher_id]);
    }

    /**
     * Create a pending handover requested by the teacher.
     *
     * Args:
     *     teacher (User): Requesting teacher (from_teacher).
     *     data (array): Validated StoreHandoverRequest data.
     */
    public function request(User $teacher, array $data): TeacherHandover
    {
        return TeacherHandover::create($this->attributes($data) + [
            'from_teacher_id' => $teacher->id,
            'requested_by' => $teacher->id,
            'status' => TeacherHandover::STATUS_PENDING,
        ]);
    }

    /**
     * Update a pending handover.
     *
     * Args:
     *     handover (TeacherHandover): The handover (must still be pending).
     *     data (array): Validated StoreHandoverRequest data.
     */
    public function revise(TeacherHandover $handover, array $data): TeacherHandover
    {
        $this->transition($handover, [TeacherHandover::STATUS_PENDING], fn (TeacherHandover $h) => $h->update($this->attributes($data)));

        return $handover->refresh();
    }

    /**
     * The receiving teacher accepts: apply the teacher change to the class.
     *
     * Raises:
     *     ValidationException: When the handover is no longer pending, the class has
     *     ended, or the receiving teacher already teaches the class.
     */
    public function approve(TeacherHandover $handover, User $by): void
    {
        $this->transition($handover, [TeacherHandover::STATUS_PENDING], function (TeacherHandover $h) use ($by) {
            $class = $this->activeClass($h);
            $ids = $class->teachers()->pluck('users.id')->map(fn ($id) => (int) $id)->all();

            if (in_array((int) $h->to_teacher_id, $ids, true)) {
                throw ValidationException::withMessages(['status' => 'Anda sudah mengajar kelas ini, jadi serah terima tidak diperlukan. Silakan tolak permintaan ini.']);
            }

            $permanent = $h->type === TeacherHandover::TYPE_PERMANENT;
            $ids = $permanent ? array_values(array_diff($ids, [(int) $h->from_teacher_id])) : $ids;
            $ids[] = (int) $h->to_teacher_id;
            $class->syncTeachers($ids);

            $h->update([
                'status' => $permanent ? TeacherHandover::STATUS_COMPLETED : TeacherHandover::STATUS_APPROVED,
                'approved_by' => $by->id,
                'approved_at' => now(),
            ]);
        });
    }

    /** The receiving teacher declines a pending handover. */
    public function reject(TeacherHandover $handover, User $by): void
    {
        $this->transition($handover, [TeacherHandover::STATUS_PENDING], fn (TeacherHandover $h) => $h->update([
            'status' => TeacherHandover::STATUS_REJECTED,
            'approved_by' => $by->id,
            'approved_at' => now(),
        ]));
    }

    /** The requesting teacher withdraws a pending handover (kept for history, never deleted). */
    public function cancel(TeacherHandover $handover): void
    {
        $this->transition($handover, [TeacherHandover::STATUS_PENDING], fn (TeacherHandover $h) => $h->update([
            'status' => TeacherHandover::STATUS_CANCELLED,
        ]));
    }

    /**
     * Close an approved temporary handover: the substitute leaves the class.
     *
     * Reason: the substitute is only removed while another teacher remains, so a
     * class is never left without a teacher.
     */
    public function complete(TeacherHandover $handover): void
    {
        $this->transition($handover, [TeacherHandover::STATUS_APPROVED], function (TeacherHandover $h) {
            $class = ClassName::withTrashed()->find($h->class_id);

            if ($class && $h->type === TeacherHandover::TYPE_TEMPORARY) {
                $ids = $class->teachers()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
                $remaining = array_values(array_diff($ids, [(int) $h->to_teacher_id]));

                if ($remaining !== [] && count($remaining) < count($ids)) {
                    $class->syncTeachers($remaining);
                }
            }

            $h->update(['status' => TeacherHandover::STATUS_COMPLETED]);
        });
    }

    /**
     * Complete every approved temporary handover whose period has ended (scheduler).
     *
     * Returns:
     *     int: Number of handovers completed.
     */
    public function completeExpired(): int
    {
        $count = 0;
        $today = TeachingSchedule::today()->toDateString();

        TeacherHandover::query()
            ->where('status', TeacherHandover::STATUS_APPROVED)
            ->where('type', TeacherHandover::TYPE_TEMPORARY)
            ->whereNotNull('effective_until')
            ->whereRaw('date(effective_until) < ?', [$today])
            ->orderBy('id')
            ->each(function (TeacherHandover $handover) use (&$count) {
                $this->complete($handover);
                $count++;
            });

        return $count;
    }

    /** Whether an approved temporary handover is past its end date. */
    public function isOverdue(TeacherHandover $handover): bool
    {
        return $handover->status === TeacherHandover::STATUS_APPROVED
            && $handover->effective_until !== null
            && $handover->effective_until->format('Y-m-d') < TeachingSchedule::today()->toDateString();
    }

    /**
     * Run a status change inside a transaction with the row locked.
     *
     * Raises:
     *     ValidationException: When the stored status is not one of `$from`.
     */
    private function transition(TeacherHandover $handover, array $from, callable $change): void
    {
        DB::transaction(function () use ($handover, $from, $change) {
            /** @var TeacherHandover $locked */
            $locked = TeacherHandover::query()->whereKey($handover->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, $from, true)) {
                throw ValidationException::withMessages(['status' => 'Status serah terima sudah berubah ('.mb_strtolower($locked->status_label).'). Muat ulang halaman.']);
            }

            $change($locked);
        });

        $handover->refresh();
    }

    /**
     * The handover's class, only when it can still be taught.
     *
     * Raises:
     *     ValidationException: When the class was archived, completed or cancelled.
     */
    private function activeClass(TeacherHandover $handover): ClassName
    {
        $class = ClassName::find($handover->class_id);

        if (! $class || ! $class->canShowInCalendar()) {
            throw ValidationException::withMessages(['status' => 'Kelas ini sudah selesai atau tidak aktif, jadi serah terima tidak dapat diterima.']);
        }

        return $class;
    }

    /**
     * Whether the user's id is one of the given (nullable) ids.
     *
     * Args:
     *     user (User): The user.
     *     ids (array<int, int|string|null>): Candidate ids from the handover row.
     */
    private function isOneOf(User $user, array $ids): bool
    {
        return in_array((int) $user->id, array_map('intval', array_filter($ids)), true);
    }

    /** Map validated request data to model attributes. */
    private function attributes(array $data): array
    {
        $temporary = $data['type'] === TeacherHandover::TYPE_TEMPORARY;

        return [
            'class_id' => (int) $data['class_id'],
            'to_teacher_id' => (int) $data['to_teacher_id'],
            'student_id' => null,
            'type' => $data['type'],
            'effective_from' => $data['effective_from'],
            'effective_until' => $temporary ? ($data['effective_until'] ?? null) : null,
            'reason' => trim($data['reason']),
            'handover_notes' => filled($data['handover_notes'] ?? null) ? trim($data['handover_notes']) : null,
            'last_meeting_number' => isset($data['last_meeting_number']) && $data['last_meeting_number'] !== '' ? (int) $data['last_meeting_number'] : null,
        ];
    }
}
