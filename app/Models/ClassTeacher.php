<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot row of `class_teacher`: one teacher assigned to one class.
 *
 * This pivot is the source of truth for "who teaches which class". The
 * teacher_assignments table is kept in sync by ClassName::syncTeachers() as an
 * assignment history (and because schedules reference it).
 */
class ClassTeacher extends Pivot
{
    protected $table = 'class_teacher';

    public $incrementing = true;

    protected $fillable = ['class_id', 'teacher_id', 'role'];

    /** The class. */
    public function className(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** The teacher. */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
