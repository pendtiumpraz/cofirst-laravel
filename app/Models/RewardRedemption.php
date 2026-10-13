<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A user's redemption of a reward.
 *
 * Allowed transitions: pending -> approved -> delivered; pending/approved ->
 * delivered; pending/approved -> cancelled (points refunded, stock released).
 * An invalid transition throws a ValidationException (field "action") so the
 * admin is redirected back with a message instead of corrupting the record.
 */
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
     * Allowed source statuses for each target status.
     *
     * @var array<string, array<int, string>>
     */
    public const TRANSITIONS = [
        self::STATUS_APPROVED => [self::STATUS_PENDING],
        self::STATUS_DELIVERED => [self::STATUS_PENDING, self::STATUS_APPROVED],
        self::STATUS_CANCELLED => [self::STATUS_PENDING, self::STATUS_APPROVED],
    ];

    /**
     * Whether the redemption may move to the given status.
     *
     * Args:
     *     status (string): Target status.
     */
    public function canTransitionTo(string $status): bool
    {
        return in_array($this->status, self::TRANSITIONS[$status] ?? [], true);
    }

    /**
     * Approve redemption (only from pending).
     */
    public function approve($processorId, $notes = null)
    {
        return $this->transitionTo(self::STATUS_APPROVED, $processorId, $notes);
    }

    /**
     * Mark as delivered (from pending or approved).
     */
    public function markDelivered($processorId, $notes = null)
    {
        return $this->transitionTo(self::STATUS_DELIVERED, $processorId, $notes);
    }

    /**
     * Cancel redemption, refund the points and release the stock (atomic).
     *
     * Cancelling an already cancelled redemption is a no-op.
     */
    public function cancel($processorId, $notes = null)
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return $this; // Already cancelled
        }

        DB::transaction(function () use ($processorId, $notes) {
            $this->transitionTo(self::STATUS_CANCELLED, $processorId, $notes);

            $points = $this->user->initializePoints();
            $points->refundPoints(
                $this->points_spent,
                PointTransaction::REASON_REFUND,
                "Refund for cancelled reward: {$this->reward->name}",
                $this
            );

            if ($this->reward->quantity_available !== null && $this->reward->quantity_redeemed > 0) {
                $this->reward->decrement('quantity_redeemed');
            }
        });

        return $this;
    }

    /**
     * Validate and apply a status transition.
     *
     * Raises:
     *     ValidationException: When the transition is not allowed.
     */
    protected function transitionTo(string $status, $processorId, $notes = null)
    {
        if (! $this->canTransitionTo($status)) {
            throw ValidationException::withMessages([
                'action' => "Status penukaran tidak dapat diubah dari \"{$this->status}\" ke \"{$status}\".",
            ]);
        }

        $this->status = $status;
        $this->processed_at = now();
        $this->processed_by = $processorId;

        if ($notes) {
            $this->notes = $notes;
        }

        $this->save();

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