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
                    <h3 class="text-lg font-medium text-gray-900 mb-4">My Enrolled Classes</h3>
                    @if ($enrollments->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($enrollments as $enrollment)
                                <div class="bg-white rounded-lg shadow-md p-6">
                                    <h4 class="text-md font-semibold text-gray-800">{{ $enrollment->class->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $enrollment->class->course->name }}</p>
                                    <p class="text-sm text-gray-500 mt-2">Teacher: {{ $enrollment->class->teacher->name }}</p>
                                    <div class="mt-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ $enrollment->status }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p>You are not enrolled in any classes.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>