<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_id',
        'points_spent',
        'status',
        'notes',
        'redeemed_at',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Redemption statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user who redeemed
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reward
     */
    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Get the processor (admin who processed)
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Approve redemption
     */
    public function approve($processorId, $notes = null)
    {
        $this->status = self::STATUS_APPROVED;
        $this->processed_at = now();
        $this->processed_by = $processorId;
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $this->save();
        
        return $this;
    }

    /**
     * Mark as delivered
     */
    public function markDelivered($processorId, $notes = null)
    {
        $this->status = self::STATUS_DELIVERED;
        $this->processed_at = now();
        $this->processed_by = $processorId;
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $this->save();
        
        return $this;
    }

    /**
     * Cancel redemption and refund points
     */
    public function cancel($processorId, $notes = null)
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return $this; // Already cancelled
        }
        
        $this->status = self::STATUS_CANCELLED;
        $this->processed_at = now();
        $this->processed_by = $processorId;
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $this->save();
        
        // Refund points to user
        if ($this->user->points) {
            $this->user->points->addPoints(
                $this->points_spent,
                'refund',
                "Refund for cancelled reward: {$this->reward->name}",
                $this
            );
        }
        
        // Decrease quantity redeemed
        if ($this->reward->quantity_available !== null) {
            $this->reward->decrement('quantity_redeemed');
        }
        
        return $this;
    }

    /**
     * Scope for pending redemptions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'text-yellow-600 bg-yellow-100',
            self::STATUS_APPROVED => 'text-blue-600 bg-blue-100',
            self::STATUS_DELIVERED => 'text-green-600 bg-green-100',
            self::STATUS_CANCELLED => 'text-red-600 bg-red-100',
        ];

        return $colors[$this->status] ?? 'text-gray-600 bg-gray-100';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }
}