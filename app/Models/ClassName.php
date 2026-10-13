<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * A class (rombel) of a course: dates, capacity, type, delivery method and status.
 *
 * Teachers are assigned through the `class_teacher` pivot (teachers()); all
 * teachers of a class are equal. `teacher` is a read-only HasOneThrough that
 * returns the first assigned teacher (eager-loadable: with('teacher')).
 * Use syncTeachers() to change assignments: it keeps class_teacher,
 * teacher_assignments and the schedules that reference them consistent.
 *
 * @property int $id
 * @property int $course_id
 * @property int|null $curriculum_id
 * @property string $name
 * @property string $status planned|active|completed|cancelled
 * @property int $max_students
 */
class ClassName extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PLANNED = 'planned';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'course_id',
        'name',
        'description',
        'photo_path',
        'start_date',
        'end_date',
        'max_students',
        'status',
        'type',
        'delivery_method',
        'curriculum_id',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'max_students' => 'integer',
    ];

    /**
     * Indonesian labels for the class types.
     *
     * Returns:
     *     array<string, string>: type => label.
     */
    public static function types(): array
    {
        return [
            'private_home_call' => 'Privat (kunjungan rumah)',
            'private_office_1on1' => 'Privat 1-on-1 (di kantor)',
            'private_online_1on1' => 'Privat 1-on-1 (online)',
            'public_school_extracurricular' => 'Ekstrakurikuler sekolah',
            'offline_seminar' => 'Seminar offline',
            'online_webinar' => 'Webinar online',
            'group_class_3_5_kids' => 'Kelas grup (3-5 anak)',
            'free_webinar' => 'Webinar gratis',
            'free_trial_30min' => 'Trial gratis 30 menit',
        ];
    }

    /** The course this class teaches. */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** The curriculum followed by this class (must belong to the same course). */
    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    /** All teachers assigned to this class (class_teacher pivot). */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_teacher', 'class_id', 'teacher_id')
            ->using(ClassTeacher::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * The first assigned teacher, as a real relation (works with with('teacher')).
     *
     * Reason: views and controllers still use $class->teacher from the time
     * class_names had a teacher_id column.
     */
    public function teacher(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, ClassTeacher::class, 'class_id', 'id', 'id', 'teacher_id')
            ->orderBy('class_teacher.id');
    }

    /**
     * The first assigned teacher (kept for backward compatibility).
     *
     * Returns:
     *     User|null: The teacher model, not a relation.
     */
    public function primaryTeacher(): ?User
    {
        return $this->teacher;
    }

    /** Teacher assignment history rows (teacher_assignments). */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'class_id');
    }

    /** Current (not unassigned) teacher assignments. */
    public function activeTeacherAssignments(): HasMany
    {
        return $this->teacherAssignments()->whereNull('unassigned_at');
    }

    /** Weekly schedule rows of this class (one per enrollment in the current schema). */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    /** All enrollments of this class. */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    /** Enrollments with status active. */
    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', Enrollment::STATUS_ACTIVE);
    }

    /** Students enrolled in this class (any enrollment status; see pivot status). */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'class_id', 'student_id')
            ->withPivot(['id', 'status', 'enrollment_date', 'payment_status'])
            ->withTimestamps();
    }

    /** Class photos uploaded for this class. */
    public function classPhotos(): HasMany
    {
        return $this->hasMany(ClassPhoto::class, 'class_id');
    }

    /** Per-meeting teacher reports (berita acara). */
    public function classReports(): HasMany
    {
        return $this->hasMany(ClassReport::class, 'class_id');
    }

    /** Periodic progress reports. */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'class_id');
    }

    /** Attendance rows of this class. */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    /** Student progress per meeting. */
    public function progress(): HasMany
    {
        return $this->hasMany(ClassProgress::class, 'class_id');
    }

    /** Student projects made in this class. */
    public function projectGalleries(): HasMany
    {
        return $this->hasMany(ProjectGallery::class, 'class_id');
    }

    /** Certificates issued for this class. */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'class_id');
    }

    /** Teacher handover requests for this class. */
    public function handovers(): HasMany
    {
        return $this->hasMany(TeacherHandover::class, 'class_id');
    }

    /**
     * Replace the class's teachers, keeping all assignment data consistent.
     *
     * Updates the class_teacher pivot (source of truth), closes removed rows in
     * teacher_assignments (unassigned_at), opens rows for new teachers, and moves
     * active schedules that pointed at a removed teacher to a remaining teacher.
     *
     * Args:
     *     teacherIds (array<int, int|string>): The complete list of teacher user ids.
     */
    public function syncTeachers(array $teacherIds): void
    {
        $ids = array_values(array_unique(array_map('intval', array_filter($teacherIds))));

        DB::transaction(function () use ($ids) {
            $this->teachers()->sync(array_fill_keys($ids, ['role' => 'teacher']));

            $this->activeTeacherAssignments()->whereNotIn('teacher_id', $ids ?: [0])
                ->update(['unassigned_at' => now()]);

            foreach ($ids as $id) {
                if (! $this->activeTeacherAssignments()->where('teacher_id', $id)->exists()) {
                    $this->teacherAssignments()->create(['teacher_id' => $id, 'assigned_at' => now()]);
                }
            }

            // Reason: schedules reference teacher_assignments; re-point the ones whose
            // assignment was closed so the new teacher sees them and the old one does not.
            $replacement = $this->activeTeacherAssignments()->orderBy('id')->value('id');
            $this->schedules()
                ->where(fn (Builder $q) => $q->whereNull('teacher_assignment_id')
                    ->orWhereNotIn('teacher_assignment_id', $this->activeTeacherAssignments()->select('id')))
                ->update(['teacher_assignment_id' => $replacement]);
        });

        $this->unsetRelation('teachers')->unsetRelation('teacher');
    }

    /**
     * Number of seats taken (enrollments with status pending or active).
     *
     * Uses the `seats_taken` attribute when the query used withSeatsTaken().
     */
    public function seatsTaken(): int
    {
        if (array_key_exists('seats_taken', $this->attributes)) {
            return (int) $this->attributes['seats_taken'];
        }

        return $this->enrollments()->whereIn('status', Enrollment::SEAT_STATUSES)->count();
    }

    /** Seats still available (never negative). */
    public function availableSeats(): int
    {
        return max(0, (int) $this->max_students - $this->seatsTaken());
    }

    /** Whether the class has no seat left. */
    public function isFull(): bool
    {
        return $this->availableSeats() === 0;
    }

    /**
     * Whether `$seats` more students can enroll.
     *
     * Args:
     *     seats (int): Number of seats requested.
     */
    public function hasCapacity(int $seats = 1): bool
    {
        return $this->availableSeats() >= $seats;
    }

    /** Add a `seats_taken` count (pending + active enrollments) to the query. */
    public function scopeWithSeatsTaken(Builder $query): Builder
    {
        return $query->withCount([
            'enrollments as seats_taken' => fn (Builder $q) => $q->whereIn('status', Enrollment::SEAT_STATUSES),
        ]);
    }

    /** Only classes that still have at least one free seat. */
    public function scopeWithAvailableSeats(Builder $query): Builder
    {
        return $query->whereRaw(
            '(select count(*) from enrollments where enrollments.class_id = class_names.id and enrollments.status in (?, ?)) < class_names.max_students',
            Enrollment::SEAT_STATUSES
        );
    }

    /** Classes taught by the given teacher (class_teacher pivot). */
    public function scopeTaughtBy(Builder $query, int|User $teacher): Builder
    {
        $id = $teacher instanceof User ? $teacher->id : $teacher;

        return $query->whereHas('teachers', fn (Builder $q) => $q->where('users.id', $id));
    }

    /** Scope untuk kelas yang aktif dan belum selesai. */
    public function scopeActiveAndOngoing(Builder $query): Builder
    {
        return $query->where('class_names.is_active', true)
            ->whereIn('class_names.status', [self::STATUS_PLANNED, self::STATUS_ACTIVE]);
    }

    /** Scope untuk kelas yang sedang berlangsung. */
    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('class_names.status', self::STATUS_ACTIVE);
    }

    /** Scope untuk kelas yang direncanakan. */
    public function scopePlanned(Builder $query): Builder
    {
        return $query->where('class_names.status', self::STATUS_PLANNED);
    }

    /** Scope untuk kelas yang sudah selesai. */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('class_names.status', self::STATUS_COMPLETED);
    }

    /** Check apakah kelas masih bisa ditampilkan di calendar. */
    public function canShowInCalendar(): bool
    {
        return $this->is_active && in_array($this->status, [self::STATUS_PLANNED, self::STATUS_ACTIVE], true);
    }

    /** Check apakah kelas sudah selesai. */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /** Comma-separated names of all teachers (uses the loaded relation when present). */
    public function getTeacherNamesAttribute(): string
    {
        return $this->teachers->pluck('name')->implode(', ');
    }

    /** Indonesian label of the class type. */
    public function getTypeLabelAttribute(): string
    {
        return self::types()[$this->type] ?? (string) $this->type;
    }

    /** Get status badge color. */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'planned' => 'bg-blue-100 text-blue-800',
            'active' => 'bg-green-100 text-green-800',
            'completed' => 'bg-gray-100 text-gray-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /** Get status label in Indonesian. */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'planned' => 'Direncanakan',
            'active' => 'Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Tidak Diketahui'
        };
    }

    /** Get the URL to the class photo. */
    public function getPhotoUrlAttribute(): string
    {
        return $this->photo_path
            ? asset('storage/'.$this->photo_path)
            : asset('images/default-class-photo.png');
    }
}
