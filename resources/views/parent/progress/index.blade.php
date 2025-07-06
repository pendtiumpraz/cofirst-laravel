<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Children Progress') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Your Children's Progress</h3>
                        <p class="mt-1 text-sm text-gray-600">Track your children's academic progress across all classes.</p>
                    </div>

                    @if($children->isEmpty())
                        <div class="text-center py-8">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No children found</h3>
                            <p class="mt-1 text-sm text-gray-500">You don't have any children enrolled in classes yet.</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($children as $child)
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-600 font-medium">{{ strtoupper(substr($child->name, 0, 2)) }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <h4 class="text-lg font-medium text-gray-900">{{ $child->name }}</h4>
                                                <p class="text-sm text-gray-500">{{ $child->email }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('parent.progress.child', $child->id) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                                            View Details
                                        </a>
                                    </div>

                                    @if(isset($progressData[$child->id]) && count($progressData[$child->id]) > 0)
                                        <div class="space-y-4">
                                            @foreach($progressData[$child->id] as $classId => $progress)
                                                <div class="border rounded-lg p-4">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <h5 class="font-medium text-gray-900">{{ $progress['class']->name }}</h5>
                                                        <span class="text-sm text-gray-500">{{ $progress['percentage'] }}% Complete</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress['percentage'] }}%"></div>
                                                    </div>
                                                    <div class="mt-2 text-sm text-gray-600">
                                                        {{ $progress['completed'] }} of {{ $progress['total'] }} meetings completed
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <p class="text-sm text-gray-500">No active enrollments found for this child.</p>
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