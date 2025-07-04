<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Materi') }}
            </h2>
            <a href="{{ route('admin.materials.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Tambah Materi') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($materials->count() > 0)
                        <div class="shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Silabus</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurikulum</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urutan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($materials as $material)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 flex-shrink-0 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                                                        <span class="text-xs font-medium text-white">{{ $material->id }}</span>
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
                                                <div class="text-sm font-medium text-gray-900">{{ $material->syllabus->title }}</div>
                                                <div class="text-sm text-gray-500">Pertemuan {{ $material->syllabus->meeting_number }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $material->syllabus->curriculum->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $material->syllabus->curriculum->course->name }}</div>
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
                                                        @elseif($material->type === 'link')
                                                            <path d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5z"/>
                                                        @else
                                                            <circle cx="10" cy="10" r="8"/>
                                                        @endif
                                                    </svg>
                                                {{ ucfirst($material->type) }}
                                            </span>
                                        </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-6 w-6 flex-shrink-0 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                                        <span class="text-xs font-medium text-blue-600">{{ $material->order }}</span>
                                                    </div>
                                                    <span class="text-sm text-gray-900">{{ $material->order }}</span>
                                                </div>
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
                                                @if($material->type === 'file' && $material->file_path)
                                                        <a href="{{ route('admin.materials.download', $material) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"/>
                                                            </svg>
                                                            Download
                                                        </a>
                                                @endif
                                                <form action="{{ route('admin.materials.destroy', $material) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"/>
                                                            </svg>
                                                            Hapus
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
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">Belum ada materi</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan materi pembelajaran pertama.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.materials.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                                    </svg>
                                    Tambah Materi
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($materials->hasPages())
                        <div class="mt-6">
                        {{ $materials->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>