<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kurikulum') }}
            </h2>
            <a href="{{ route('admin.curriculums.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Tambah Kurikulum') }}
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

                    <div class="table-wrapper overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200" data-enhance="true" data-searchable="true" data-sortable="true" data-show-no="true">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Kurikulum</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Mata Pelajaran</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Dibuat</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($curriculums as $curriculum)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $curriculum->name }}</div>
                                            @if($curriculum->description)
                                                <div class="text-sm text-gray-500">{{ Str::limit($curriculum->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-gradient-to-r from-purple-400 to-blue-500 flex items-center justify-center">
                                                        <span class="text-xs font-medium text-white">{{ substr($curriculum->course->name, 0, 1) }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $curriculum->course->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full {{ $curriculum->status === 'active' ? 'bg-green-100 text-green-800 ring-1 ring-green-600/20' : 'bg-red-100 text-red-800 ring-1 ring-red-600/20' }}">
                                                <svg class="w-1.5 h-1.5 mr-1.5 {{ $curriculum->status === 'active' ? 'fill-green-500' : 'fill-red-500' }}" viewBox="0 0 6 6">
                                                    <circle cx="3" cy="3" r="3"/>
                                                </svg>
                                                {{ $curriculum->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $curriculum->created_at->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('admin.curriculums.show', $curriculum) }}" class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-md hover:bg-blue-200 transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    Lihat
                                                </a>
                                                <a href="{{ route('admin.curriculums.edit', $curriculum) }}" class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-md hover:bg-yellow-200 transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Edit
                                                </a>
                                                <form action="{{ route('admin.curriculums.toggle-status', $curriculum) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 {{ $curriculum->status === 'active' ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} text-xs font-medium rounded-md transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $curriculum->status === 'active' ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636' : 'M5 13l4 4L19 7' }}"/>
                                                        </svg>
                                                        {{ $curriculum->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.curriculums.destroy', $curriculum) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kurikulum ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-md hover:bg-red-200 transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <p class="text-sm font-medium">Tidak ada data kurikulum</p>
                                                <p class="text-xs">Mulai dengan menambahkan kurikulum baru</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $curriculums->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>