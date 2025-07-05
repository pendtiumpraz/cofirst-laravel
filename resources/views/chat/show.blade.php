@extends('layouts.app')

@section('title', 'Chat - ' . $conversation->getDisplayName(auth()->id()))

@section('content')
<div class="h-full flex flex-col bg-white rounded-lg shadow">
    <!-- Chat Header -->
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('chat.index') }}" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $conversation->getDisplayName(auth()->id()) }}
                </h2>
                <p class="text-sm text-gray-600">
                    {{ $participants->count() }} participants
                </p>
            </div>
        </div>
        
        <div class="flex items-center space-x-2">
            <button onclick="showParticipants()" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Messages Container -->
    <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4">
        @foreach($messages->reverse() as $message)
            @include('chat.partials.message', ['message' => $message])
        @endforeach
    </div>
    
    <!-- Message Input -->
    <div class="border-t border-gray-200 px-6 py-4">
        <form id="message-form" class="flex items-end space-x-4">
            @csrf
            <div class="flex-1">
                <textarea 
                    id="message-input"
                    name="content"
                    rows="1"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Type a message..."
                    onkeypress="handleKeyPress(event)"></textarea>
            </div>
            
            <div class="flex items-center space-x-2">
                <label for="file-input" class="cursor-pointer text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                    <input type="file" id="file-input" name="attachments[]" multiple class="hidden" onchange="handleFileSelect(this)">
                </label>
                
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </div>
        </form>
        
        <!-- File Preview -->
        <div id="file-preview" class="hidden mt-2 flex flex-wrap gap-2"></div>
    </div>
</div>

<!-- Participants Modal -->
<div id="participantsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Participants</h3>
            
            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($participants as $participant)
                    <div class="flex items-center space-x-3 p-2">
                        @if($participant->profile_photo_path)
                            <img src="{{ $participant->profile_photo_url }}" alt="{{ $participant->name }}" 
                                 class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-gray-600 font-medium">
                                    {{ substr($participant->name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <p class="font-medium">{{ $participant->name }}</p>
                            <p class="text-sm text-gray-600">{{ ucfirst($participant->roles->first()->name) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-4">
                <button onclick="hideParticipants()" 
                        class="w-full px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const conversationId = {{ $conversation->id }};
const currentUserId = {{ auth()->id() }};
let selectedFiles = [];

// Initialize Echo for real-time messaging
if (typeof Echo !== 'undefined') {
    Echo.private(`conversation.${conversationId}`)
        .listen('MessageSent', (e) => {
            if (e.message.sender_id !== currentUserId) {
                appendMessage(e.message);
                markMessageAsRead(e.message.id);
            }
        });
}

// Handle form submission
document.getElementById('message-form').addEventListener('submit', function(e) {
    e.preventDefault();
    sendMessage();
});

function sendMessage() {
    const content = document.getElementById('message-input').value.trim();
    const files = document.getElementById('file-input').files;
    
    if (!content && files.length === 0) return;
    
    const formData = new FormData();
    formData.append('content', content);
    
    if (files.length > 0) {
        for (let i = 0; i < files.length; i++) {
            formData.append('attachments[]', files[i]);
        }
    }
    
    fetch(`{{ route('chat.send', '') }}/${conversationId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.html) {
            appendMessageHtml(data.html);
            document.getElementById('message-input').value = '';
            document.getElementById('file-input').value = '';
            clearFilePreview();
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Failed to send message');
    });
}

function appendMessage(message) {
    const container = document.getElementById('messages-container');
    const messageHtml = createMessageHtml(message);
    container.insertAdjacentHTML('beforeend', messageHtml);
    container.scrollTop = container.scrollHeight;
}

function appendMessageHtml(html) {
    const container = document.getElementById('messages-container');
    container.insertAdjacentHTML('beforeend', html);
    container.scrollTop = container.scrollHeight;
}

function createMessageHtml(message) {
    const isOwn = message.sender_id === currentUserId;
    const alignClass = isOwn ? 'ml-auto' : 'mr-auto';
    const bgClass = isOwn ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-900';
    
    return `
        <div class="flex ${isOwn ? 'justify-end' : 'justify-start'}">
            <div class="${alignClass} max-w-xs lg:max-w-md">
                <div class="${bgClass} rounded-lg px-4 py-2">
                    <p class="text-sm">${message.content}</p>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    ${message.sender.name} • ${new Date(message.created_at).toLocaleTimeString()}
                </p>
            </div>
        </div>
    `;
}

function handleKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}

function handleFileSelect(input) {
    const files = input.files;
    if (files.length > 0) {
        showFilePreview(files);
    }
}

function showFilePreview(files) {
    const preview = document.getElementById('file-preview');
    preview.innerHTML = '';
    preview.classList.remove('hidden');
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const div = document.createElement('div');
        div.className = 'bg-gray-100 rounded px-3 py-1 text-sm flex items-center space-x-2';
        div.innerHTML = `
            <span>${file.name}</span>
            <button type="button" onclick="removeFile(${i})" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        preview.appendChild(div);
    }
}

function clearFilePreview() {
    document.getElementById('file-preview').innerHTML = '';
    document.getElementById('file-preview').classList.add('hidden');
}

function markMessageAsRead(messageId) {
    fetch(`{{ route('chat.mark-read', '') }}/${conversationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ message_ids: [messageId] })
    });
}

function showParticipants() {
    document.getElementById('participantsModal').classList.remove('hidden');
}

function hideParticipants() {
    document.getElementById('participantsModal').classList.add('hidden');
}

// Auto-scroll to bottom on load
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('messages-container');
    container.scrollTop = container.scrollHeight;
});
</script>

<style>
.h-full {
    height: calc(100vh - 200px);
}
</style>
@endsection