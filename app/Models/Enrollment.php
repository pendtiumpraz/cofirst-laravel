<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'class_id',
        'enrollment_date',
        'status',
        'payment_status',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the student for this enrollment.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the class for this enrollment.
     */
    public function class()
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /**
     * Get the class for this enrollment (alias for consistency).
     */
    public function className()
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /**
     * Get the course for this enrollment.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Scope to get only active enrollments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
