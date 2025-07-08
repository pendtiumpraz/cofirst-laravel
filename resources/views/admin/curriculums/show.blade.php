<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Kurikulum') }}: {{ $curriculum->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.curriculums.edit', $curriculum) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('admin.curriculums.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Kembali') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Curriculum Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Informasi Kurikulum</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama Kurikulum</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $curriculum->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $curriculum->description ?? 'Tidak ada deskripsi' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kursus</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $curriculum->course->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <span class="mt-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $curriculum->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($curriculum->status) }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Dibuat</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $curriculum->created_at->format('d M Y H:i') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Diperbarui</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $curriculum->updated_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Statistik</h3>
                            <div class="space-y-3">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ $curriculum->syllabuses ? $curriculum->syllabuses->count() : 0 }}</div>
                                    <div class="text-sm text-blue-600">Total Silabus</div>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">{{ $curriculum->syllabuses ? $curriculum->syllabuses->sum(function($syllabus) { return $syllabus->materials ? $syllabus->materials->count() : 0; }) : 0 }}</div>
                                    <div class="text-sm text-green-600">Total Materi</div>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-purple-600">{{ $curriculum->classes ? $curriculum->classes->count() : 0 }}</div>
                                    <div class="text-sm text-purple-600">Kelas Menggunakan</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Syllabuses Section -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Daftar Silabus</h3>
                            <a href="{{ route('admin.syllabuses.create', ['curriculum_id' => $curriculum->id]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                {{ __('Tambah Silabus') }}
                            </a>
                        </div>
                        
                        @if($curriculum->syllabuses && $curriculum->syllabuses->count() > 0)
                            <div class="shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                                <div class="table-wrapper">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200" data-enhance="true" data-searchable="true" data-sortable="true" data-show-no="true">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pertemuan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materi</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($curriculum->syllabuses->sortBy('meeting_number') as $syllabus)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="h-8 w-8 flex-shrink-0 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full flex items-center justify-center">
                                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M6 2a1 1 0 000 2h8a1 1 0 100-2H6zM5 6a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2V6zM9 11a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z"/>
                                                            </svg>
                                                        </div>
                                                        <div class="ml-3">
                                                            <span class="text-sm font-medium text-gray-900">{{ $syllabus->meeting_number }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $syllabus->title }}</div>
                                                    @if($syllabus->description)
                                                        <div class="text-sm text-gray-500">{{ Str::limit($syllabus->description, 50) }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $syllabus->duration_minutes }} menit</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        {{ $syllabus->materials ? $syllabus->materials->count() : 0 }} materi
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $syllabus->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        <span class="w-2 h-2 mr-1 rounded-full {{ $syllabus->status === 'active' ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                                        {{ ucfirst($syllabus->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex items-center space-x-3">
                                                        <a href="{{ route('admin.syllabuses.show', $syllabus) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                            </svg>
                                                            Lihat
                                                        </a>
                                                        <a href="{{ route('admin.syllabuses.edit', $syllabus) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                                            </svg>
                                                            Edit
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada silabus</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan silabus pertama untuk kurikulum ini.</p>
                                <div class="mt-6">
                                    <a href="{{ route('admin.syllabuses.create', ['curriculum_id' => $curriculum->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        Tambah Silabus
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Classes Using This Curriculum -->
                    @if($curriculum->classes && $curriculum->classes->count() > 0)
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Kelas yang Menggunakan Kurikulum Ini</h3>
                            <div class="table-wrapper">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white border border-gray-200" data-enhance="true" data-searchable="true" data-sortable="true" data-show-no="true">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengajar</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($curriculum->classes as $class)
                                            <tr>
                                                <td class="py-2 px-4 border-b border-gray-200">{{ $class->name }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200">{{ $class->teachers->first()->name ?? 'N/A' }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200">{{ $class->enrollments ? $class->enrollments->count() : 0 }}/{{ $class->max_students }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $class->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ ucfirst($class->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>