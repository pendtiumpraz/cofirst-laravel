<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'profile_photo_path',
        'photo_crop_data',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'photo_crop_data' => 'array',
    ];

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the classes where this user is a teacher.
     */
    public function teachingClasses()
    {
        return $this->hasMany(ClassName::class, 'teacher_id');
    }

    /**
     * Get the teacher assignments for this user.
     */
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }

    /**
     * Get the enrollments for this student.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    /**
     * Get the students for this parent.
     */
    public function children()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id');
    }

    /**
     * Get the parents for this student.
     */
    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id');
    }

    /**
     * Get the reports for this student.
     */
    public function studentReports()
    {
        return $this->hasMany(Report::class, 'student_id');
    }

    /**
     * Get the reports written by this teacher.
     */
    public function teacherReports()
    {
        return $this->hasMany(Report::class, 'teacher_id');
    }

    /**
     * Get the financial transactions for this student.
     */
    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'student_id');
    }

    /**
     * Check if user is a parent.
     */
    public function isParent()
    {
        return $this->hasRole('parent');
    }

    /**
     * Check if user is a student.
     */
    public function isStudent()
    {
        return $this->hasRole('student');
    }

    /**
     * Check if user is a teacher.
     */
    public function isTeacher()
    {
        return $this->hasRole('teacher');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is finance.
     */
    public function isFinance()
    {
        return $this->hasRole('finance');
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the URL to the user's profile photo.
     */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
            ? asset('storage/' . $this->profile_photo_path)
            : asset('images/default-avatar.png');
    }

    /**
     * Get the user's project galleries.
     */
    public function projectGalleries()
    {
        return $this->hasMany(ProjectGallery::class, 'student_id');
    }

    /**
     * Get the class photos uploaded by this user.
     */
    public function uploadedClassPhotos()
    {
        return $this->hasMany(ClassPhoto::class, 'uploaded_by');
    }
}
