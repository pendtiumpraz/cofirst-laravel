<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $class->name }} - Materi Pembelajaran
            </h2>
            <a href="{{ route('student.curriculum.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Class Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Mata Pelajaran</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $class->course->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Guru</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $class->teacher->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Kurikulum</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $class->curriculum->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Curriculum Content -->
            @if($class->curriculum && $class->curriculum->syllabuses->count() > 0)
                <div class="space-y-6">
                    @foreach($class->curriculum->syllabuses as $syllabus)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $syllabus->name }}
                                    </h3>
                                    <span class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                        {{ $syllabus->materials->count() }} Materi
                                    </span>
                                </div>
                                
                                @if($syllabus->description)
                                    <p class="text-sm text-gray-600 mb-4">{{ $syllabus->description }}</p>
                                @endif
                                
                                @if($syllabus->materials->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($syllabus->materials as $material)
                                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <div class="flex items-center space-x-3">
                                                            <!-- Progress Status Icon -->
                                                            @php
                                                                $isCompleted = in_array($material->id, $studentProgress['completed']);
                                                                $isInProgress = in_array($material->id, $studentProgress['in_progress']);
                                                                $isNotStarted = !$isCompleted && !$isInProgress;
                                                            @endphp
                                                            
                                                            @if($isCompleted)
                                                                <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                </div>
                                                                <span class="text-xs font-medium text-green-600">Selesai</span>
                                                            @elseif($isInProgress)
                                                                <div class="flex-shrink-0 w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center">
                                                                    <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                </div>
                                                                <span class="text-xs font-medium text-yellow-600">Sedang Dipelajari</span>
                                                            @else
                                                                <div class="flex-shrink-0 w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                                                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                </div>
                                                                <span class="text-xs font-medium text-gray-500">Belum Dimulai</span>
                                                            @endif
                                                        </div>
                                                        
                                                        <h4 class="text-sm font-medium text-gray-900 mt-2">{{ $material->title }}</h4>
                                                        @if($material->description)
                                                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($material->description, 100) }}</p>
                                                        @endif
                                                        
                                                        <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                                            <span>Tipe: {{ ucfirst($material->type) }}</span>
                                                            @if($material->duration)
                                                                <span>Durasi: {{ $material->duration }} menit</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex items-center space-x-2">
                                                        @if($material->file_path)
                                                            <a href="{{ route('student.materials.download', $material->id) }}" 
                                                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                                Download
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('student.materials.show', $material->id) }}" 
                                                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium">
                                                            Lihat
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 italic">Belum ada materi untuk silabus ini.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum Ada Kurikulum</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Kelas ini belum memiliki kurikulum atau materi pembelajaran.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>