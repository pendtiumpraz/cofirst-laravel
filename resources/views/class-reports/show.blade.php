<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Berita Acara Kelas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Header Info -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Umum</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="font-medium text-gray-700">Tanggal Dibuat:</span>
                                <p class="text-gray-900">{{ $classReport->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Nama Guru:</span>
                                <p class="text-gray-900">{{ $classReport->teacher->name }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Tanggal Pelaksanaan:</span>
                                <p class="text-gray-900">{{ $classReport->report_date->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Waktu Kelas:</span>
                                <p class="text-gray-900">{{ $classReport->start_time->format('H:i') }} - {{ $classReport->end_time->format('H:i') }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Nama Siswa:</span>
                                <p class="text-gray-900">{{ $classReport->student->name }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Kelas yang Diikuti:</span>
                                <p class="text-gray-900">{{ $classReport->class->name }} - {{ $classReport->class->course->name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Class Details -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Kelas</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <span class="font-medium text-gray-700">Pertemuan ke-:</span>
                                <p class="text-gray-900">{{ $classReport->meeting_number }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Kurikulum:</span>
                                <p class="text-gray-900">{{ $classReport->curriculum->name ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Silabus:</span>
                                <p class="text-gray-900">{{ $classReport->syllabus->title ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="font-medium text-gray-700">Materi:</span>
                            <p class="text-gray-900">{{ $classReport->material->title ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Learning Concept -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Materi / Konsep Pembelajaran</h3>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $classReport->learning_concept }}</p>
                        </div>
                    </div>

                    <!-- Bloom's Taxonomy -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Taksonomi Bloom</h3>
                        <div class="grid grid-cols-2 gap-4">
                            @if($classReport->remember_understanding)
                                <div class="p-3 bg-yellow-50 rounded-lg">
                                    <h4 class="font-medium text-yellow-800 mb-2">Remember (Mengingat)</h4>
                                    <p class="text-gray-900 text-sm whitespace-pre-wrap">{{ $classReport->remember_understanding }}</p>
                                </div>
                            @endif
                            @if($classReport->understand_comprehension)
                                <div class="p-3 bg-green-50 rounded-lg">
                                    <h4 class="font-medium text-green-800 mb-2">Understand (Memahami)</h4>
                                    <p class="text-gray-900 text-sm whitespace-pre-wrap">{{ $classReport->understand_comprehension }}</p>
                                </div>
                            @endif
                            @if($classReport->apply_application)
                                <div class="p-3 bg-blue-50 rounded-lg">
                                    <h4 class="font-medium text-blue-800 mb-2">Apply (Menerapkan)</h4>
                                    <p class="text-gray-900 text-sm whitespace-pre-wrap">{{ $classReport->apply_application }}</p>
                                </div>
                            @endif
                            @if($classReport->analyze_analysis)
                                <div class="p-3 bg-purple-50 rounded-lg">
                                    <h4 class="font-medium text-purple-800 mb-2">Analyze (Menganalisis)</h4>
                                    <p class="text-gray-900 text-sm whitespace-pre-wrap">{{ $classReport->analyze_analysis }}</p>
                                </div>
                            @endif
                            @if($classReport->evaluate_evaluation)
                                <div class="p-3 bg-red-50 rounded-lg">
                                    <h4 class="font-medium text-red-800 mb-2">Evaluate (Mengevaluasi)</h4>
                                    <p class="text-gray-900 text-sm whitespace-pre-wrap">{{ $classReport->evaluate_evaluation }}</p>
                                </div>
                            @endif
                            @if($classReport->create_creation)
                                <div class="p-3 bg-indigo-50 rounded-lg">
                                    <h4 class="font-medium text-indigo-800 mb-2">Create (Mencipta)</h4>
                                    <p class="text-gray-900 text-sm whitespace-pre-wrap">{{ $classReport->create_creation }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Notes and Follow Up -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        @if($classReport->notes_recommendations)
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Catatan dan Rekomendasi</h3>
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $classReport->notes_recommendations }}</p>
                                </div>
                            </div>
                        @endif
                        @if($classReport->follow_up_notes)
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Rencana Tindak Lanjut</h3>
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $classReport->follow_up_notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Learning Media -->
                    @if($classReport->learning_media_link)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Foto Bukti Belajar</h3>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <a href="{{ $classReport->learning_media_link }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                    {{ $classReport->learning_media_link }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center pt-6 border-t">
                        <a href="{{ route('class-reports.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Kembali ke Daftar
                        </a>
                        
                        <div class="space-x-2">
                            @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin') || $classReport->teacher_id === Auth::id())
                                <a href="{{ route('class-reports.edit', $classReport) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                    Edit
                                </a>
                                <form action="{{ route('class-reports.destroy', $classReport) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berita acara ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>