<?php

namespace App\Models;

use App\Models\Casts\MultilineText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The curriculum of a course: an ordered list of syllabuses (one per meeting),
 * each with materials.
 *
 * `objectives` is newline-separated text (string); use `objectives_list` for an
 * array. `learning_objectives` is an alias of `objectives` (the admin form posts it).
 */
class Curriculum extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'curriculums';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'type',
        'status',
        'duration_weeks',
        'objectives',
        'learning_objectives',
    ];

    protected $casts = [
        'type' => 'string',
        'status' => 'string',
        'duration_weeks' => 'integer',
        'objectives' => MultilineText::class,
    ];

    /** The course. */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** Syllabuses (meetings) ordered by meeting number. */
    public function syllabuses(): HasMany
    {
        return $this->hasMany(Syllabus::class)->orderBy('meeting_number');
    }

    /** Active syllabuses ordered by meeting number. */
    public function activeSyllabuses(): HasMany
    {
        return $this->hasMany(Syllabus::class)->where('status', 'active')->orderBy('meeting_number');
    }

    /** All materials of all syllabuses. */
    public function materials(): HasManyThrough
    {
        return $this->hasManyThrough(Material::class, Syllabus::class);
    }

    /** Active materials ordered by meeting and order. */
    public function activeMaterials(): HasManyThrough
    {
        return $this->hasManyThrough(Material::class, Syllabus::class)
            ->where('materials.status', 'active')
            ->orderBy('syllabuses.meeting_number')
            ->orderBy('materials.order');
    }

    /** Classes that follow this curriculum. */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassName::class);
    }

    /** Scope for active curriculums. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('curriculums.status', 'active');
    }

    /** Objectives as an array of items. */
    public function getObjectivesListAttribute(): array
    {
        return MultilineText::toList($this->objectives);
    }

    /** Alias getter: learning_objectives => objectives. */
    public function getLearningObjectivesAttribute(): ?string
    {
        return $this->objectives;
    }

    /** Alias setter: learning_objectives => objectives. */
    public function setLearningObjectivesAttribute(mixed $value): void
    {
        $this->objectives = $value;
    }

    /** Get curriculum types. */
    public static function getTypes(): array
    {
        return [
            'fast-track' => 'Fast Track',
            'regular' => 'Regular',
            'expert' => 'Expert',
            'beginner' => 'Beginner',
        ];
    }
}
