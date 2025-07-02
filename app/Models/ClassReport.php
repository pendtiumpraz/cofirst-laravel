<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}