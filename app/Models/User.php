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
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')
            ->select('users.*'); // Explicitly select columns from users table to avoid ambiguity
    }

    /**
     * Get the parents for this student.
     */
    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
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
     * Get the URL to the user's profile photo.
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            // For development, use base64 encoded image if HTTP access fails
            $filePath = public_path('storage/' . $this->profile_photo_path);
            
            if (file_exists($filePath)) {
                // First try normal asset URL
                $assetUrl = asset('storage/' . $this->profile_photo_path);
                
                // If we're in development and having HTTP access issues, 
                // we can return base64 data URL as fallback
                if (app()->environment('local') && !$this->isUrlAccessible($assetUrl)) {
                    $imageData = base64_encode(file_get_contents($filePath));
                    $mimeType = mime_content_type($filePath);
                    return "data:$mimeType;base64,$imageData";
                }
                
                return $assetUrl;
            }
        }
        
        return asset('images/default-avatar.png');
    }

    /**
     * Check if URL is accessible via HTTP
     */
    private function isUrlAccessible($url)
    {
        // In development, we'll use base64 fallback
        // In production, this should work with proper server configuration
        return !app()->environment('local');
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
            ->as('pivot')
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
        if (!$this->points) {
            $this->points()->create([
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
