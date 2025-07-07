@php
use Illuminate\Support\Facades\Storage;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Class Photo Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Photo Display -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Photo</h3>
                            @if($classPhoto->photo_path)
                                @php
                                    $imagePath = storage_path('app/public/' . $classPhoto->photo_path);
                                    $imageExists = file_exists($imagePath);
                                @endphp
                                
                                @if($imageExists)
                                    <img src="/storage/{{ $classPhoto->photo_path }}" 
                                         alt="{{ $classPhoto->title }}" 
                                         class="w-full rounded-lg shadow-lg"
                                         onerror="this.onerror=null; this.src='/images/{{ $classPhoto->photo_path }}';">
                                @else
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <p class="text-red-600">Image file not found on server</p>
                                    </div>
                                @endif
                                
                                <!-- Debug info -->
                                <div class="mt-2 text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                    <p><strong>Photo Path:</strong> {{ $classPhoto->photo_path }}</p>
                                    <p><strong>Full Path:</strong> {{ $imagePath ?? 'N/A' }}</p>
                                    <p><strong>File Exists:</strong> {{ $imageExists ? 'Yes' : 'No' }}</p>
                                    <p><strong>Public URL:</strong> /storage/{{ $classPhoto->photo_path }}</p>
                                </div>
                            @else
                                <div class="bg-gray-100 rounded-lg flex items-center justify-center h-64">
                                    <p class="text-gray-500">No photo available</p>
                                </div>
                            @endif
                        </div>

                        <!-- Photo Details -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Details</h3>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Title</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $classPhoto->title }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Class</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $classPhoto->class->name }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Teacher</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $classPhoto->teacher->user->name }}</dd>
                                </div>

                                @if($classPhoto->description)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $classPhoto->description }}</dd>
                                </div>
                                @endif

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Date Taken</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $classPhoto->date_taken->format('F j, Y') }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Uploaded At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $classPhoto->created_at->format('F j, Y g:i A') }}</dd>
                                </div>
                            </dl>

                            <div class="mt-6 flex space-x-3">
                                <a href="{{ route('admin.class-photos.edit', $classPhoto) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Edit
                                </a>
                                <a href="{{ route('admin.class-photos.index') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 