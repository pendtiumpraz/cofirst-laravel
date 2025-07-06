<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserBadge extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at',
        'earned_for',
        'is_featured',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'earned_for' => 'array',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the badge
     */
    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Scope for featured badges
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured()
    {
        $this->is_featured = !$this->is_featured;
        $this->save();
        
        return $this->is_featured;
    }
}