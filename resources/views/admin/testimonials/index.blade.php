@extends('layouts.app')

@section('title', 'Manage Testimonials')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Testimonials</h1>
            <p class="mt-1 text-sm text-gray-600">Review and approve parent testimonials</p>
        </div>
        
        <!-- Stats -->
        <div class="flex space-x-4">
            <div class="bg-blue-50 px-4 py-2 rounded-lg">
                <div class="text-sm text-blue-600">Total</div>
                <div class="text-2xl font-bold text-blue-900">{{ $testimonials->total() }}</div>
            </div>
            <div class="bg-green-50 px-4 py-2 rounded-lg">
                <div class="text-sm text-green-600">Active</div>
                <div class="text-2xl font-bold text-green-900">{{ $testimonials->where('is_active', true)->count() }}</div>
            </div>
            <div class="bg-yellow-50 px-4 py-2 rounded-lg">
                <div class="text-sm text-yellow-600">Pending</div>
                <div class="text-2xl font-bold text-yellow-900">{{ $testimonials->where('is_active', false)->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Testimonials Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($testimonials as $testimonial)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $testimonial->title }}</h3>
                        <div class="flex items-center mt-1 space-x-2">
                            <span class="text-sm text-gray-600">by {{ $testimonial->user->name }}</span>
                            <span class="text-gray-300">•</span>
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="ml-1 text-sm text-gray-600">({{ $testimonial->rating }}/5)</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Badges -->
                    <div class="flex flex-col items-end space-y-1">
                        @if($testimonial->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Pending
                            </span>
                        @endif
                        
                        @if($testimonial->is_featured)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                ⭐ Featured
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Child Info -->
                @if($testimonial->child_name || $testimonial->child_class)
                    <div class="bg-gray-50 rounded-lg p-3 mb-4">
                        <div class="text-sm text-gray-600">
                            @if($testimonial->child_name)
                                <div><strong>Child:</strong> {{ $testimonial->child_name }}</div>
                            @endif
                            @if($testimonial->child_class)
                                <div><strong>Class:</strong> {{ $testimonial->child_class }}</div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Content -->
                <div class="mb-4">
                    <p class="text-gray-700 leading-relaxed">{{ Str::limit($testimonial->content, 200) }}</p>
                    @if(strlen($testimonial->content) > 200)
                        <button onclick="toggleContent({{ $testimonial->id }})" class="text-blue-600 hover:text-blue-800 text-sm mt-1">
                            Read more...
                        </button>
                        <div id="full-content-{{ $testimonial->id }}" class="hidden mt-2">
                            <p class="text-gray-700 leading-relaxed">{{ $testimonial->content }}</p>
                        </div>
                    @endif
                </div>

                <!-- Meta -->
                <div class="text-xs text-gray-500 mb-4">
                    Submitted on {{ $testimonial->created_at->format('M j, Y \a\t g:i A') }}
                    @if($testimonial->updated_at != $testimonial->created_at)
                        • Updated {{ $testimonial->updated_at->diffForHumans() }}
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <div class="flex space-x-2">
                        <!-- Toggle Status -->
                        <form method="POST" action="{{ route('admin.testimonials.toggle-status', $testimonial) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="text-sm px-3 py-1 rounded-md {{ $testimonial->is_active ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} transition-colors">
                                {{ $testimonial->is_active ? 'Hide' : 'Approve' }}
                            </button>
                        </form>
                        
                        <!-- Toggle Featured -->
                        @if($testimonial->is_active)
                            <form method="POST" action="{{ route('admin.testimonials.toggle-featured', $testimonial) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="text-sm px-3 py-1 rounded-md {{ $testimonial->is_featured ? 'bg-purple-100 text-purple-700 hover:bg-purple-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                                    {{ $testimonial->is_featured ? 'Unfeature' : 'Feature' }}
                                </button>
                            </form>
                        @endif
                    </div>
                    
                    <!-- View/Delete -->
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.testimonials.show', $testimonial) }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm">
                            View
                        </a>
                        
                        <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" 
                              class="inline" 
                              onsubmit="return confirm('Are you sure you want to delete this testimonial?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No testimonials</h3>
                <p class="mt-1 text-sm text-gray-500">No testimonials have been submitted yet.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $testimonials->links() }}
    </div>
</div>

<script>
function toggleContent(id) {
    const fullContent = document.getElementById('full-content-' + id);
    fullContent.classList.toggle('hidden');
}
</script>
@endsection 