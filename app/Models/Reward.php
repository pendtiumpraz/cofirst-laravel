<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * A reward in the points shop. quantity_available NULL = unlimited.
 * available_from/available_until are inclusive dates.
 */
class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'points_cost',
        'quantity_available',
        'quantity_redeemed',
        'image',
        'metadata',
        'is_active',
        'available_from',
        'available_until',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'available_from' => 'date',
        'available_until' => 'date',
    ];

    /**
     * Reward types
     */
    const TYPE_PHYSICAL = 'physical';
    const TYPE_DIGITAL = 'digital';
    const TYPE_PRIVILEGE = 'privilege';
    const TYPE_DISCOUNT = 'discount';

    /**
     * Get redemptions for this reward
     */
    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Check if reward is available
     */
    public function isAvailable()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->available_from && $now->lt($this->available_from->copy()->startOfDay())) {
            return false;
        }

        // Reason: available_until is a date; the reward stays available the whole last day.
        if ($this->available_until && $now->gt($this->available_until->copy()->endOfDay())) {
            return false;
        }
        
        if ($this->quantity_available !== null) {
            $remaining = $this->quantity_available - $this->quantity_redeemed;
            if ($remaining <= 0) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get remaining quantity
     */
    public function getRemainingQuantityAttribute()
    {
        if ($this->quantity_available === null) {
            return null; // Unlimited
        }
        
        return max(0, $this->quantity_available - $this->quantity_redeemed);
    }

    /**
     * Check if user can redeem this reward
     */
    public function canBeRedeemedBy($user)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        
        if (!$user->points || $user->points->points < $this->points_cost) {
            return false;
        }
        
        return true;
    }

    /**
     * Redeem this reward for a user (atomic).
     *
     * Locks the reward row, re-checks availability and stock, deducts the points
     * and creates a pending redemption inside one DB transaction, so a double
     * click can neither overspend points nor oversell stock.
     *
     * Returns:
     *     RewardRedemption: The pending redemption.
     *
     * Raises:
     *     \Exception: When the reward is unavailable or points are insufficient.
     */
    public function redeemFor($user)
    {
        return DB::transaction(function () use ($user) {
            $reward = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $user->initializePoints();

            if (! $reward->isAvailable()) {
                throw new \Exception('Hadiah ini sedang tidak tersedia.');
            }

            if ($user->points->points < $reward->points_cost) {
                throw new \Exception('Poin Anda belum cukup untuk menukar hadiah ini.');
            }

            $user->points->spendPoints(
                $reward->points_cost,
                PointTransaction::REASON_REWARD_REDEMPTION,
                "Redeemed: {$reward->name}",
                $reward
            );

            $redemption = $reward->redemptions()->create([
                'user_id' => $user->id,
                'points_spent' => $reward->points_cost,
                'status' => RewardRedemption::STATUS_PENDING,
                'redeemed_at' => now(),
            ]);

            if ($reward->quantity_available !== null) {
                $reward->increment('quantity_redeemed');
            }

            $this->setRawAttributes($reward->fresh()->getAttributes(), true);

            return $redemption;
        });
    }

    /**
     * Scope for active rewards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for available rewards
     */
    public function scopeAvailable($query)
    {
        // Reason: the columns are dates; compare with today's date string so the
        // reward is still listed on its last available day.
        $today = Carbon::today()->toDateString();

        return $query->active()
            ->where(function ($q) use ($today) {
                $q->whereNull('available_from')
                    ->orWhereDate('available_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('available_until')
                    ->orWhereDate('available_until', '>=', $today);
            })
            ->where(function ($q) {
                $q->whereNull('quantity_available')
                    ->orWhereRaw('quantity_redeemed < quantity_available');
            });
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        return asset('images/default-reward.png');
    }
}