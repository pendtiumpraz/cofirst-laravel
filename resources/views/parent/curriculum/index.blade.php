<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Progress Anak') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($children->count() > 0)
                        @foreach($children as $child)
                            <div class="mb-8 last:mb-0">
                                <div class="border-b border-gray-200 pb-4 mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $child->name }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $child->email }}</p>
                                </div>
                                
                                @if($child->enrollments->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($child->enrollments as $enrollment)
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-300">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h4 class="font-medium text-gray-900">{{ $enrollment->class->name }}</h4>
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
                                                        
                                                        @php
                                                            $totalMaterials = $enrollment->class->curriculum->syllabuses->sum(function($syllabus) {
                                                                return $syllabus->materials->count();
                                                            });
                                                        @endphp
                                                        
                                                        <div class="bg-white rounded p-2 mt-2">
                                                            <p class="text-xs text-gray-500 mb-1">Progress Pembelajaran:</p>
                                                            <div class="flex items-center space-x-2">
                                                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                                                    <div class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                                                                </div>
                                                                <span class="text-xs text-gray-600">0/{{ $totalMaterials }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <a href="{{ route('parent.curriculum.child-progress', [$child->id, $enrollment->class->id]) }}" 
                                                   class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200">
                                                    Lihat Detail Progress
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                                        <div class="mx-auto h-8 w-8 text-gray-400">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                            </svg>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">
                                            {{ $child->name }} belum terdaftar di kelas manapun.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-12">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum Ada Anak Terdaftar</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Anda belum memiliki anak yang terdaftar dalam sistem. Silakan hubungi admin untuk menambahkan anak Anda.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>