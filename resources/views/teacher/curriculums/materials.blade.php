<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Materi - {{ $curriculum->name }}
            </h2>
            <a href="{{ route('teacher.curriculums.show', $curriculum) }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                Kembali ke Kurikulum
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Curriculum Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $curriculum->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $curriculum->course->name }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $curriculum->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($curriculum->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Materials by Syllabus -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Materi Pembelajaran</h3>
                    
                    @if($materials->count() > 0)
                        <div class="space-y-8">
                            @foreach($materials->groupBy('syllabus.name') as $syllabusName => $syllabusMaterials)
                                <div class="border border-gray-200 rounded-lg p-6">
                                    <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        {{ $syllabusName }}
                                    </h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($syllabusMaterials->sortBy('order') as $material)
                                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors duration-200">
                                                <div class="flex items-start justify-between mb-3">
                                                    <div class="flex-1">
                                                        <h5 class="font-medium text-gray-900 text-sm mb-1">
                                                            {{ $material->title }}
                                                        </h5>
                                                        <div class="flex items-center text-xs text-gray-500 mb-2">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                                {{ $material->type === 'file' ? 'bg-blue-100 text-blue-800' : 
                                                                   ($material->type === 'link' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                                                {{ ucfirst($material->type) }}
                                                            </span>
                                                            <span class="ml-2">Urutan: {{ $material->order }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                @if($material->description)
                                                    <p class="text-gray-600 text-xs mb-3 line-clamp-2">
                                                        {{ Str::limit($material->description, 80) }}
                                                    </p>
                                                @endif
                                                
                                                <div class="flex items-center justify-between">
                                                    <div class="text-xs text-gray-500">
                                                        {{ $material->created_at->format('d M Y') }}
                                                    </div>
                                                    
                                                    <div class="flex space-x-2">
                                                        <a href="{{ route('teacher.materials.show', $material) }}" 
                                                           class="inline-flex items-center px-2 py-1 bg-blue-600 border border-transparent rounded text-xs text-white hover:bg-blue-700 transition-colors duration-200">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                            </svg>
                                                            Lihat
                                                        </a>
                                                        
                                                        @if($material->type === 'file' && $material->file_path)
                                                            <a href="{{ route('teacher.materials.download', $material) }}" 
                                                               class="inline-flex items-center px-2 py-1 bg-green-600 border border-transparent rounded text-xs text-white hover:bg-green-700 transition-colors duration-200">
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
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada materi</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Belum ada materi yang tersedia untuk kurikulum ini.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>