<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Progress for') }} {{ $child->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('parent.progress.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    ← Back to Progress Overview
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <div class="flex items-center">
                            <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-blue-600 font-medium text-lg">{{ strtoupper(substr($child->name, 0, 2)) }}</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ $child->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $child->email }}</p>
                            </div>
                        </div>
                    </div>

                    @if(empty($detailedProgress))
                        <div class="text-center py-8">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No active enrollments</h3>
                            <p class="mt-1 text-sm text-gray-500">This child is not currently enrolled in any classes.</p>
                        </div>
                    @else
                        <div class="space-y-8">
                            @foreach($detailedProgress as $classId => $progress)
                                <div class="border rounded-lg p-6">
                                    <div class="mb-6">
                                        <h4 class="text-lg font-medium text-gray-900">{{ $progress['class']->name }}</h4>
                                        <p class="text-sm text-gray-500">{{ $progress['class']->description ?? 'No description available' }}</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                        <div class="bg-blue-50 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">Completed</p>
                                                    <p class="text-2xl font-bold text-blue-600">{{ $progress['completed_meetings'] }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-yellow-50 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">In Progress</p>
                                                    <p class="text-2xl font-bold text-yellow-600">{{ $progress['in_progress_meetings'] }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">Total Meetings</p>
                                                    <p class="text-2xl font-bold text-gray-600">{{ $progress['total_meetings'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <div class="flex items-center justify-between mb-2">
                                            <h5 class="font-medium text-gray-900">Overall Progress</h5>
                                            <span class="text-sm text-gray-500">
                                                {{ $progress['total_meetings'] > 0 ? round(($progress['completed_meetings'] / $progress['total_meetings']) * 100, 1) : 0 }}% Complete
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress['total_meetings'] > 0 ? round(($progress['completed_meetings'] / $progress['total_meetings']) * 100, 1) : 0 }}%"></div>
                                        </div>
                                    </div>

                                    @if($progress['progress']->count() > 0)
                                        <div class="mb-6">
                                            <h5 class="font-medium text-gray-900 mb-4">Meeting Progress</h5>
                                            <div class="space-y-3">
                                                @foreach($progress['progress'] as $meetingProgress)
                                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                        <div class="flex items-center">
                                                            <div class="h-8 w-8 rounded-full flex items-center justify-center
                                                                {{ $meetingProgress->status === 'completed' ? 'bg-green-100' : ($meetingProgress->status === 'in_progress' ? 'bg-yellow-100' : 'bg-gray-100') }}">
                                                                <span class="text-sm font-medium
                                                                    {{ $meetingProgress->status === 'completed' ? 'text-green-600' : ($meetingProgress->status === 'in_progress' ? 'text-yellow-600' : 'text-gray-600') }}">
                                                                    {{ $meetingProgress->meeting_number }}
                                                                </span>
                                                            </div>
                                                            <div class="ml-3">
                                                                <p class="text-sm font-medium text-gray-900">Meeting {{ $meetingProgress->meeting_number }}</p>
                                                                @if($meetingProgress->syllabus)
                                                                    <p class="text-xs text-gray-500">{{ $meetingProgress->syllabus->topic }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                            {{ $meetingProgress->status === 'completed' ? 'bg-green-100 text-green-800' : ($meetingProgress->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                                            {{ ucfirst(str_replace('_', ' ', $meetingProgress->status)) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($progress['material_access']->count() > 0)
                                        <div>
                                            <h5 class="font-medium text-gray-900 mb-4">Recent Material Access</h5>
                                            <div class="space-y-2">
                                                @foreach($progress['material_access']->take(5) as $access)
                                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                                        <div class="flex items-center">
                                                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                            <span class="text-sm text-gray-900">{{ $access->material->title }}</span>
                                                        </div>
                                                        <span class="text-xs text-gray-500">{{ $access->accessed_at->diffForHumans() }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 