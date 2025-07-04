<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Materi') }}: {{ $material->title }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.materials.edit', $material) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('admin.materials.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Kembali') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Material Information -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Informasi Materi</h3>
                                <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                                    <div>
                                        <span class="font-medium text-gray-600">Judul:</span>
                                        <p class="text-gray-800">{{ $material->title }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Silabus:</span>
                                        <p class="text-gray-800">
                                            <a href="{{ route('admin.syllabuses.show', $material->syllabus) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $material->syllabus->title }}
                                            </a>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Kurikulum:</span>
                                        <p class="text-gray-800">
                                            <a href="{{ route('admin.curriculums.show', $material->syllabus->curriculum) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $material->syllabus->curriculum->name }}
                                            </a>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Kursus:</span>
                                        <p class="text-gray-800">
                                            {{ $material->syllabus->curriculum->course->name }}
                                        </p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Tipe:</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($material->type === 'video') bg-blue-100 text-blue-800
                                            @elseif($material->type === 'document') bg-green-100 text-green-800
                                            @elseif($material->type === 'quiz') bg-purple-100 text-purple-800
                                            @elseif($material->type === 'assignment') bg-orange-100 text-orange-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($material->type) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Status:</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $material->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $material->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Urutan:</span>
                                        <p class="text-gray-800">{{ $material->order }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Detail Tambahan</h3>
                                <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                                    @if($material->duration)
                                    <div>
                                        <span class="font-medium text-gray-600">Durasi:</span>
                                        <p class="text-gray-800">{{ $material->duration }} menit</p>
                                    </div>
                                    @endif
                                    
                                    @if($material->file_path)
                                    <div>
                                        <span class="font-medium text-gray-600">File:</span>
                                        <p class="text-gray-800">
                                            <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                Lihat File
                                            </a>
                                        </p>
                                    </div>
                                    @endif
                                    
                                    @if($material->url)
                                    <div>
                                        <span class="font-medium text-gray-600">URL:</span>
                                        <p class="text-gray-800">
                                            <a href="{{ $material->url }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                {{ $material->url }}
                                            </a>
                                        </p>
                                    </div>
                                    @endif
                                    
                                    <div>
                                        <span class="font-medium text-gray-600">Dibuat:</span>
                                        <p class="text-gray-800">{{ $material->created_at->format('d M Y H:i') }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Diperbarui:</span>
                                        <p class="text-gray-800">{{ $material->updated_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($material->description)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Deskripsi</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $material->description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Content -->
                    @if($material->content)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Konten</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="prose max-w-none">
                                {!! $material->content !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Material Access Statistics -->
                    @if($material->materialAccesses && $material->materialAccesses->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Statistik Akses</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ $material->materialAccesses->count() }}</div>
                                    <div class="text-sm text-gray-600">Total Akses</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ $material->materialAccesses->where('completed', true)->count() }}</div>
                                    <div class="text-sm text-gray-600">Selesai</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-orange-600">{{ $material->materialAccesses->where('completed', false)->count() }}</div>
                                    <div class="text-sm text-gray-600">Belum Selesai</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Recent Access Log -->
                    @if($material->materialAccesses && $material->materialAccesses->count() > 0)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Log Akses Terbaru</h3>
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Akses</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($material->materialAccesses->take(10) as $access)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $access->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $access->user->email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $access->accessed_at ? $access->accessed_at->format('d M Y H:i') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $access->completed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $access->completed ? 'Selesai' : 'Dalam Progress' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $access->progress_percentage ?? 0 }}%
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