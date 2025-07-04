<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $curriculum->name }}
            </h2>
            <a href="{{ route('teacher.curriculums.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Curriculum Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kurikulum</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Nama Kurikulum</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $curriculum->name }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $curriculum->course->name }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $curriculum->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($curriculum->status) }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Dibuat</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $curriculum->created_at->format('d M Y H:i') }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Terakhir Diperbarui</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $curriculum->updated_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($curriculum->description)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-900">{{ $curriculum->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Syllabuses -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Silabus</h3>
                        <a href="{{ route('teacher.curriculums.materials', $curriculum) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Lihat Semua Materi
                        </a>
                    </div>
                    
                    @if($curriculum->syllabuses->count() > 0)
                        <div class="space-y-4">
                            @foreach($curriculum->syllabuses as $syllabus)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900 mb-2">{{ $syllabus->name }}</h4>
                                            @if($syllabus->description)
                                                <p class="text-sm text-gray-600 mb-3">{{ $syllabus->description }}</p>
                                            @endif
                                            
                                            <div class="flex items-center text-xs text-gray-500 space-x-4">
                                                <span class="flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    {{ $syllabus->materials_count ?? $syllabus->materials->count() }} Materi
                                                </span>
                                                <span class="flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    {{ $syllabus->created_at->format('d M Y') }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex space-x-2 ml-4">
                                            <a href="{{ route('teacher.syllabuses.show', $syllabus) }}" 
                                               class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                                Lihat Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada silabus</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Belum ada silabus yang tersedia untuk kurikulum ini.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>