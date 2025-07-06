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
        'photo_path',
        'start_date',
        'end_date',
        'max_students',
        'status',
        'type',
        'delivery_method', // Kolom baru
        'curriculum_id',   // Kolom baru
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
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

    public function classPhotos()
    {
        return $this->hasMany(ClassPhoto::class, 'class_id');
    }

    /**
     * Scope untuk kelas yang aktif dan belum selesai
     */
    public function scopeActiveAndOngoing($query)
    {
        return $query->where('is_active', true)
                    ->whereIn('status', ['planned', 'active']);
    }

    /**
     * Scope untuk kelas yang sedang berlangsung
     */
    public function scopeOngoing($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk kelas yang sudah selesai
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check apakah kelas masih bisa ditampilkan di calendar
     */
    public function canShowInCalendar()
    {
        return $this->is_active && in_array($this->status, ['planned', 'active']);
    }

    /**
     * Check apakah kelas sudah selesai
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'planned' => 'bg-blue-100 text-blue-800',
            'active' => 'bg-green-100 text-green-800',
            'completed' => 'bg-gray-100 text-gray-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'planned' => 'Direncanakan',
            'active' => 'Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get the URL to the class photo.
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : asset('images/default-class-photo.png');
    }
}