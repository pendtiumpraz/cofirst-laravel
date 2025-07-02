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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Class Name:</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $schedule->className->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Schedule Date:</p>
                            <p class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($schedule->schedule_date)->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Schedule Time:</p>
                            <p class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($schedule->schedule_time)->format('H:i') }}</p>
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
                            <p class="mt-1 text-sm text-gray-900">{{ $schedule->is_active ? 'Yes' : 'No' }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('admin.schedules.edit', $schedule->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Edit Schedule
                        </a>
                        <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-300 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 ml-2">
                            Back to Schedules
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
