<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Classes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">My Classes</h3>
                    @if ($classes->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($classes as $class)
                                <div class="bg-white rounded-lg shadow-md p-6">
                                    <h4 class="text-md font-semibold text-gray-800">{{ $class->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $class->course->name }}</p>
                                    <p class="text-sm text-gray-500 mt-2">Enrolled Students: {{ $class->enrollments_count }}</p>
                                    <div class="mt-4">
                                        <a href="{{ route('teacher.class-detail', $class) }}" class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p>You are not assigned to any active classes.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>