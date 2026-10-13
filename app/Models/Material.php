<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A learning material of a syllabus (document, video, exercise, quiz, project).
 *
 * meeting_start..meeting_end is the range of meetings the material is available
 * for; it defaults to the syllabus's meeting number.
 */
class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'syllabus_id',
        'title',
        'description',
        'content',
        'meeting_start',
        'meeting_end',
        'type',
        'file_path',
        'external_url',
        'status',
        'order',
    ];

    protected $casts = [
        'meeting_start' => 'integer',
        'meeting_end' => 'integer',
        'order' => 'integer',
        'type' => 'string',
        'status' => 'string',
    ];

    /** The syllabus (meeting) this material belongs to. */
    public function syllabus(): BelongsTo
    {
        return $this->belongsTo(Syllabus::class);
    }

    /** Access log rows of students opening this material. */
    public function materialAccess(): HasMany
    {
        return $this->hasMany(MaterialAccess::class);
    }

    /** Student progress rows referencing this material. */
    public function classProgress(): HasMany
    {
        return $this->hasMany(ClassProgress::class);
    }

    /** Meeting reports that used this material. */
    public function classReports(): HasMany
    {
        return $this->hasMany(ClassReport::class);
    }

    /** Scope for active materials. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('materials.status', 'active');
    }

    /** Material types with Indonesian labels. */
    public static function getTypes(): array
    {
        return [
            'document' => 'Dokumen',
            'video' => 'Video',
            'exercise' => 'Latihan',
            'quiz' => 'Kuis',
            'project' => 'Proyek',
        ];
    }

    /** Whether the material spans multiple meetings. */
    public function isMultiMeeting(): bool
    {
        return $this->meeting_start !== $this->meeting_end;
    }

    /** Meeting range as text, e.g. "Pertemuan 3" or "Pertemuan 3-4". */
    public function getMeetingRangeAttribute(): string
    {
        if ($this->meeting_start === $this->meeting_end) {
            return "Pertemuan {$this->meeting_start}";
        }

        return "Pertemuan {$this->meeting_start}-{$this->meeting_end}";
    }

    /** Whether the material is available for the given meeting number. */
    public function isAvailableForMeeting($meetingNumber): bool
    {
        return $meetingNumber >= $this->meeting_start && $meetingNumber <= $this->meeting_end;
    }
}
