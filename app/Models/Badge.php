<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image_path',
        'category',
        'level',
        'points_required',
        'criteria',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Badge categories
     */
    const CATEGORY_ACADEMIC = 'academic';
    const CATEGORY_SOCIAL = 'social';
    const CATEGORY_ATTENDANCE = 'attendance';
    const CATEGORY_SPECIAL = 'special';

    /**
     * Badge levels
     */
    const LEVEL_BRONZE = 'bronze';
    const LEVEL_SILVER = 'silver';
    const LEVEL_GOLD = 'gold';
    const LEVEL_PLATINUM = 'platinum';

    /**
     * Get users who have earned this badge
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot(['earned_at', 'earned_for', 'is_featured'])
            ->withTimestamps();
    }

    /**
     * Check if user has earned this badge
     */
    public function isEarnedBy($user)
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Award badge to user
     */
    public function awardTo($user, $earnedFor = null)
    {
        if ($this->isEarnedBy($user)) {
            return false; // Already has this badge
        }

        $this->users()->attach($user->id, [
            'earned_at' => now(),
            'earned_for' => $earnedFor ? json_encode($earnedFor) : null,
        ]);

        // Award points for earning the badge (create the balance row when missing)
        if ($this->points_required > 0) {
            $user->initializePoints()->addPoints(
                $this->points_required,
                PointTransaction::REASON_BADGE_EARNED,
                "Earned badge: {$this->name}",
                $this
            );
        }

        return true;
    }

    /**
     * Scope for active badges
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Get level color
     */
    public function getLevelColorAttribute()
    {
        $colors = [
            self::LEVEL_BRONZE => 'text-orange-600',
            self::LEVEL_SILVER => 'text-gray-500',
            self::LEVEL_GOLD => 'text-yellow-500',
            self::LEVEL_PLATINUM => 'text-purple-600',
        ];

        return $colors[$this->level] ?? 'text-gray-600';
    }

    /**
     * Get level background color
     */
    public function getLevelBgColorAttribute()
    {
        $colors = [
            self::LEVEL_BRONZE => 'bg-orange-100',
            self::LEVEL_SILVER => 'bg-gray-100',
            self::LEVEL_GOLD => 'bg-yellow-100',
            self::LEVEL_PLATINUM => 'bg-purple-100',
        ];

        return $colors[$this->level] ?? 'bg-gray-100';
    }

    /**
     * Check whether a user meets this badge's criteria, using real data.
     *
     * Supported criteria keys (all must be met):
     * - first_login (bool): the user has a points row.
     * - total_points (int): total points ever earned.
     * - study_streak / login_streak (int): longest daily streak.
     * - attendance_count / classes_attended (int): meetings attended (present/late).
     * - materials_read (int): distinct materials opened.
     * - creative_projects / projects_published (int): projects in the gallery.
     * - meetings_completed (int): completed class_progress rows.
     * Unknown keys (features that do not exist yet) are treated as not met.
     *
     * Args:
     *     user (User): The user.
     *     context (array): Optional pre-computed values keyed like the criteria.
     *
     * Returns:
     *     bool: True when every criterion is met.
     */
    public function checkCriteria($user, $context = [])
    {
        $criteria = $this->criteria ?? [];

        if ($criteria === []) {
            return false;
        }

        foreach ($criteria as $key => $required) {
            $actual = $context[$key] ?? $this->measureCriterion($user, $key);

            if ($actual === null) {
                return false;
            }

            if (is_bool($required) ? (bool) $actual !== $required : (int) $actual < (int) $required) {
                return false;
            }
        }

        return true;
    }

    /**
     * Measure one criterion for a user, or null when the key is unsupported.
     */
    protected function measureCriterion($user, string $key): int|bool|null
    {
        return match ($key) {
            'first_login' => $user->points()->exists(),
            'total_points' => (int) $user->points()->value('total_earned'),
            'study_streak', 'login_streak' => (int) $user->points()->value('longest_streak'),
            'attendance_count', 'classes_attended' => Attendance::where('student_id', $user->id)->attended()->count(),
            'materials_read' => MaterialAccess::where('student_id', $user->id)->distinct()->count('material_id'),
            'creative_projects', 'projects_published' => ProjectGallery::where('student_id', $user->id)->count(),
            'meetings_completed' => ClassProgress::where('student_id', $user->id)->where('status', 'completed')->count(),
            default => null,
        };
    }
}
