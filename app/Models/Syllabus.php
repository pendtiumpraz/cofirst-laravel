<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Syllabus extends Model
{
    use HasFactory;

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
        'learning_objectives' => 'json',
        'activities' => 'json',
        'duration_minutes' => 'integer',
        'status' => 'string',
    ];

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class)->orderBy('order');
    }

    public function activeMaterials()
    {
        return $this->hasMany(Material::class)->where('status', 'active')->orderBy('order');
    }

    public function classProgress()
    {
        return $this->hasMany(ClassProgress::class);
    }

    // Scope for active syllabuses
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Get materials for specific meeting range
    public function getMaterialsForMeeting($meetingNumber)
    {
        return $this->materials()
                   ->where('meeting_start', '<=', $meetingNumber)
                   ->where('meeting_end', '>=', $meetingNumber)
                   ->where('status', 'active')
                   ->get();
    }
}