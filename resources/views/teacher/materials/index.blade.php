<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Materi Kelas Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($materials->count() > 0)
                        <div class="space-y-6">
                            @foreach($materials->groupBy('syllabus.curriculum.course.name') as $courseName => $courseMaterials)
                                <div class="border border-gray-200 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
                                        </svg>
                                        {{ $courseName }}
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $courseMaterials->count() }} Materi
                                        </span>
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($courseMaterials as $material)
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow duration-200">
                                                <div class="flex items-start justify-between mb-3">
                                                    <div class="flex-1">
                                                        <h4 class="text-md font-medium text-gray-900 mb-2">
                                                            {{ $material->title }}
                                                        </h4>
                                                        <p class="text-sm text-gray-600 mb-2">
                                                            <span class="font-medium">Silabus:</span> {{ $material->syllabus->title }}
                                                        </p>
                                                        <p class="text-sm text-gray-600 mb-2">
                                                            <span class="font-medium">Kurikulum:</span> {{ $material->syllabus->curriculum->name }}
                                                        </p>
                                                    </div>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                        @if($material->type === 'video') bg-red-100 text-red-800
                                                        @elseif($material->type === 'document') bg-blue-100 text-blue-800
                                                        @elseif($material->type === 'link') bg-green-100 text-green-800
                                                        @else bg-gray-100 text-gray-800
                                                        @endif">
                                                        {{ ucfirst($material->type) }}
                                                    </span>
                                                </div>
                                                
                                                @if($material->description)
                                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                                        {{ Str::limit($material->description, 80) }}
                                                    </p>
                                                @endif
                                                
                                                <div class="flex items-center justify-between">
                                                    <div class="text-xs text-gray-500">
                                                        Urutan: {{ $material->order }}
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <a href="{{ route('teacher.materials.show', $material) }}" 
                                                           class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs leading-4 font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                            </svg>
                                                            Lihat
                                                        </a>
                                                        @if($material->file_path)
                                                            <a href="{{ route('teacher.materials.download', $material) }}" 
                                                               class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                                </svg>
                                                                Download
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada materi</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Belum ada materi untuk kelas yang Anda ajar.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>