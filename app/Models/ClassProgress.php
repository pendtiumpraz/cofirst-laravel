<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassProgress extends Model
{
    use HasFactory;

    protected $table = 'class_progress';

    protected $fillable = [
        'student_id',
        'class_id',
        'syllabus_id',
        'material_id',
        'meeting_number',
        'status',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'meeting_number' => 'integer',
        'status' => 'string',
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    // Scope for completed progress
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Scope for in progress
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    // Scope for not started
    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not_started');
    }

    // Mark as completed
    public function markCompleted($notes = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $notes,
        ]);
    }

    // Mark as in progress
    public function markInProgress($notes = null)
    {
        $this->update([
            'status' => 'in_progress',
            'notes' => $notes,
        ]);
    }

    // Get progress percentage for a student in a class
    public static function getProgressPercentage($studentId, $classId)
    {
        $total = self::where('student_id', $studentId)
                    ->where('class_id', $classId)
                    ->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $completed = self::where('student_id', $studentId)
                        ->where('class_id', $classId)
                        ->where('status', 'completed')
                        ->count();
        
        return round(($completed / $total) * 100, 2);
    }
}