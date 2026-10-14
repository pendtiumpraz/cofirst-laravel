<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * An account. Roles (spatie): superadmin, admin, teacher, student, parent, finance.
 *
 * Soft-deletable: delete() archives the account and keeps payments, reports and
 * certificates linked; forceDelete() is blocked by RESTRICT on financial records.
 * Parents and children are linked through parent_student (children()/parents()).
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

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
        'preferences',
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
        'preferences' => 'array',
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
        return $this->belongsToMany(ClassName::class, 'class_teacher', 'teacher_id', 'class_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the teacher assignments for this user.
     */
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }

    /**
     * Classes this teacher currently teaches: assigned through class_teacher,
     * is_active and status planned or active. Use for dashboards, lists and
     * authorization so all teacher screens agree.
     */
    public function taughtClasses()
    {
        return $this->teachingClasses()
            ->where('class_names.is_active', true)
            ->whereIn('class_names.status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE]);
    }

    /**
     * Whether this teacher is assigned to the given class.
     */
    public function teachesClass(int|ClassName $class): bool
    {
        $classId = $class instanceof ClassName ? $class->id : $class;

        return $this->teachingClasses()->where('class_names.id', $classId)->exists();
    }

    /**
     * Get the active enrollments for this student.
     */
    public function activeEnrollments()
    {
        return $this->enrollments()->where('enrollments.status', Enrollment::STATUS_ACTIVE);
    }

    /**
     * Classes this student is enrolled in (any enrollment status; see pivot).
     */
    public function enrolledClasses()
    {
        return $this->belongsToMany(ClassName::class, 'enrollments', 'student_id', 'class_id')
            ->withPivot(['id', 'status', 'enrollment_date', 'payment_status'])
            ->withTimestamps();
    }

    /**
     * Attendance rows of this student.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    /**
     * Per-meeting reports about this student.
     */
    public function classReports()
    {
        return $this->hasMany(ClassReport::class, 'student_id');
    }

    /**
     * Per-meeting reports written by this teacher.
     */
    public function writtenClassReports()
    {
        return $this->hasMany(ClassReport::class, 'teacher_id');
    }

    /**
     * Learning progress rows of this student.
     */
    public function classProgress()
    {
        return $this->hasMany(ClassProgress::class, 'student_id');
    }

    /**
     * Certificates issued to this student.
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'student_id');
    }

    /**
     * Testimonials written by this (parent) user.
     */
    public function testimonials()
    {
        return $this->hasMany(Testimonial::class);
    }

    /**
     * Invoices this parent pays for (payer_id).
     */
    public function paidTransactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'payer_id');
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
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')
            ->withTimestamps()
            ->select('users.*'); // Explicitly select columns from users table to avoid ambiguity
    }

    /**
     * Whether this parent is linked to the given student.
     */
    public function isParentOf(int|User $student): bool
    {
        $studentId = $student instanceof User ? $student->id : $student;

        return $this->children()->where('users.id', $studentId)->exists();
    }

    /**
     * Get the parents for this student.
     */
    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
            ->withTimestamps()
            ->select('users.*'); // Explicitly select columns from users table to avoid ambiguity
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
     * Get the URL to the user's profile photo (default avatar when none).
     *
     * Reason: the old version ran file_exists() and inlined the image as base64 on
     * every call in the local environment, which bloated every page with avatars.
     */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
            ? asset('storage/'.$this->profile_photo_path)
            : asset('images/default-avatar.png');
    }

    /**
     * Age in years from the profile's birth date (null when unknown).
     */
    public function getAgeAttribute(): ?int
    {
        return $this->profile?->age;
    }

    /**
     * Whether this user is a child (younger than 18 by profile birth date).
     */
    public function isChild(): bool
    {
        return (bool) $this->profile?->isChild();
    }

    /**
     * Read one per-user preference (users.preferences JSON).
     */
    public function preference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences ?? [], $key, $default);
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

    /**
     * Get user's conversations
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['joined_at', 'last_read_at', 'unread_count', 'is_admin', 'is_muted', 'muted_until'])
            ->withTimestamps();
    }

    /**
     * Get user's conversation participations
     */
    public function conversationParticipants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Get user's sent messages
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get total unread messages count
     */
    public function getUnreadMessagesCountAttribute()
    {
        return $this->conversationParticipants()->sum('unread_count');
    }

    /**
     * Get conversations with unread messages
     */
    public function unreadConversations()
    {
        return $this->conversations()->wherePivot('unread_count', '>', 0);
    }

    /**
     * Get user's points
     */
    public function points()
    {
        return $this->hasOne(UserPoint::class);
    }

    /**
     * Get user's point transactions
     */
    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    /**
     * Get user's badges
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot(['earned_at', 'earned_for', 'is_featured'])
            ->withTimestamps()
            ->using(UserBadge::class);
    }

    /**
     * Get user's featured badges
     */
    public function featuredBadges()
    {
        return $this->badges()->wherePivot('is_featured', true);
    }

    /**
     * Get user's reward redemptions
     */
    public function rewardRedemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Initialize points for user if not exists
     */
    public function initializePoints()
    {
        if (! $this->points) {
            // Reason: firstOrCreate on the unique user_id avoids duplicate rows when two
            // requests initialise points at the same time.
            $this->points()->firstOrCreate([], [
                'points' => 0,
                'total_earned' => 0,
                'total_spent' => 0,
                'level' => 1,
                'current_streak' => 0,
                'longest_streak' => 0,
            ]);
            // Load the relationship after creating
            $this->load('points');
        }

        return $this->points;
    }

    /**
     * Add points to user
     */
    public function addPoints($amount, $reason, $description = null, $relatedModel = null)
    {
        $this->initializePoints();

        return $this->points->addPoints($amount, $reason, $description, $relatedModel);
    }

    /**
     * Check if user has enough points
     */
    public function hasPoints($amount)
    {
        return $this->points && $this->points->points >= $amount;
    }

    /**
     * Get user's current level
     */
    public function getCurrentLevelAttribute()
    {
        return $this->points ? $this->points->level : 1;
    }

    /**
     * Get user's current points
     */
    public function getCurrentPointsAttribute()
    {
        return $this->points ? $this->points->points : 0;
    }
}
