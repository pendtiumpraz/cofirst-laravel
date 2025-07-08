<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Materials') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">My Materials</h1>
                        <p class="text-gray-600">Access your course materials and track your progress</p>
                    </div>
                </div>

                <!-- Enrolled Classes -->
                <div class="space-y-6">
                    @forelse($enrollments as $enrollment)
                        @php
                            $class = $enrollment->class;
                            $progress = $progressData[$class->id] ?? ['total' => 0, 'completed' => 0, 'percentage' => 0];
                        @endphp
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div class="h-12 w-12 bg-gradient-to-r from-blue-400 to-purple-500 rounded-lg flex items-center justify-center">
                                            <span class="text-white font-medium">{{ substr($class->name, 0, 2) }}</span>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $class->name }}</h3>
                                            <p class="text-sm text-gray-600">{{ $class->course->name }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-6 text-sm text-gray-600 mb-4">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            {{ $class->teachers->first()->name ?? 'N/A' }}
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            {{ $progress['total'] }} meetings
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mb-4">
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">Progress</span>
                                            <span class="text-gray-900 font-medium">{{ $progress['completed'] }}/{{ $progress['total'] }} ({{ $progress['percentage'] }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progress['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="ml-6">
                                    <a href="{{ route('student.materials.by-class', $class->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        View Materials
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Materials Preview -->
                            @if($class->curriculum && $class->curriculum->syllabuses)
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Materials</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($class->curriculum->syllabuses->take(3) as $syllabus)
                                            @foreach($syllabus->materials->take(2) as $material)
                                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                                    <div class="flex-shrink-0 mr-3">
                                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                                @if($material->type === 'video')
                                                                    <path d="M2 6a2 2 0 012-2h6l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                                                @elseif($material->type === 'document' || $material->type === 'file')
                                                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 00-2 2v6a2 2 0 002 2h2.586l-1.293-1.293a1 1 0 111.414-1.414L10 14.586V7a2 2 0 012-2h2a2 2 0 012 2v7.414l2.293-2.293a1 1 0 111.414 1.414L15.414 18H14a2 2 0 01-2-2v-2H6a2 2 0 01-2-2V5z"/>
                                                                @else
                                                                    <circle cx="10" cy="10" r="8"/>
                                                                @endif
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $material->title }}</p>
                                                        <p class="text-xs text-gray-500">{{ ucfirst($material->type) }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No materials available</h3>
                            <p class="mt-1 text-sm text-gray-500">You are not enrolled in any classes yet.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Progress Summary -->
                @if($enrollments->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Active Classes</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $enrollments->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Avg. Progress</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ number_format(collect($progressData)->avg('percentage'), 1) }}%</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Materials</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ $enrollments->sum(function($enrollment) { 
                                            return $enrollment->class->curriculum->syllabuses->sum(function($syllabus) { 
                                                return $syllabus->materials->count(); 
                                            }); 
                                        }) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout> 