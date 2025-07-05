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

        // Award points for earning the badge
        if ($user->points) {
            $user->points->addPoints(
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
     * Check if criteria is met for a user
     */
    public function checkCriteria($user, $context = [])
    {
        // This would be implemented based on specific criteria types
        // For now, returning false as placeholder
        return false;
    }
}