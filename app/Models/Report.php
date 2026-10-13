<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A periodic progress report written by a teacher about one student in one class
 * (table `reports`). Per-meeting notes live in ClassReport instead.
 *
 * Columns: class_id, student_id, teacher_id (nullable: kept when the teacher is
 * deleted), title, content, recommendations, progress_percentage (0-100),
 * score (optional assessment 0-100), report_date, type (weekly|monthly|final|incident).
 *
 * `report_type` is an alias of `type` (the teacher form posts report_type).
 *
 * @property int $id
 * @property string $type
 * @property string|null $score
 */
class Report extends Model
{
    use HasFactory;

    public const TYPE_WEEKLY = 'weekly';

    public const TYPE_MONTHLY = 'monthly';

    public const TYPE_FINAL = 'final';

    public const TYPE_INCIDENT = 'incident';

    protected $fillable = [
        'student_id',
        'teacher_id',
        'class_id',
        'title',
        'content',
        'recommendations',
        'progress_percentage',
        'score',
        'report_date',
        'type',
        'report_type',
    ];

    protected $casts = [
        'report_date' => 'date',
        'progress_percentage' => 'decimal:2',
        'score' => 'decimal:2',
    ];

    /**
     * Indonesian labels for the report types.
     *
     * Returns:
     *     array<string, string>: type => label.
     */
    public static function types(): array
    {
        return [
            self::TYPE_WEEKLY => 'Mingguan',
            self::TYPE_MONTHLY => 'Bulanan',
            self::TYPE_FINAL => 'Akhir',
            self::TYPE_INCIDENT => 'Kejadian khusus',
        ];
    }

    /** Get the student for this report. */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** Get the teacher who wrote this report. */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /** Get the class for this report. */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** Get the class for this report (preferred name). */
    public function className(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** Reports about one student. */
    public function scopeForStudent(Builder $query, int|User $student): Builder
    {
        return $query->where('reports.student_id', $student instanceof User ? $student->id : $student);
    }

    /** Alias getter: report_type => type. */
    public function getReportTypeAttribute(): ?string
    {
        return $this->attributes['type'] ?? null;
    }

    /** Alias setter: report_type => type. */
    public function setReportTypeAttribute(?string $value): void
    {
        $this->attributes['type'] = $value ?? self::TYPE_WEEKLY;
    }

    /** Indonesian label of the type. */
    public function getTypeLabelAttribute(): string
    {
        return self::types()[$this->type] ?? (string) $this->type;
    }
}
