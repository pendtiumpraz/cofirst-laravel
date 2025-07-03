<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Silabus Kelas Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($syllabuses->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($syllabuses as $syllabus)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="p-6">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex-1">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                                    {{ $syllabus->title }}
                                                </h3>
                                                <p class="text-sm text-gray-600 mb-2">
                                                    <span class="font-medium">Kurikulum:</span> {{ $syllabus->curriculum->name }}
                                                </p>
                                                <p class="text-sm text-gray-600 mb-2">
                                                    <span class="font-medium">Mata Pelajaran:</span> {{ $syllabus->curriculum->course->name }}
                                                </p>
                                                <p class="text-sm text-gray-600 mb-4">
                                                    <span class="font-medium">Pertemuan:</span> {{ $syllabus->meeting_number }}
                                                </p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $syllabus->materials_count }} Materi
                                            </span>
                                        </div>
                                        
                                        @if($syllabus->description)
                                            <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                                {{ Str::limit($syllabus->description, 120) }}
                                            </p>
                                        @endif
                                        
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center text-sm text-gray-500">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $syllabus->duration_minutes }} menit
                                            </div>
                                            <a href="{{ route('teacher.syllabuses.show', $syllabus) }}" 
                                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                Lihat Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada silabus</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Belum ada silabus untuk kelas yang Anda ajar.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>