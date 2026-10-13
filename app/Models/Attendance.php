<?php

namespace App\Models;

use App\Models\Casts\TimeOfDay;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attendance of one student at one class meeting (table `attendance`).
 *
 * Unique per (class_id, student_id, attendance_date). `status` is one of
 * present, late, absent, excused; "attended" means present or late.
 *
 * @property int $id
 * @property int $class_id
 * @property int $student_id
 * @property int|null $schedule_id
 * @property int|null $teacher_id
 * @property int|null $meeting_number
 * @property \Illuminate\Support\Carbon $attendance_date
 * @property string $status
 */
class Attendance extends Model
{
    use HasFactory;

    public const STATUS_PRESENT = 'present';

    public const STATUS_LATE = 'late';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_EXCUSED = 'excused';

    /** Statuses that count as "attended" for attendance rates and points. */
    public const ATTENDED_STATUSES = [self::STATUS_PRESENT, self::STATUS_LATE];

    protected $table = 'attendance';

    protected $fillable = [
        'class_id',
        'student_id',
        'schedule_id',
        'teacher_id',
        'meeting_number',
        'attendance_date',
        'status',
        'check_in_time',
        'minutes_late',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => TimeOfDay::class,
        'meeting_number' => 'integer',
        'minutes_late' => 'integer',
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
            self::STATUS_PRESENT => 'Hadir',
            self::STATUS_LATE => 'Terlambat',
            self::STATUS_ABSENT => 'Tidak hadir',
            self::STATUS_EXCUSED => 'Izin',
        ];
    }

    /**
     * Attendance rate in percent for a set of attendance rows.
     *
     * Args:
     *     rows (iterable<Attendance>): Rows for one student or class.
     *
     * Returns:
     *     float: (present + late) / total * 100, rounded to 1 decimal; 0 when empty.
     */
    public static function rateFor(iterable $rows): float
    {
        $total = 0;
        $attended = 0;

        foreach ($rows as $row) {
            $total++;
            $attended += in_array($row->status, self::ATTENDED_STATUSES, true) ? 1 : 0;
        }

        return $total === 0 ? 0.0 : round($attended / $total * 100, 1);
    }

    /** The class this attendance belongs to. */
    public function className(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** The student. */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** The teacher who taught/recorded the meeting. */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /** The weekly schedule slot the meeting came from (optional). */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    /** Only rows that count as attended (present or late). */
    public function scopeAttended(Builder $query): Builder
    {
        return $query->whereIn('status', self::ATTENDED_STATUSES);
    }

    /** Rows for one student. */
    public function scopeForStudent(Builder $query, int|User $student): Builder
    {
        return $query->where('student_id', $student instanceof User ? $student->id : $student);
    }

    /** Indonesian label of the status. */
    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    /** Whether the student attended (present or late). */
    public function isAttended(): bool
    {
        return in_array($this->status, self::ATTENDED_STATUSES, true);
    }
}
