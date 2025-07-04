<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Schedule - New') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Header Section -->
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">My Class Schedule</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                View your weekly class schedule and upcoming sessions
                            </p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-500">
                                <span class="font-medium">Total Classes:</span>
                                <span class="ml-1 bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                    {{ $schedules->count() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Calendar Component -->
                    <div class="mb-8">
                        <x-schedule-calendar :schedules="$schedules" />
                    </div>

                    <!-- Upcoming Classes -->
                    @if($upcomingClasses->isNotEmpty())
                    <div class="mb-8">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Upcoming Classes</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($upcomingClasses as $schedule)
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-blue-600">
                                        {{ $schedule->day_name ?? 'N/A' }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ $schedule->start_time ?? 'N/A' }} - {{ $schedule->end_time ?? 'N/A' }}
                                    </span>
                                </div>
                                <h5 class="font-medium text-gray-900 mb-1">
                                    {{ $schedule->className->name ?? 'N/A' }}
                                </h5>
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ $schedule->className->course->name ?? 'N/A' }}
                                </p>
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $schedule->className->teacher->name ?? 'N/A' }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- All Schedules Table -->
                    <div class="bg-white">
                        <div class="mb-4">
                            <h4 class="text-lg font-medium text-gray-900">All My Classes</h4>
                            <p class="text-sm text-gray-600">Complete list of your enrolled classes</p>
                        </div>
                        
                        @if($schedules->isNotEmpty())
                        <div class="shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Day & Time
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Class
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Course
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Teacher
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($schedules as $schedule)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-white">
                                                                {{ substr($schedule->day_name ?? 'N', 0, 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $schedule->day_name ?? 'N/A' }}
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $schedule->start_time ?? 'N/A' }} - {{ $schedule->end_time ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $schedule->className->name ?? 'N/A' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $schedule->className->type ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $schedule->className->course->name ?? 'N/A' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $schedule->className->course->level ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-green-400 to-green-600 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-white">
                                                                {{ substr($schedule->className->teacher->name ?? 'N', 0, 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $schedule->className->teacher->name ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Active
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 11-8 0V9a4 4 0 018 0v6zM7 21h10"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No schedules found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                You haven't enrolled in any classes yet.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('student.classes') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Browse Classes
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 