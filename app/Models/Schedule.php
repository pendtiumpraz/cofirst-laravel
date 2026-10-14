<?php

namespace App\Models;

use App\Models\Casts\TimeOfDay;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A weekly time slot of a class (day_of_week 1=Senin .. 7=Minggu, start/end time,
 * room or "Online").
 *
 * In the current schema there is one row per enrollment (enrollment_id) so a
 * student's timetable is `Schedule::forStudent($id)`; teacher_assignment_id
 * points at the teacher assignment of a teacher of the same class.
 * start_time/end_time are TIME columns read as Carbon (today's date) and
 * serialised as "H:i".
 *
 * @property int $day_of_week
 * @property Carbon|null $start_time
 * @property Carbon|null $end_time
 */
class Schedule extends Model
{
    use HasFactory;

    /** Indonesian day names, ISO numbering (1 = Monday). */
    public const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    protected $fillable = [
        'class_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'is_active',
        'enrollment_id',
        'teacher_assignment_id',
    ];

    protected $casts = [
        'start_time' => TimeOfDay::class,
        'end_time' => TimeOfDay::class,
        'is_active' => 'boolean',
        'day_of_week' => 'integer',
    ];

    /** Get the class for this schedule. */
    public function className(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** Get the class for this schedule (legacy name used by some views). */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** Get the enrollment (student) for this schedule. */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    /** Get the teacher assignment for this schedule. */
    public function teacherAssignment(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignment::class, 'teacher_assignment_id');
    }

    /** Get the class reports for this schedule. */
    public function classReports(): HasMany
    {
        return $this->hasMany(ClassReport::class, 'schedule_id');
    }

    /** Attendance rows recorded for this slot. */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'schedule_id');
    }

    /** Scope to get only active schedules. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('schedules.is_active', true);
    }

    /**
     * Scope untuk schedule yang bisa ditampilkan di calendar
     * (hanya kelas yang aktif dan belum selesai).
     */
    public function scopeForCalendar(Builder $query): Builder
    {
        return $query->where('schedules.is_active', true)
            ->whereHas('className', function (Builder $q) {
                $q->where('is_active', true)
                    ->whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE]);
            });
    }

    /** Scope untuk schedule berdasarkan status kelas. */
    public function scopeByClassStatus(Builder $query, string $status): Builder
    {
        return $query->whereHas('className', function (Builder $q) use ($status) {
            $q->where('status', $status);
        });
    }

    /** Schedules of one student (through the enrollment). */
    public function scopeForStudent(Builder $query, int|User $student): Builder
    {
        $id = $student instanceof User ? $student->id : $student;

        return $query->whereHas('enrollment', fn (Builder $q) => $q->where('student_id', $id));
    }

    /**
     * Schedules of one teacher: the slot's assignment belongs to the teacher, or
     * the teacher is assigned to the class (class_teacher pivot).
     */
    public function scopeForTeacher(Builder $query, int|User $teacher): Builder
    {
        $id = $teacher instanceof User ? $teacher->id : $teacher;

        return $query->where(fn (Builder $q) => $q
            ->whereHas('teacherAssignment', fn (Builder $a) => $a->where('teacher_id', $id))
            ->orWhereHas('className.teachers', fn (Builder $t) => $t->where('users.id', $id)));
    }

    /** Schedules on a given ISO weekday (1 = Senin). */
    public function scopeOnDay(Builder $query, int $dayOfWeek): Builder
    {
        return $query->where('schedules.day_of_week', $dayOfWeek);
    }

    /** Check apakah schedule bisa ditampilkan di calendar. */
    public function canShowInCalendar(): bool
    {
        return $this->is_active &&
               $this->className &&
               $this->className->canShowInCalendar();
    }

    /** The teacher of this slot (via the teacher assignment). */
    public function getTeacherAttribute(): ?User
    {
        return $this->teacherAssignment?->teacher;
    }

    /** Get the day name. */
    public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? '';
    }

    /** Get formatted time range, e.g. "09:00 - 10:30". */
    public function getTimeRangeAttribute(): string
    {
        if (! $this->start_time || ! $this->end_time) {
            return '';
        }

        return $this->start_time->format('H:i').' - '.$this->end_time->format('H:i');
    }
}
