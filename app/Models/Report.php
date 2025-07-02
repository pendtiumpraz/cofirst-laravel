<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'class_id',
        'report_date',
        'content',
        'attendance_status',
        'behavior_score',
        'academic_score',
        'notes',
    ];

    protected $casts = [
        'report_date' => 'date',
        'behavior_score' => 'integer',
        'academic_score' => 'integer',
    ];

    /**
     * Get the student for this report.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the teacher who wrote this report.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the class for this report.
     */
    public function class()
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }
}
