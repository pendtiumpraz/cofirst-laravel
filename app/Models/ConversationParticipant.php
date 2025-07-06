<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'joined_at',
        'last_read_at',
        'unread_count',
        'is_admin',
        'is_muted',
        'muted_until',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_read_at' => 'datetime',
        'muted_until' => 'datetime',
        'is_admin' => 'boolean',
        'is_muted' => 'boolean',
    ];

    /**
     * Get the conversation
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the participant has muted the conversation
     */
    public function isMuted()
    {
        if (!$this->is_muted) {
            return false;
        }

        // Check if mute has expired
        if ($this->muted_until && $this->muted_until->isPast()) {
            $this->update(['is_muted' => false, 'muted_until' => null]);
            return false;
        }

        return true;
    }

    /**
     * Mute the conversation
     */
    public function mute($until = null)
    {
        $this->update([
            'is_muted' => true,
            'muted_until' => $until,
        ]);
    }

    /**
     * Unmute the conversation
     */
    public function unmute()
    {
        $this->update([
            'is_muted' => false,
            'muted_until' => null,
        ]);
    }

    /**
     * Increment unread count
     */
    public function incrementUnreadCount()
    {
        $this->increment('unread_count');
    }

    /**
     * Reset unread count
     */
    public function markAsRead()
    {
        $this->update([
            'unread_count' => 0,
            'last_read_at' => now(),
        ]);
    }
}