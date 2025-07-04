<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurikulum Kelas Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($curriculums->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($curriculums as $curriculum)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-md p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        {{ $curriculum->name }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mb-2">
                                        {{ $curriculum->course->name }}
                                    </p>
                                    @if($curriculum->description)
                                        <p class="text-gray-600 text-sm mb-4">
                                            {{ Str::limit($curriculum->description, 100) }}
                                        </p>
                                    @endif
                                    <div class="flex justify-between items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $curriculum->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($curriculum->status) }}
                                        </span>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('teacher.curriculums.show', $curriculum) }}" 
                                               class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                                Lihat
                                            </a>
                                            <a href="{{ route('teacher.curriculums.materials', $curriculum) }}" 
                                               class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                                Materi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <h3 class="text-lg font-medium text-gray-900">Tidak ada kurikulum</h3>
                            <p class="text-gray-500">Belum ada kurikulum yang tersedia untuk kelas yang Anda ajar.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>