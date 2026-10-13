<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A request to hand a class (or one student of it) over from one teacher to
 * another, permanently or temporarily (table `teacher_handovers`).
 *
 * Workflow: pending -> approved -> completed, or pending -> rejected/cancelled.
 * `student_id` NULL means the whole class.
 *
 * @property int $id
 * @property int $class_id
 * @property int|null $from_teacher_id
 * @property int|null $to_teacher_id
 * @property int|null $student_id
 * @property string $type
 * @property string $status
 */
class TeacherHandover extends Model
{
    use HasFactory;

    public const TYPE_PERMANENT = 'permanent';

    public const TYPE_TEMPORARY = 'temporary';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'class_id',
        'from_teacher_id',
        'to_teacher_id',
        'student_id',
        'type',
        'effective_from',
        'effective_until',
        'reason',
        'handover_notes',
        'last_meeting_number',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'approved_at' => 'datetime',
        'last_meeting_number' => 'integer',
    ];

    /**
     * Indonesian labels for every status.
     *
     * Returns:
     *     array<string, string>: status => label.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    /**
     * Indonesian labels for the handover types.
     *
     * Returns:
     *     array<string, string>: type => label.
     */
    public static function types(): array
    {
        return [
            self::TYPE_PERMANENT => 'Permanen',
            self::TYPE_TEMPORARY => 'Sementara',
        ];
    }

    /** The class being handed over. */
    public function className(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** The teacher handing over. */
    public function fromTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_teacher_id');
    }

    /** The teacher taking over. */
    public function toTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_teacher_id');
    }

    /** The single student handed over (null = whole class). */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** Who requested the handover. */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /** Who approved it. */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Handovers involving a teacher on either side. */
    public function scopeInvolving(Builder $query, int|User $teacher): Builder
    {
        $id = $teacher instanceof User ? $teacher->id : $teacher;

        return $query->where(fn (Builder $q) => $q->where('from_teacher_id', $id)->orWhere('to_teacher_id', $id));
    }

    /** Pending handovers. */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /** Whether the whole class is handed over (not a single student). */
    public function isWholeClass(): bool
    {
        return $this->student_id === null;
    }

    /** Indonesian label of the status. */
    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }
}
