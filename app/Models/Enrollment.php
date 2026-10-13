<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A student's enrollment in a class (and/or a course before a class is assigned).
 *
 * Status lifecycle: pending (registered, awaiting payment/approval) -> active ->
 * completed; or suspended / dropped / cancelled. Pending and active enrollments
 * occupy a seat (see ClassName::seatsTaken()). `is_active` is kept in sync with
 * the status automatically.
 *
 * @property int $id
 * @property int|null $class_id
 * @property int|null $course_id
 * @property int $student_id
 * @property string $status
 * @property string $payment_status unpaid|pending|paid|refunded
 */
class Enrollment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_DROPPED = 'dropped';

    public const STATUS_CANCELLED = 'cancelled';

    /** Statuses that occupy a seat in the class. */
    public const SEAT_STATUSES = [self::STATUS_PENDING, self::STATUS_ACTIVE];

    protected $fillable = [
        'student_id',
        'course_id',
        'class_id',
        'enrollment_date',
        'status',
        'payment_status',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Keep the legacy is_active flag consistent with the status.
     */
    protected static function booted(): void
    {
        static::saving(function (Enrollment $enrollment) {
            if ($enrollment->isDirty('status') && $enrollment->status !== null) {
                $enrollment->is_active = in_array($enrollment->status, self::SEAT_STATUSES, true);
            }
        });
    }

    /**
     * Indonesian labels for every status.
     *
     * Returns:
     *     array<string, string>: status => label.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_SUSPENDED => 'Ditangguhkan',
            self::STATUS_INACTIVE => 'Tidak aktif',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_DROPPED => 'Keluar',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    /** Get the student for this enrollment. */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** Get the class for this enrollment. */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** Get the class for this enrollment (preferred name). */
    public function className(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** Get the course for this enrollment. */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /** The student's weekly schedule rows for this enrollment. */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'enrollment_id');
    }

    /** Invoices/payments linked to this enrollment. */
    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'enrollment_id');
    }

    /** Scope to get only active enrollments. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('enrollments.status', self::STATUS_ACTIVE);
    }

    /** Scope to pending enrollments. */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('enrollments.status', self::STATUS_PENDING);
    }

    /** Scope to completed enrollments. */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('enrollments.status', self::STATUS_COMPLETED);
    }

    /** Scope to enrollments that occupy a seat (pending or active). */
    public function scopeOccupyingSeat(Builder $query): Builder
    {
        return $query->whereIn('enrollments.status', self::SEAT_STATUSES);
    }

    /** Scope to the enrollments of one student. */
    public function scopeForStudent(Builder $query, int|User $student): Builder
    {
        return $query->where('enrollments.student_id', $student instanceof User ? $student->id : $student);
    }

    /** Whether the enrollment is active. */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /** Indonesian label of the status. */
    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? (string) $this->status;
    }
}
