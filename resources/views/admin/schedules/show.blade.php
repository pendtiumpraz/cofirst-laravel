<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Schedule Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Class Name:</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $schedule->className->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $schedule->className->course->name ?? '' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Class Status:</p>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $schedule->className->status_badge_color ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $schedule->className->status_label ?? 'N/A' }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Day of Week:</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $schedule->day_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Time:</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $schedule->time_range }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Room:</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $schedule->room ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Teacher:</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $schedule->teacherAssignment->teacher->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Student:</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $schedule->enrollment->student->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Active:</p>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $schedule->is_active ? 'Yes' : 'No' }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Can Show in Calendar:</p>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $schedule->canShowInCalendar() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $schedule->canShowInCalendar() ? 'Yes' : 'No' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-6">
                        @if($schedule->className->canShowInCalendar())
                            <a href="{{ route('admin.schedules.edit', $schedule->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Edit Schedule
                            </a>
                        @else
                            <span class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                                Edit Disabled (Class Completed)
                            </span>
                        @endif
                        <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-300 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 ml-2">
                            Back to Schedules
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
