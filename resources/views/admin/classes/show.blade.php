<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Class Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-semibold text-gray-800">{{ $class->name }}</h3>
                        <a href="{{ route('admin.classes.index') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                            &larr; Back to Classes
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Class Information -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4 class="text-lg font-bold text-gray-700 mb-4">Class Information</h4>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Course</dt>
                                    <dd class="mt-1 text-md text-gray-900">{{ $class->course->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                                    <dd class="mt-1 text-md text-gray-900">{{ ucfirst($class->type) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Teacher</dt>
                                    <dd class="mt-1 text-md text-gray-900">{{ $class->teacher->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Capacity</dt>
                                    <dd class="mt-1 text-md text-gray-900">{{ $class->enrollments->count() }} / {{ $class->max_students }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $class->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($class->status) }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Enrolled Students -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4 class="text-lg font-bold text-gray-700 mb-4">Enrolled Students</h4>
                            @if($class->enrollments->count() > 0)
                                <ul class="divide-y divide-gray-200">
                                    @foreach($class->enrollments as $enrollment)
                                        <li class="py-3 flex justify-between items-center">
                                            <span class="text-md text-gray-800">{{ $enrollment->student->name }}</span>
                                            <span class="text-sm text-gray-500">{{ $enrollment->student->email }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500">No students are currently enrolled in this class.</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 border-t pt-6 flex justify-end">
                         <a href="{{ route('admin.classes.edit', $class) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Edit Class
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
