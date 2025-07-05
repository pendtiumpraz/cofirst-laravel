<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        
        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }
        
        if ($this->available_until && $now->gt($this->available_until)) {
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
     * Redeem reward for user
     */
    public function redeemFor($user)
    {
        if (!$this->canBeRedeemedBy($user)) {
            throw new \Exception('Cannot redeem this reward');
        }
        
        // Deduct points
        $user->points->spendPoints(
            $this->points_cost,
            PointTransaction::REASON_REWARD_REDEMPTION,
            "Redeemed: {$this->name}",
            $this
        );
        
        // Create redemption record
        $redemption = $this->redemptions()->create([
            'user_id' => $user->id,
            'points_spent' => $this->points_cost,
            'status' => RewardRedemption::STATUS_PENDING,
            'redeemed_at' => now(),
        ]);
        
        // Update quantity redeemed
        if ($this->quantity_available !== null) {
            $this->increment('quantity_redeemed');
        }
        
        return $redemption;
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
        $now = Carbon::now();
        
        return $query->active()
            ->where(function ($q) use ($now) {
                $q->whereNull('available_from')
                    ->orWhere('available_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('available_until')
                    ->orWhere('available_until', '>=', $now);
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