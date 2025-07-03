<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Materi Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($enrollments->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($enrollments as $enrollment)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $enrollment->class->name }}
                                            </h3>
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                        </div>
                                        
                                        <div class="space-y-2 mb-4">
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Mata Pelajaran:</span> 
                                                {{ $enrollment->class->course->name ?? 'N/A' }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Guru:</span> 
                                                {{ $enrollment->class->teacher->name ?? 'N/A' }}
                                            </p>
                                            @if($enrollment->class->curriculum)
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Kurikulum:</span> 
                                                    {{ $enrollment->class->curriculum->name }}
                                                </p>
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Total Silabus:</span> 
                                                    {{ $enrollment->class->curriculum->syllabuses->count() }} silabus
                                                </p>
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Total Materi:</span> 
                                                    {{ $enrollment->class->curriculum->syllabuses->sum(function($syllabus) { return $syllabus->materials->count(); }) }} materi
                                                </p>
                                            @endif
                                        </div>
                                        
                                        <div class="flex space-x-2">
                                            <a href="{{ route('student.curriculum.show', $enrollment->class->id) }}" 
                                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200">
                                                Lihat Materi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum Ada Kelas</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Anda belum terdaftar di kelas manapun. Silakan hubungi admin untuk mendaftar kelas.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>