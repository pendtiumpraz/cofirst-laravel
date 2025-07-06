@extends('layouts.app')

@section('title', $testimonial ? 'Edit Testimonial' : 'Submit Testimonial')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow-lg rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $testimonial ? 'Edit Your Testimonial' : 'Submit Your Testimonial' }}
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                Share your experience with CodingFirst and help other parents learn about our programs.
            </p>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('parent.testimonials.store') }}" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        Testimonial Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           value="{{ old('title', $testimonial->title ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('title') border-red-300 @enderror"
                           placeholder="e.g., Amazing coding experience for my child!"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Child Name -->
                <div>
                    <label for="child_name" class="block text-sm font-medium text-gray-700">
                        Child's Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="child_name" 
                           id="child_name" 
                           value="{{ old('child_name', $testimonial->child_name ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('child_name') border-red-300 @enderror"
                           placeholder="Your child's name"
                           required>
                    @error('child_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Child Class -->
                <div>
                    <label for="child_class" class="block text-sm font-medium text-gray-700">
                        Class/Program <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="child_class" 
                           id="child_class" 
                           value="{{ old('child_class', $testimonial->child_class ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('child_class') border-red-300 @enderror"
                           placeholder="e.g., Python Beginner, Scratch Programming"
                           required>
                    @error('child_class')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rating -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Overall Rating <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-1">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" 
                                       name="rating" 
                                       value="{{ $i }}" 
                                       class="sr-only"
                                       {{ old('rating', $testimonial->rating ?? 5) == $i ? 'checked' : '' }}
                                       required>
                                <svg class="w-8 h-8 text-gray-300 hover:text-yellow-400 transition-colors star-icon {{ old('rating', $testimonial->rating ?? 5) >= $i ? 'text-yellow-400' : '' }}" 
                                     fill="currentColor" 
                                     viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </label>
                        @endfor
                    </div>
                    @error('rating')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content -->
                <div class="md:col-span-2">
                    <label for="content" class="block text-sm font-medium text-gray-700">
                        Your Testimonial <span class="text-red-500">*</span>
                    </label>
                    <textarea name="content" 
                              id="content" 
                              rows="6" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('content') border-red-300 @enderror"
                              placeholder="Please share your experience with CodingFirst. What did you and your child like most about the program? How has it helped your child? (Minimum 50 characters)"
                              required>{{ old('content', $testimonial->content ?? '') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Minimum 50 characters, maximum 1000 characters</p>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status Info -->
            @if($testimonial)
                <div class="mt-6 p-4 rounded-lg {{ $testimonial->is_active ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 {{ $testimonial->is_active ? 'text-green-400' : 'text-yellow-400' }} mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium {{ $testimonial->is_active ? 'text-green-800' : 'text-yellow-800' }}">
                            Status: {{ $testimonial->is_active ? 'Approved and Published' : 'Pending Admin Approval' }}
                        </span>
                    </div>
                    @if($testimonial->is_featured)
                        <p class="mt-1 text-sm text-green-600">⭐ Your testimonial is featured on our website!</p>
                    @endif
                </div>
            @endif

            <!-- Submit Button -->
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <span class="text-red-500">*</span> Required fields
                </div>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                    {{ $testimonial ? 'Update Testimonial' : 'Submit Testimonial' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Star rating functionality
    const stars = document.querySelectorAll('.star-icon');
    const radioInputs = document.querySelectorAll('input[name="rating"]');
    
    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            radioInputs[index].checked = true;
            updateStars(index + 1);
        });
        
        star.addEventListener('mouseover', function() {
            highlightStars(index + 1);
        });
    });
    
    document.querySelector('form').addEventListener('mouseleave', function() {
        const checkedRating = document.querySelector('input[name="rating"]:checked');
        if (checkedRating) {
            updateStars(parseInt(checkedRating.value));
        }
    });
    
    function updateStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }
    
    function highlightStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }
});
</script>
@endsection 