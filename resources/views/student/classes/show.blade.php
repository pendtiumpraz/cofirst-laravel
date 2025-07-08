<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $enrollment->className->name }} - Class Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Class Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Class Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Class Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Class Name</dt>
                                    <dd class="text-sm text-gray-900">{{ $enrollment->className->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Course</dt>
                                    <dd class="text-sm text-gray-900">{{ $enrollment->className->course->name ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Teacher</dt>
                                    <dd class="text-sm text-gray-900">{{ $enrollment->className->teachers->first()->name ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ ucfirst($enrollment->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Enrollment Date</dt>
                                    <dd class="text-sm text-gray-900">{{ $enrollment->created_at->format('M d, Y') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Quick Actions -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <a href="{{ route('student.schedules.index') }}" class="w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    View Schedule
                                </a>
                                <a href="{{ route('student.materials.by-class', $enrollment->className->id) }}" class="w-full bg-green-600 text-white text-center py-2 px-4 rounded-md text-sm font-medium hover:bg-green-700 transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    View Materials
                                </a>
                                <a href="{{ route('student.reports.index') }}" class="w-full bg-purple-600 text-white text-center py-2 px-4 rounded-md text-sm font-medium hover:bg-purple-700 transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Class Schedule -->
            @if($schedules->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Class Schedule</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($schedules as $schedule)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-blue-600">
                                    {{ $schedule->day_name ?? 'N/A' }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ $schedule->start_time ?? 'N/A' }} - {{ $schedule->end_time ?? 'N/A' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                Teacher: {{ $schedule->teacherAssignment->teacher->name ?? 'N/A' }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Reports -->
            @if($reports->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Reports</h3>
                        <a href="{{ route('student.reports.index') }}" class="text-sm text-blue-600 hover:text-blue-700">View All</a>
                    </div>
                    <div class="space-y-4">
                        @foreach($reports as $report)
                        <div class="border-l-4 border-blue-500 pl-4 py-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $report->subject ?? 'General Report' }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($report->content, 100) }}</p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        By {{ $report->teacher->name ?? 'Teacher' }} • {{ $report->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <a href="{{ route('student.reports.show', $report->id) }}" class="text-sm text-blue-600 hover:text-blue-700 ml-4">
                                    View Details
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No reports yet</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Your teacher hasn't created any reports for this class yet.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout> 