<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

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

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    public function materialAccess()
    {
        return $this->hasMany(MaterialAccess::class);
    }

    public function classProgress()
    {
        return $this->hasMany(ClassProgress::class);
    }

    // Scope for active materials
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Get material types
    public static function getTypes()
    {
        return [
            'document' => 'Document',
            'video' => 'Video',
            'exercise' => 'Exercise',
            'quiz' => 'Quiz',
            'project' => 'Project',
        ];
    }

    // Check if material spans multiple meetings
    public function isMultiMeeting()
    {
        return $this->meeting_start !== $this->meeting_end;
    }

    // Get meeting range as string
    public function getMeetingRangeAttribute()
    {
        if ($this->meeting_start === $this->meeting_end) {
            return "Meeting {$this->meeting_start}";
        }
        return "Meetings {$this->meeting_start}-{$this->meeting_end}";
    }

    // Check if material is available for specific meeting
    public function isAvailableForMeeting($meetingNumber)
    {
        return $meetingNumber >= $this->meeting_start && $meetingNumber <= $this->meeting_end;
    }
}
