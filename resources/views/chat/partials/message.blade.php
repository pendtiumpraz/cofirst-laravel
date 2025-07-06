@php
    $isOwn = $message->sender_id === auth()->id();
    $isRead = $message->reads->count() > 1; // More than just the sender has read it
@endphp

<div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}" id="message-{{ $message->id }}">
    <div class="{{ $isOwn ? 'ml-auto' : 'mr-auto' }} max-w-xs lg:max-w-md">
        <!-- Reply To -->
        @if($message->replyTo)
        <div class="text-xs text-gray-500 mb-1 px-3 py-1 bg-gray-100 rounded">
            <p class="font-medium">Replying to {{ $message->replyTo->sender->name }}</p>
            <p class="truncate">{{ Str::limit($message->replyTo->content, 50) }}</p>
        </div>
        @endif
        
        <!-- Message Bubble -->
        <div class="{{ $isOwn ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-900' }} rounded-lg px-4 py-2 relative group">
            @if($message->type === 'text')
                <p class="text-sm whitespace-pre-wrap">{{ $message->content }}</p>
            @elseif($message->type === 'file' && $message->attachments)
                @foreach($message->attachments as $attachment)
                    <div class="mb-2">
                        @if(Str::startsWith($attachment['mime_type'], 'image/'))
                            <img src="{{ asset('storage/' . $attachment['path']) }}" 
                                 alt="{{ $attachment['filename'] }}" 
                                 class="max-w-full rounded cursor-pointer"
                                 onclick="window.open('{{ asset('storage/' . $attachment['path']) }}', '_blank')">
                        @else
                            <a href="{{ asset('storage/' . $attachment['path']) }}" 
                               download="{{ $attachment['filename'] }}"
                               class="flex items-center space-x-2 {{ $isOwn ? 'text-white' : 'text-blue-600' }} hover:underline">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                <span class="text-sm">{{ $attachment['filename'] }}</span>
                            </a>
                        @endif
                    </div>
                @endforeach
                @if($message->content)
                    <p class="text-sm mt-2">{{ $message->content }}</p>
                @endif
            @elseif($message->type === 'system')
                <p class="text-sm italic">{{ $message->content }}</p>
            @endif
            
            <!-- Message Actions (visible on hover) -->
            @if($isOwn && $message->canBeEditedBy(auth()->id()))
            <div class="absolute -top-8 right-0 hidden group-hover:flex space-x-1 bg-white rounded shadow-lg p-1">
                <button onclick="editMessage({{ $message->id }})" class="text-gray-600 hover:text-gray-900 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </button>
                <button onclick="deleteMessage({{ $message->id }})" class="text-red-600 hover:text-red-900 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
            @endif
        </div>
        
        <!-- Message Meta -->
        <div class="flex items-center justify-between mt-1 px-2">
            <p class="text-xs text-gray-500">
                @if(!$isOwn)
                    {{ $message->sender->name }} • 
                @endif
                {{ $message->created_at->format('H:i') }}
                @if($message->is_edited)
                    • edited
                @endif
            </p>
            
            @if($isOwn)
                <div class="flex items-center space-x-1">
                    @if($isRead)
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <svg class="w-4 h-4 text-blue-600 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>