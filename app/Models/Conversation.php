<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'class_id',
        'last_message_at',
        'is_active',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the participants of the conversation
     */
    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Get the users in the conversation
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['joined_at', 'last_read_at', 'unread_count', 'is_admin', 'is_muted', 'muted_until'])
            ->withTimestamps();
    }

    /**
     * Get the messages in the conversation
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the latest message
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Get the class if this is a class conversation
     */
    public function class()
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /**
     * Add a participant to the conversation
     */
    public function addParticipant($userId, $isAdmin = false)
    {
        return $this->participants()->firstOrCreate([
            'user_id' => $userId,
        ], [
            'is_admin' => $isAdmin,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove a participant from the conversation
     */
    public function removeParticipant($userId)
    {
        return $this->participants()->where('user_id', $userId)->delete();
    }

    /**
     * Check if a user is a participant
     */
    public function hasParticipant($userId)
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }

    /**
     * Get or create a private conversation between two users
     */
    public static function getPrivateConversation($user1Id, $user2Id)
    {
        // Look for existing conversation
        $conversation = self::where('type', 'private')
            ->whereHas('participants', function ($query) use ($user1Id) {
                $query->where('user_id', $user1Id);
            })
            ->whereHas('participants', function ($query) use ($user2Id) {
                $query->where('user_id', $user2Id);
            })
            ->withCount('participants')
            ->having('participants_count', '=', 2)
            ->first();

        if (!$conversation) {
            // Create new conversation
            $conversation = self::create(['type' => 'private']);
            $conversation->addParticipant($user1Id);
            $conversation->addParticipant($user2Id);
        }

        return $conversation;
    }

    /**
     * Get conversation name for display
     */
    public function getDisplayName($currentUserId = null)
    {
        if ($this->name) {
            return $this->name;
        }

        if ($this->type === 'private' && $currentUserId) {
            $otherUser = $this->users()->where('users.id', '!=', $currentUserId)->first();
            return $otherUser ? $otherUser->name : 'Unknown User';
        }

        if ($this->type === 'class' && $this->class) {
            return $this->class->name . ' Chat';
        }

        return 'Conversation';
    }

    /**
     * Mark all messages as read for a user
     */
    public function markAsRead($userId)
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        if ($participant) {
            $participant->update([
                'last_read_at' => now(),
                'unread_count' => 0,
            ]);
        }
    }
}