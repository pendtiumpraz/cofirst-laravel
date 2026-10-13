<?php

namespace App\Models;

use App\Models\Casts\TimeOfDay;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A teacher's per-meeting report (berita acara) about one student, with Bloom's
 * taxonomy notes.
 *
 * schedule_id, curriculum_id, syllabus_id and material_id are nullable and SET NULL
 * on delete, so editing the curriculum never erases reports. start_time/end_time
 * are TIME columns (Carbon on read, "H:i" in JSON).
 *
 * Security note for controllers: set teacher_id from the authenticated user and
 * validate class/student ownership; do not pass $request->all().
 */
class ClassReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'student_id',
        'schedule_id',
        'report_date',
        'start_time',
        'end_time',
        'meeting_number',
        'curriculum_id',
        'syllabus_id',
        'material_id',
        'learning_concept',
        'remember_understanding',
        'understand_comprehension',
        'apply_application',
        'analyze_analysis',
        'evaluate_evaluation',
        'create_creation',
        'notes_recommendations',
        'follow_up_notes',
        'learning_media_link',
    ];

    protected $casts = [
        'report_date' => 'date',
        'start_time' => TimeOfDay::class,
        'end_time' => TimeOfDay::class,
        'meeting_number' => 'integer',
    ];

    /** The teacher who wrote the report. */
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

    /** The student. */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** The weekly schedule slot (nullable). */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    /** The curriculum (nullable). */
    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class)->withTrashed();
    }

    /** The syllabus / meeting (nullable). */
    public function syllabus(): BelongsTo
    {
        return $this->belongsTo(Syllabus::class)->withTrashed();
    }

    /** The material used (nullable). */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class)->withTrashed();
    }

    /** Reports about one student. */
    public function scopeForStudent(Builder $query, int|User $student): Builder
    {
        return $query->where('class_reports.student_id', $student instanceof User ? $student->id : $student);
    }

    /** Reports written by one teacher. */
    public function scopeByTeacher(Builder $query, int|User $teacher): Builder
    {
        return $query->where('class_reports.teacher_id', $teacher instanceof User ? $teacher->id : $teacher);
    }
}
