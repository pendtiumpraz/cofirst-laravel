<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'points',
        'type',
        'reason',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Transaction types
     */
    const TYPE_EARNED = 'earned';
    const TYPE_SPENT = 'spent';
    const TYPE_BONUS = 'bonus';
    const TYPE_PENALTY = 'penalty';

    /**
     * Common reasons for earning points
     */
    const REASON_LOGIN = 'login';
    const REASON_ASSIGNMENT_COMPLETE = 'assignment_complete';
    const REASON_QUIZ_COMPLETE = 'quiz_complete';
    const REASON_QUIZ_PERFECT = 'quiz_perfect';
    const REASON_ATTENDANCE = 'attendance';
    const REASON_PARTICIPATION = 'participation';
    const REASON_BADGE_EARNED = 'badge_earned';
    const REASON_LEVEL_UP = 'level_up';
    const REASON_STREAK_BONUS = 'streak_bonus';
    const REASON_REWARD_REDEMPTION = 'reward_redemption';

    /**
     * Points values for different actions
     */
    const POINTS = [
        self::REASON_LOGIN => 5,
        self::REASON_ASSIGNMENT_COMPLETE => 20,
        self::REASON_QUIZ_COMPLETE => 15,
        self::REASON_QUIZ_PERFECT => 30,
        self::REASON_ATTENDANCE => 10,
        self::REASON_PARTICIPATION => 5,
        self::REASON_LEVEL_UP => 50,
        self::REASON_STREAK_BONUS => [
            7 => 25,   // 1 week streak
            14 => 50,  // 2 week streak
            30 => 100, // 1 month streak
        ],
    ];

    /**
     * Get the user that owns the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model
     */
    public function related()
    {
        return $this->morphTo();
    }

    /**
     * Scope for earned points
     */
    public function scopeEarned($query)
    {
        return $query->where('type', self::TYPE_EARNED);
    }

    /**
     * Scope for spent points
     */
    public function scopeSpent($query)
    {
        return $query->where('type', self::TYPE_SPENT);
    }

    /**
     * Get formatted description
     */
    public function getFormattedDescriptionAttribute()
    {
        if ($this->description) {
            return $this->description;
        }

        $descriptions = [
            self::REASON_LOGIN => 'Daily login bonus',
            self::REASON_ASSIGNMENT_COMPLETE => 'Completed assignment',
            self::REASON_QUIZ_COMPLETE => 'Completed quiz',
            self::REASON_QUIZ_PERFECT => 'Perfect score on quiz',
            self::REASON_ATTENDANCE => 'Class attendance',
            self::REASON_PARTICIPATION => 'Class participation',
            self::REASON_BADGE_EARNED => 'Earned a badge',
            self::REASON_LEVEL_UP => 'Level up bonus',
            self::REASON_STREAK_BONUS => 'Login streak bonus',
            self::REASON_REWARD_REDEMPTION => 'Redeemed reward',
        ];

        return $descriptions[$this->reason] ?? ucfirst(str_replace('_', ' ', $this->reason));
    }

    /**
     * Get icon for transaction reason
     */
    public function getIconAttribute()
    {
        $icons = [
            self::REASON_LOGIN => 'calendar-check',
            self::REASON_ASSIGNMENT_COMPLETE => 'clipboard-check',
            self::REASON_QUIZ_COMPLETE => 'check-circle',
            self::REASON_QUIZ_PERFECT => 'star',
            self::REASON_ATTENDANCE => 'user-check',
            self::REASON_PARTICIPATION => 'hand-raised',
            self::REASON_BADGE_EARNED => 'award',
            self::REASON_LEVEL_UP => 'trending-up',
            self::REASON_STREAK_BONUS => 'fire',
            self::REASON_REWARD_REDEMPTION => 'gift',
        ];

        return $icons[$this->reason] ?? 'circle';
    }
}