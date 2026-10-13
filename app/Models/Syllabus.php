<?php

namespace App\Models;

use App\Models\Casts\MultilineText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One meeting (pertemuan) of a curriculum, identified by meeting_number.
 *
 * `learning_objectives` and `activities` are newline-separated text (string);
 * use `learning_objectives_list` / `activities_list` for arrays.
 */
class Syllabus extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'syllabuses';

    protected $fillable = [
        'curriculum_id',
        'title',
        'description',
        'meeting_number',
        'learning_objectives',
        'activities',
        'duration_minutes',
        'status',
    ];

    protected $casts = [
        'meeting_number' => 'integer',
        'learning_objectives' => MultilineText::class,
        'activities' => MultilineText::class,
        'duration_minutes' => 'integer',
        'status' => 'string',
    ];

    /** The curriculum. */
    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    /** Materials ordered by `order`. */
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class)->orderBy('order');
    }

    /** Active materials ordered by `order`. */
    public function activeMaterials(): HasMany
    {
        return $this->hasMany(Material::class)->where('status', 'active')->orderBy('order');
    }

    /** Student progress rows for this meeting. */
    public function classProgress(): HasMany
    {
        return $this->hasMany(ClassProgress::class);
    }

    /** Meeting reports that covered this syllabus. */
    public function classReports(): HasMany
    {
        return $this->hasMany(ClassReport::class);
    }

    /** Scope for active syllabuses. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('syllabuses.status', 'active');
    }

    /** Learning objectives as an array of items. */
    public function getLearningObjectivesListAttribute(): array
    {
        return MultilineText::toList($this->learning_objectives);
    }

    /** Activities as an array of items. */
    public function getActivitiesListAttribute(): array
    {
        return MultilineText::toList($this->activities);
    }

    /** Get materials for a specific meeting number. */
    public function getMaterialsForMeeting($meetingNumber)
    {
        return $this->materials()
            ->where('meeting_start', '<=', $meetingNumber)
            ->where('meeting_end', '>=', $meetingNumber)
            ->where('status', 'active')
            ->get();
    }
}
