<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'points',
        'total_earned',
        'total_spent',
        'level',
        'current_streak',
        'longest_streak',
        'last_activity_date',
    ];

    protected $casts = [
        'last_activity_date' => 'date',
    ];

    /**
     * Points required for each level
     */
    const LEVEL_POINTS = [
        1 => 0,
        2 => 100,
        3 => 300,
        4 => 600,
        5 => 1000,
        6 => 1500,
        7 => 2500,
        8 => 4000,
        9 => 6000,
        10 => 10000,
    ];

    /**
     * Get the user that owns the points
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add points to user
     */
    public function addPoints($amount, $reason, $description = null, $relatedModel = null)
    {
        $this->points += $amount;
        $this->total_earned += $amount;
        
        // Check for level up
        $this->checkLevelUp();
        
        // Update activity and streak
        $this->updateActivityStreak();
        
        $this->save();
        
        // Create transaction record
        $this->createTransaction($amount, 'earned', $reason, $description, $relatedModel);
        
        return $this;
    }

    /**
     * Spend points
     */
    public function spendPoints($amount, $reason, $description = null, $relatedModel = null)
    {
        if ($this->points < $amount) {
            throw new \Exception('Insufficient points');
        }
        
        $this->points -= $amount;
        $this->total_spent += $amount;
        $this->save();
        
        // Create transaction record
        $this->createTransaction(-$amount, 'spent', $reason, $description, $relatedModel);
        
        return $this;
    }

    /**
     * Check and update level based on total earned points
     */
    protected function checkLevelUp()
    {
        $newLevel = 1;
        
        foreach (self::LEVEL_POINTS as $level => $requiredPoints) {
            if ($this->total_earned >= $requiredPoints) {
                $newLevel = $level;
            } else {
                break;
            }
        }
        
        if ($newLevel > $this->level) {
            $this->level = $newLevel;
            
            // Trigger level up event/notification
            // TODO: Add notification for level up
        }
    }

    /**
     * Update activity streak
     */
    protected function updateActivityStreak()
    {
        $today = Carbon::today();
        
        if (!$this->last_activity_date) {
            // First activity
            $this->current_streak = 1;
            $this->longest_streak = 1;
        } elseif ($this->last_activity_date->isYesterday()) {
            // Continuing streak
            $this->current_streak++;
            if ($this->current_streak > $this->longest_streak) {
                $this->longest_streak = $this->current_streak;
            }
        } elseif (!$this->last_activity_date->isToday()) {
            // Streak broken
            $this->current_streak = 1;
        }
        
        $this->last_activity_date = $today;
    }

    /**
     * Create transaction record
     */
    protected function createTransaction($points, $type, $reason, $description, $relatedModel)
    {
        $transaction = new PointTransaction([
            'user_id' => $this->user_id,
            'points' => $points,
            'type' => $type,
            'reason' => $reason,
            'description' => $description,
        ]);
        
        if ($relatedModel) {
            $transaction->related()->associate($relatedModel);
        }
        
        $transaction->save();
    }

    /**
     * Get points needed for next level
     */
    public function getPointsForNextLevelAttribute()
    {
        $nextLevel = $this->level + 1;
        
        if (isset(self::LEVEL_POINTS[$nextLevel])) {
            return self::LEVEL_POINTS[$nextLevel] - $this->total_earned;
        }
        
        return 0; // Max level reached
    }

    /**
     * Get level progress percentage
     */
    public function getLevelProgressAttribute()
    {
        $currentLevelPoints = self::LEVEL_POINTS[$this->level];
        $nextLevel = $this->level + 1;
        
        if (!isset(self::LEVEL_POINTS[$nextLevel])) {
            return 100; // Max level
        }
        
        $nextLevelPoints = self::LEVEL_POINTS[$nextLevel];
        $pointsInCurrentLevel = $this->total_earned - $currentLevelPoints;
        $pointsNeededForLevel = $nextLevelPoints - $currentLevelPoints;
        
        return min(100, round(($pointsInCurrentLevel / $pointsNeededForLevel) * 100));
    }
}