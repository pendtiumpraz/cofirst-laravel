<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Available Courses
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-white">
                    <h1 class="text-2xl font-bold mb-2">Available Courses</h1>
                    <p class="text-blue-100">Explore and enroll in courses that interest you.</p>
                </div>
            </div>

            <!-- Courses Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($courses as $course)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                        <div class="p-6">
                            @if($course->thumbnail)
                                <img src="{{ $course->thumbnail }}" alt="{{ $course->name }}" class="w-full h-48 object-cover rounded-lg mb-4">
                            @endif
                            
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $course->name }}</h3>
                            <p class="text-gray-600 text-sm mb-4">{{ $course->description }}</p>
                            
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $course->classes->count() }} classes available
                                </div>
                                
                                @if(in_array($course->id, $enrolledCourseIds))
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                        Enrolled
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">
                                        Available
                                    </span>
                                @endif
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">Level:</span> {{ $course->level ?? 'All Levels' }}
                                </div>
                                
                                @if(!in_array($course->id, $enrolledCourseIds))
                                    <button class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        Enroll Now
                                    </button>
                                @else
                                    <button class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white">
                                        View Classes
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2a1 1 0 01-1 1H6a1 1 0 01-1-1v-2h10z" clip-rule="evenodd"/>
                        </svg>
                        <p class="mt-2">No courses available at the moment</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout> 