<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'is_active',
        'enrollment_id',
        'teacher_assignment_id',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
        'day_of_week' => 'integer',
    ];

    /**
     * Get the class for this schedule.
     */
    public function className()
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /**
     * Get the enrollment for this schedule.
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    /**
     * Get the teacher assignment for this schedule.
     */
    public function teacherAssignment()
    {
        return $this->belongsTo(TeacherAssignment::class, 'teacher_assignment_id');
    }

    /**
     * Get the class reports for this schedule.
     */
    public function classReports()
    {
        return $this->hasMany(ClassReport::class, 'schedule_id');
    }

    /**
     * Scope to get only active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk schedule yang bisa ditampilkan di calendar
     * (hanya kelas yang aktif dan belum selesai)
     */
    public function scopeForCalendar($query)
    {
        return $query->where('is_active', true)
                    ->whereHas('className', function($q) {
                        $q->where('is_active', true)
                          ->whereIn('status', ['planned', 'active']);
                    });
    }

    /**
     * Scope untuk schedule berdasarkan status kelas
     */
    public function scopeByClassStatus($query, $status)
    {
        return $query->whereHas('className', function($q) use ($status) {
            $q->where('status', $status);
        });
    }

    /**
     * Check apakah schedule bisa ditampilkan di calendar
     */
    public function canShowInCalendar()
    {
        return $this->is_active && 
               $this->className && 
               $this->className->canShowInCalendar();
    }

    /**
     * Get the day name.
     */
    public function getDayNameAttribute()
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return $days[$this->day_of_week] ?? '';
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
}