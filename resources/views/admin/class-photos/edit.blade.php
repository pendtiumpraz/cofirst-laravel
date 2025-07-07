@php
use Illuminate\Support\Facades\Storage;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Class Photo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.class-photos.update', $classPhoto) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Current Photo -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Current Photo</h3>
                                @if($classPhoto->photo_path)
                                    <img src="/storage/{{ $classPhoto->photo_path }}" 
                                         alt="{{ $classPhoto->title }}" 
                                         class="w-full rounded-lg shadow-lg mb-4">
                                @else
                                    <div class="bg-gray-100 rounded-lg flex items-center justify-center h-64">
                                        <p class="text-gray-500">No photo available</p>
                                    </div>
                                @endif
                                
                                <!-- Upload New Photo -->
                                <div class="mt-4">
                                    <label for="photo" class="block text-sm font-medium text-gray-700">
                                        Replace Photo (optional)
                                    </label>
                                    <input type="file" 
                                           name="photo" 
                                           id="photo" 
                                           accept="image/*"
                                           class="mt-1 block w-full text-sm text-gray-500
                                                  file:mr-4 file:py-2 file:px-4
                                                  file:rounded-full file:border-0
                                                  file:text-sm file:font-semibold
                                                  file:bg-blue-50 file:text-blue-700
                                                  hover:file:bg-blue-100">
                                    @error('photo')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Photo Details -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Photo Details</h3>
                                
                                <!-- Title -->
                                <div class="mb-4">
                                    <label for="title" class="block text-sm font-medium text-gray-700">
                                        Title <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="title" 
                                           id="title" 
                                           value="{{ old('title', $classPhoto->title) }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    @error('title')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="description" class="block text-sm font-medium text-gray-700">
                                        Description
                                    </label>
                                    <textarea name="description" 
                                              id="description" 
                                              rows="3"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description', $classPhoto->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Date Taken -->
                                <div class="mb-4">
                                    <label for="date_taken" class="block text-sm font-medium text-gray-700">
                                        Date Taken <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" 
                                           name="date_taken" 
                                           id="date_taken" 
                                           value="{{ old('date_taken', $classPhoto->date_taken->format('Y-m-d')) }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    @error('date_taken')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Read-only fields -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Class</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $classPhoto->class->name }}</p>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Teacher</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $classPhoto->teacher->user->name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 flex items-center justify-end space-x-3">
                            <a href="{{ route('admin.class-photos.show', $classPhoto) }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Update Photo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>