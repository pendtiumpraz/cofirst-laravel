<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\ClassName;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * Display chat interface
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get user's conversations
        $conversations = $user->conversations()
            ->with(['latestMessage.sender', 'users' => function ($query) use ($user) {
                $query->where('users.id', '!=', $user->id);
            }])
            ->orderBy('last_message_at', 'desc')
            ->get();

        // Get available contacts based on role
        $contacts = $this->getAvailableContacts($user);

        return view('chat.index', compact('conversations', 'contacts'));
    }

    /**
     * Show a specific conversation
     */
    public function show(Conversation $conversation)
    {
        $user = Auth::user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            abort(403, 'You are not a participant in this conversation.');
        }

        // Mark conversation as read
        $conversation->markAsRead($user->id);

        // Get messages with sender info
        $messages = $conversation->messages()
            ->with(['sender', 'replyTo', 'readBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Get conversation participants
        $participants = $conversation->users()->get();

        return view('chat.show', compact('conversation', 'messages', 'participants'));
    }

    /**
     * Start a new conversation
     */
    public function startConversation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        
        $otherUserId = $request->user_id;

        // Check if user can chat with this person
        if (!$this->canChatWith($user, $otherUserId)) {
            return response()->json(['error' => 'You cannot start a conversation with this user.'], 403);
        }

        // Get or create private conversation
        $conversation = Conversation::getPrivateConversation($user->id, $otherUserId);

        return response()->json([
            'conversation_id' => $conversation->id,
            'redirect_url' => route('chat.show', $conversation),
        ]);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'content' => 'required_without:attachments|string|max:5000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
            'reply_to_id' => 'nullable|exists:messages,id',
        ]);

        $user = Auth::user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            abort(403, 'You are not a participant in this conversation.');
        }

        $attachments = [];
        
        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('chat-attachments/' . $conversation->id, 'public');
                $attachments[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content' => $request->content ?? '',
            'type' => !empty($attachments) ? 'file' : 'text',
            'attachments' => !empty($attachments) ? $attachments : null,
            'reply_to_id' => $request->reply_to_id,
        ]);

        // Load relationships
        $message->load(['sender', 'replyTo']);

        // Broadcast message
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $message,
            'html' => view('chat.partials.message', compact('message'))->render(),
        ]);
    }

    /**
     * Edit a message
     */
    public function editMessage(Request $request, Message $message)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $user = Auth::user();

        // Check if user can edit
        if (!$message->canBeEditedBy($user->id)) {
            abort(403, 'You cannot edit this message.');
        }

        $message->edit($request->content);

        return response()->json([
            'message' => $message,
            'success' => true,
        ]);
    }

    /**
     * Delete a message
     */
    public function deleteMessage(Message $message)
    {
        $user = Auth::user();

        // Check if user can delete
        if (!$message->canBeDeletedBy($user->id)) {
            abort(403, 'You cannot delete this message.');
        }

        $message->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, Conversation $conversation)
    {
        $user = Auth::user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            abort(403);
        }

        $conversation->markAsRead($user->id);

        // Mark individual messages as read
        $messageIds = $request->input('message_ids', []);
        if (!empty($messageIds)) {
            foreach ($messageIds as $messageId) {
                $message = Message::find($messageId);
                if ($message && $message->conversation_id === $conversation->id) {
                    $message->markAsReadBy($user->id);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Search conversations and messages
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $user = Auth::user();
        $query = $request->query;

        // Search conversations by participant names
        $conversations = $user->conversations()
            ->whereHas('users', function ($q) use ($query, $user) {
                $q->where('users.id', '!=', $user->id)
                  ->where('name', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        // Search messages
        $messages = Message::whereHas('conversation.participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('content', 'like', "%{$query}%")
            ->with(['conversation', 'sender'])
            ->limit(10)
            ->get();

        return response()->json([
            'conversations' => $conversations,
            'messages' => $messages,
        ]);
    }

    /**
     * Get available contacts based on user role
     */
    private function getAvailableContacts($user)
    {
        $contacts = collect();

        // Super Admin cannot chat with anyone
        if ($user->hasRole('superadmin')) {
            return $contacts;
        }

        if ($user->hasRole('student')) {
            // Students can chat with their parents, admin, and teachers who teach their purchased classes
            // 1. Parents
            $parents = $user->parents()->get();
            
            // 2. Admins
            $admins = User::role('admin')->get();
            
            // 3. Teachers who teach their purchased classes
            $classIds = $user->enrollments()->where('status', 'active')->pluck('class_id');
            $teachers = User::role('teacher')
                ->whereHas('teachingClasses', function ($query) use ($classIds) {
                    $query->whereIn('id', $classIds);
                })
                ->get();
            
            $contacts = $parents->merge($admins)->merge($teachers);
            
        } elseif ($user->hasRole('teacher')) {
            // Teachers can chat with admin, students they teach, and other teachers
            // 1. Admins
            $admins = User::role('admin')->get();
            
            // 2. Students they teach
            $classIds = $user->teachingClasses()->pluck('class_names.id');
            $students = User::role('student')
                ->whereHas('enrollments', function ($query) use ($classIds) {
                    $query->whereIn('class_id', $classIds)
                          ->where('status', 'active');
                })
                ->get();
            
            // 3. Other teachers
            $teachers = User::role('teacher')
                ->where('id', '!=', $user->id)
                ->get();
            
            $contacts = $admins->merge($students)->merge($teachers);
            
        } elseif ($user->hasRole('parent')) {
            // Parents can only chat with their children and admin
            // 1. Children
            $children = $user->children()->get();
            
            // 2. Admins
            $admins = User::role('admin')->get();
            
            $contacts = $children->merge($admins);
            
        } elseif ($user->hasRole('finance')) {
            // Finance can only chat with admin
            $admins = User::role('admin')->get();
            
            $contacts = $admins;
            
        } elseif ($user->hasRole('admin')) {
            // Admins can chat with anyone except superadmin
            $contacts = User::where('id', '!=', $user->id)
                ->where('is_active', true)
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'superadmin');
                })
                ->get();
        }

        return $contacts->unique('id');
    }

    /**
     * Check if user can chat with another user
     */
    private function canChatWith($user, $otherUserId)
    {
        $otherUser = User::find($otherUserId);
        if (!$otherUser || !$otherUser->is_active) {
            return false;
        }

        // Super Admin cannot chat with anyone
        if ($user->hasRole('superadmin')) {
            return false;
        }

        // Cannot chat with Super Admin
        if ($otherUser->hasRole('superadmin')) {
            return false;
        }

        // Admins can chat with anyone except superadmin
        if ($user->hasRole('admin')) {
            return true;
        }

        // Check based on roles using available contacts
        $availableContacts = $this->getAvailableContacts($user);
        return $availableContacts->contains('id', $otherUserId);
    }
}