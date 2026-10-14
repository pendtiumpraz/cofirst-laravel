<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A chat message (soft-deletable). content is nullable for image/file messages.
 */
class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'type',
        'attachments',
        'reply_to_id',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Update conversation's last_message_at when a message is created
        static::created(function ($message) {
            Conversation::whereKey($message->conversation_id)->update([
                'last_message_at' => $message->created_at,
            ]);

            // Increment unread count for all participants except sender (one query)
            ConversationParticipant::where('conversation_id', $message->conversation_id)
                ->where('user_id', '!=', $message->sender_id)
                ->increment('unread_count');
        });
    }

    /**
     * Get the conversation
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the sender
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the message this is replying to
     */
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Get the read receipts
     */
    public function reads()
    {
        return $this->hasMany(MessageRead::class);
    }

    /**
     * Get users who have read the message
     */
    public function readBy()
    {
        return $this->belongsToMany(User::class, 'message_reads')
            ->withPivot('read_at');
    }

    /**
     * Check if a user has read the message
     */
    public function isReadBy($userId)
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    /**
     * Mark as read by a user
     */
    public function markAsReadBy($userId)
    {
        if (! $this->isReadBy($userId)) {
            MessageRead::create([
                'message_id' => $this->id,
                'user_id' => $userId,
                'read_at' => now(),
            ]);

            // Update participant's last_read_at
            $this->conversation->participants()
                ->where('user_id', $userId)
                ->update(['last_read_at' => now()]);
        }
    }

    /**
     * Edit the message
     */
    public function edit($newContent)
    {
        $this->update([
            'content' => $newContent,
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    /**
     * Get formatted content based on type
     */
    public function getFormattedContent()
    {
        switch ($this->type) {
            case 'system':
                // Plain text: views escape it; style system messages in the view.
                return (string) $this->content;
            case 'image':
                return '[Image]';
            case 'file':
                return '[File: '.($this->attachments['filename'] ?? 'Unknown').']';
            default:
                return $this->content;
        }
    }

    /**
     * Check if message can be edited
     */
    public function canBeEditedBy($userId)
    {
        // Only sender can edit
        if ($this->sender_id !== $userId) {
            return false;
        }

        // Can only edit within 15 minutes
        return $this->created_at->diffInMinutes(now()) <= 15;
    }

    /**
     * Check if message can be deleted
     */
    public function canBeDeletedBy($userId)
    {
        // Sender can always delete their own messages
        if ($this->sender_id === $userId) {
            return true;
        }

        // Admins can delete any message
        $participant = $this->conversation->participants()
            ->where('user_id', $userId)
            ->first();

        return $participant && $participant->is_admin;
    }
}
