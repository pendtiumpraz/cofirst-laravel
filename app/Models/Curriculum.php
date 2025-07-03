<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'type' => 'string',
        'status' => 'string',
        'duration_weeks' => 'integer',
        'objectives' => 'json'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function syllabuses()
    {
        return $this->hasMany(Syllabus::class)->orderBy('meeting_number');
    }

    public function activeSyllabuses()
    {
        return $this->hasMany(Syllabus::class)->where('status', 'active')->orderBy('meeting_number');
    }

    public function materials()
    {
        return $this->hasManyThrough(Material::class, Syllabus::class);
    }

    public function activeMaterials()
    {
        return $this->hasManyThrough(Material::class, Syllabus::class)
                   ->where('materials.status', 'active')
                   ->orderBy('syllabuses.meeting_number')
                   ->orderBy('materials.order');
    }

    // Scope for active curriculums
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Get curriculum types
    public static function getTypes()
    {
        return [
            'fast-track' => 'Fast Track',
            'regular' => 'Regular',
            'expert' => 'Expert',
            'beginner' => 'Beginner',
        ];
    }
}