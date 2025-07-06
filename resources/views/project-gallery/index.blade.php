<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Project Gallery') }}
            </h2>
            @if(auth()->user()->hasRole('student'))
            <a href="{{ route('project-gallery.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Upload Project') }}
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Gallery Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @forelse($galleries as $gallery)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                            <a href="{{ route('project-gallery.show', $gallery) }}">
                                <img 
                                    src="{{ asset('storage/' . ($gallery->thumbnail_path ?? $gallery->photo_path)) }}" 
                                    alt="{{ $gallery->title }}"
                                    class="w-full h-48 object-cover"
                                >
                            </a>
                            <div class="p-4">
                                <h3 class="font-semibold text-lg mb-2">
                                    <a href="{{ route('project-gallery.show', $gallery) }}" class="hover:text-blue-600">
                                        {{ $gallery->title }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ Str::limit($gallery->description, 100) }}
                                </p>
                                <div class="flex justify-between items-center text-xs text-gray-500">
                                    <span>{{ $gallery->student->name }}</span>
                                    <span>{{ $gallery->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    <span>{{ $gallery->class->name }}</span>
                                    @if($gallery->is_featured)
                                    <span class="ml-2 bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Featured</span>
                                    @endif
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    <span>{{ $gallery->views }} views</span>
                                </div>
                                
                                @if(auth()->id() === $gallery->student_id || auth()->user()->hasRole(['admin', 'teacher']))
                                <div class="mt-3 flex gap-2">
                                    <a href="{{ route('project-gallery.edit', $gallery) }}" class="text-blue-600 hover:underline text-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('project-gallery.destroy', $gallery) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full text-center py-12">
                            <p class="text-gray-500">No projects uploaded yet.</p>
                            @if(auth()->user()->hasRole('student'))
                            <a href="{{ route('project-gallery.create') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Upload Your First Project
                            </a>
                            @endif
                        </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $galleries->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>