<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * History of a teacher's assignment to a class (assigned_at .. unassigned_at).
 *
 * The class_teacher pivot is the source of truth for current assignments;
 * ClassName::syncTeachers() keeps these rows in step with it. Schedules point at
 * an assignment through schedules.teacher_assignment_id.
 */
class TeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'assigned_at',
        'unassigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
    ];

    /** The teacher. */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /** The class (legacy name). */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** The class. */
    public function className(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** Schedules served by this assignment. */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'teacher_assignment_id');
    }

    /** Assignments that are still current (not unassigned). */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereNull('teacher_assignments.unassigned_at');
    }

    /** Whether the assignment is still current. */
    public function isCurrent(): bool
    {
        return $this->unassigned_at === null;
    }
}
