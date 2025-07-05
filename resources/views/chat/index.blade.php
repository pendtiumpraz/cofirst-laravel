@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="h-full flex">
    <!-- Conversations List -->
    <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Messages</h2>
                <button onclick="showNewChatModal()" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Search -->
            <div class="relative">
                <input type="text" 
                       placeholder="Search conversations..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       onkeyup="searchConversations(this.value)">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
        
        <!-- Conversations List -->
        <div class="flex-1 overflow-y-auto" id="conversations-list">
            @forelse($conversations as $conversation)
                @php
                    $otherUser = $conversation->users->first();
                    $displayName = $conversation->getDisplayName(auth()->id());
                    $unreadCount = $conversation->pivot->unread_count;
                @endphp
                <a href="{{ route('chat.show', $conversation) }}" 
                   class="block px-4 py-3 hover:bg-gray-50 transition {{ $unreadCount > 0 ? 'bg-blue-50' : '' }}">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            @if($otherUser && $otherUser->profile_photo_path)
                                <img src="{{ $otherUser->profile_photo_url }}" alt="{{ $otherUser->name }}" 
                                     class="w-12 h-12 rounded-full object-cover">
                            @else
                                <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-gray-600 font-medium">
                                        {{ substr($displayName, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $displayName }}
                                </p>
                                @if($conversation->latestMessage)
                                    <p class="text-xs text-gray-500">
                                        {{ $conversation->latestMessage->created_at->diffForHumans() }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-gray-600 truncate">
                                    @if($conversation->latestMessage)
                                        @if($conversation->latestMessage->sender_id === auth()->id())
                                            You: 
                                        @endif
                                        {{ $conversation->latestMessage->getFormattedContent() }}
                                    @else
                                        No messages yet
                                    @endif
                                </p>
                                @if($unreadCount > 0)
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-blue-600 rounded-full">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-4 text-center text-gray-500">
                    <p>No conversations yet</p>
                    <button onclick="showNewChatModal()" class="mt-2 text-blue-600 hover:text-blue-800">
                        Start a new conversation
                    </button>
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Welcome Message -->
    <div class="flex-1 flex items-center justify-center bg-gray-50">
        <div class="text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Select a conversation</h3>
            <p class="text-gray-600">Choose a conversation from the list or start a new one</p>
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div id="newChatModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Start New Conversation</h3>
            
            <div class="mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select a contact</label>
                <select id="contactSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Choose a contact...</option>
                    @foreach($contacts as $contact)
                        <option value="{{ $contact->id }}">
                            {{ $contact->name }} ({{ ucfirst($contact->roles->first()->name) }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="mt-4 flex justify-end space-x-3">
                <button onclick="hideNewChatModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Cancel
                </button>
                <button onclick="startNewChat()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Start Chat
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showNewChatModal() {
    document.getElementById('newChatModal').classList.remove('hidden');
}

function hideNewChatModal() {
    document.getElementById('newChatModal').classList.add('hidden');
    document.getElementById('contactSelect').value = '';
}

function startNewChat() {
    const userId = document.getElementById('contactSelect').value;
    if (!userId) {
        alert('Please select a contact');
        return;
    }
    
    fetch('{{ route("chat.start") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.redirect_url) {
            window.location.href = data.redirect_url;
        }
    })
    .catch(error => {
        alert('Error starting conversation');
        console.error(error);
    });
}

function searchConversations(query) {
    // Implement search functionality
    // This would filter the conversations list based on the query
}
</script>

<style>
.h-full {
    height: calc(100vh - 200px); /* Adjust based on your header/footer height */
}
</style>
@endsection