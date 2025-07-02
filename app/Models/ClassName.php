<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassName extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'teacher_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'max_students',
        'status',
        'type',
        'delivery_method', // Kolom baru
        'curriculum_id',   // Kolom baru
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class); // Relasi baru
    }
}