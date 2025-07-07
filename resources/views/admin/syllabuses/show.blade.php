<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Silabus') }}: {{ $syllabus->title }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.syllabuses.edit', $syllabus) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('admin.syllabuses.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Kembali') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Syllabus Information -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Informasi Silabus</h3>
                                <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                                    <div>
                                        <span class="font-medium text-gray-600">Judul:</span>
                                        <p class="text-gray-800">{{ $syllabus->title }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Kurikulum:</span>
                                        <p class="text-gray-800">
                                            <a href="{{ route('admin.curriculums.show', $syllabus->curriculum) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $syllabus->curriculum->name }}
                                            </a>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Kursus:</span>
                                        <p class="text-gray-800">{{ $syllabus->curriculum->course->name }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Status:</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $syllabus->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $syllabus->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Urutan:</span>
                                        <p class="text-gray-800">{{ $syllabus->order }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Detail Tambahan</h3>
                                <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                                    @if($syllabus->duration_weeks)
                                    <div>
                                        <span class="font-medium text-gray-600">Durasi (Minggu):</span>
                                        <p class="text-gray-800">{{ $syllabus->duration_weeks }} minggu</p>
                                    </div>
                                    @endif
                                    
                                    @if($syllabus->total_hours)
                                    <div>
                                        <span class="font-medium text-gray-600">Total Jam:</span>
                                        <p class="text-gray-800">{{ $syllabus->total_hours }} jam</p>
                                    </div>
                                    @endif
                                    
                                    <div>
                                        <span class="font-medium text-gray-600">Dibuat:</span>
                                        <p class="text-gray-800">{{ $syllabus->created_at->format('d M Y H:i') }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Diperbarui:</span>
                                        <p class="text-gray-800">{{ $syllabus->updated_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($syllabus->description)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Deskripsi</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $syllabus->description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Learning Objectives -->
                    @if($syllabus->learning_objectives)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Tujuan Pembelajaran</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $syllabus->learning_objectives }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Prerequisites -->
                    @if($syllabus->prerequisites)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Prasyarat</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $syllabus->prerequisites }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Materials -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Materi ({{ $syllabus->materials->count() }})</h3>
                            <a href="{{ route('admin.materials.create', ['syllabus_id' => $syllabus->id]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Tambah Materi
                            </a>
                        </div>
                        
                        @if($syllabus->materials->count() > 0)
                        <div class="shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urutan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($syllabus->materials->sortBy('order') as $material)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 flex-shrink-0 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-medium text-white">{{ $material->order }}</span>
                                                </div>
                                            </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $material->title }}</div>
                                                @if($material->description)
                                                <div class="text-sm text-gray-500">{{ Str::limit($material->description, 50) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($material->type === 'video') bg-blue-100 text-blue-800
                                                    @elseif($material->type === 'document') bg-green-100 text-green-800
                                                    @elseif($material->type === 'quiz') bg-purple-100 text-purple-800
                                                    @elseif($material->type === 'assignment') bg-orange-100 text-orange-800
                                                @elseif($material->type === 'file') bg-indigo-100 text-indigo-800
                                                @elseif($material->type === 'link') bg-teal-100 text-teal-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    @if($material->type === 'video')
                                                        <path d="M2 6a2 2 0 012-2h6l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                                    @elseif($material->type === 'document' || $material->type === 'file')
                                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 00-2 2v6a2 2 0 002 2h2.586l-1.293-1.293a1 1 0 111.414-1.414L10 14.586V7a2 2 0 012-2h2a2 2 0 012 2v7.414l2.293-2.293a1 1 0 111.414 1.414L15.414 18H14a2 2 0 01-2-2v-2H6a2 2 0 01-2-2V5z"/>
                                                    @elseif($material->type === 'quiz')
                                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    @elseif($material->type === 'assignment')
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                                    @elseif($material->type === 'link')
                                                        <path d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5z"/>
                                                    @else
                                                        <circle cx="10" cy="10" r="8"/>
                                                    @endif
                                                </svg>
                                                    {{ ucfirst($material->type) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $material->duration ? $material->duration . ' menit' : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $material->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                <span class="w-2 h-2 mr-1 rounded-full {{ $material->status === 'active' ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                                    {{ $material->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                                </span>
                                            </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                <a href="{{ route('admin.materials.show', $material) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                    </svg>
                                                    Lihat
                                                </a>
                                                <a href="{{ route('admin.materials.edit', $material) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                                    </svg>
                                                    Edit
                                                </a>
                                                <form action="{{ route('admin.materials.toggle-status', $material) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white {{ $material->status === 'active' ? 'bg-red-500 hover:bg-red-600 focus:ring-red-500' : 'bg-green-500 hover:bg-green-600 focus:ring-green-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            @if($material->status === 'active')
                                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                                                            @else
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                                            @endif
                                                        </svg>
                                                        {{ $material->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
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
                        <div class="bg-gray-50 p-8 rounded-lg text-center">
                            <p class="text-gray-500 mb-4">Belum ada materi untuk silabus ini.</p>
                            <a href="{{ route('admin.materials.create', ['syllabus_id' => $syllabus->id]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Tambah Materi Pertama
                            </a>
                        </div>
                        @endif
                    </div>

                    <!-- Classes using this Syllabus -->
                    @if($syllabus->curriculum->classNames && $syllabus->curriculum->classNames->count() > 0)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Kelas yang Menggunakan Silabus Ini</h3>
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($syllabus->curriculum->classNames as $class)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $class->name }}</div>
                                                @if($class->description)
                                                <div class="text-sm text-gray-500">{{ Str::limit($class->description, 50) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($class->type === 'private_home_call' || $class->type === 'private_office_1on1' || $class->type === 'private_online_1on1') bg-purple-100 text-purple-800
                                                    @elseif($class->type === 'group_class_3_5_kids') bg-blue-100 text-blue-800
                                                    @elseif($class->type === 'public_school_extracurricular') bg-green-100 text-green-800
                                                    @elseif($class->type === 'offline_seminar' || $class->type === 'online_webinar') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    @if($class->type === 'private_home_call')
                                                        Private - Home Call
                                                    @elseif($class->type === 'private_office_1on1')
                                                        Private - Office 1-on-1
                                                    @elseif($class->type === 'private_online_1on1')
                                                        Private - Online 1-on-1
                                                    @elseif($class->type === 'group_class_3_5_kids')
                                                        Group Class (3-5 Kids)
                                                    @elseif($class->type === 'public_school_extracurricular')
                                                        Public School Extracurricular
                                                    @elseif($class->type === 'offline_seminar')
                                                        Offline Seminar
                                                    @elseif($class->type === 'online_webinar')
                                                        Online Webinar
                                                    @else
                                                        {{ ucfirst(str_replace('_', ' ', $class->type)) }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                Rp {{ number_format($class->price, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $class->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $class->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $class->enrollments->count() }} siswa
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    </table>
                            </div>
                        </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>