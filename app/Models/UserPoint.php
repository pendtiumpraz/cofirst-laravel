<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * A user's point balance, level and activity streak (one row per user;
 * user_id is unique).
 *
 * All balance changes run in a DB transaction with a row lock and write a
 * PointTransaction: addPoints() (type earned), spendPoints() (type spent) and
 * refundPoints() (type refund: restores points and total_spent without raising
 * total_earned or the level). The streak only moves for login/attendance points.
 */
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
        'points' => 'integer',
        'total_earned' => 'integer',
        'total_spent' => 'integer',
        'level' => 'integer',
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
    ];

    /** Reasons that count as daily activity for the streak. */
    public const STREAK_REASONS = [PointTransaction::REASON_LOGIN, PointTransaction::REASON_ATTENDANCE];

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
     * Add earned points (atomic) and record an "earned" transaction.
     *
     * Args:
     *     amount (int): Points to add.
     *     reason (string): PointTransaction::REASON_* value.
     *     description (string|null): Human readable description.
     *     relatedModel (Model|null): Model that caused the points.
     *
     * Returns:
     *     self: This model, refreshed with the new balance.
     */
    public function addPoints($amount, $reason, $description = null, $relatedModel = null)
    {
        return $this->mutateLocked(function (self $row) use ($amount, $reason) {
            $row->points += $amount;
            $row->total_earned += $amount;
            $row->checkLevelUp();

            if (in_array($reason, self::STREAK_REASONS, true)) {
                $row->updateActivityStreak();
            }
        }, $amount, PointTransaction::TYPE_EARNED, $reason, $description, $relatedModel);
    }

    /**
     * Spend points (atomic); throws when the balance is insufficient.
     *
     * Returns:
     *     self: This model, refreshed with the new balance.
     *
     * Raises:
     *     \Exception: When the user has fewer points than $amount.
     */
    public function spendPoints($amount, $reason, $description = null, $relatedModel = null)
    {
        return $this->mutateLocked(function (self $row) use ($amount) {
            if ($row->points < $amount) {
                throw new \Exception('Poin tidak mencukupi.');
            }

            $row->points -= $amount;
            $row->total_spent += $amount;
        }, -$amount, PointTransaction::TYPE_SPENT, $reason, $description, $relatedModel);
    }

    /**
     * Give spent points back (e.g. a cancelled reward redemption).
     *
     * Restores the balance and lowers total_spent; total_earned, level and streak
     * are untouched, and the transaction is recorded with type "refund".
     *
     * Returns:
     *     self: This model, refreshed with the new balance.
     */
    public function refundPoints($amount, $reason = 'refund', $description = null, $relatedModel = null)
    {
        return $this->mutateLocked(function (self $row) use ($amount) {
            $row->points += $amount;
            $row->total_spent = max(0, $row->total_spent - $amount);
        }, $amount, PointTransaction::TYPE_REFUND, $reason, $description, $relatedModel);
    }

    /**
     * Apply a balance change to a freshly locked copy of this row, save it and
     * write the matching PointTransaction, all inside one DB transaction.
     *
     * Reason: the previous read-modify-write on the in-memory model lost updates
     * when two requests (e.g. a double click) changed points at the same time.
     */
    protected function mutateLocked(callable $change, $points, $type, $reason, $description, $relatedModel)
    {
        DB::transaction(function () use ($change, $points, $type, $reason, $description, $relatedModel) {
            $row = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $change($row);
            $row->save();

            $this->setRawAttributes($row->getAttributes(), true);
            $this->createTransaction($points, $type, $reason, $description, $relatedModel);
        });

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

        if ($this->last_activity_date && $this->last_activity_date->isSameDay($today)) {
            return;
        }

        if (! $this->last_activity_date) {
            // First activity
            $this->current_streak = 1;
            $this->longest_streak = 1;
        } elseif ($this->last_activity_date->isYesterday()) {
            // Continuing streak
            $this->current_streak++;
            if ($this->current_streak > $this->longest_streak) {
                $this->longest_streak = $this->current_streak;
            }
        } elseif (! $this->last_activity_date->isToday()) {
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

        if (! isset(self::LEVEL_POINTS[$nextLevel])) {
            return 100; // Max level
        }

        $nextLevelPoints = self::LEVEL_POINTS[$nextLevel];
        $pointsInCurrentLevel = $this->total_earned - $currentLevelPoints;
        $pointsNeededForLevel = $nextLevelPoints - $currentLevelPoints;

        return min(100, round(($pointsInCurrentLevel / $pointsNeededForLevel) * 100));
    }
}
