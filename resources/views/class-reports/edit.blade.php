<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Berita Acara Kelas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Grace Period Warning -->
                    @php
                        $daysSinceClass = now()->diffInDays($classReport->report_date);
                        $isWithinGracePeriod = $daysSinceClass <= 7;
                    @endphp
                    
                    @if(!$isWithinGracePeriod)
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                            <p><strong>Peringatan:</strong> Periode edit telah berakhir (lebih dari 1 minggu sejak tanggal kelas). Anda mungkin tidak dapat menyimpan perubahan.</p>
                        </div>
                    @endif

                    <form action="{{ route('class-reports.update', $classReport) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Schedule Info Display (Read-only) -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Jadwal</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium">Kelas:</span> <span>{{ $classReport->class->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Kursus:</span> <span>{{ $classReport->class->course->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Siswa:</span> <span>{{ $classReport->student->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Hari & Waktu:</span> <span>{{ $classReport->schedule->day_name }}, {{ $classReport->schedule->time_range }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Report Date -->
                        <div class="mb-6">
                            <label for="report_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Pelaksanaan Kelas
                            </label>
                            <input type="date" name="report_date" id="report_date" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('report_date', $classReport->report_date->format('Y-m-d')) }}" required>
                        </div>

                        <!-- Time -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                                    Waktu Mulai Kelas
                                </label>
                                <input type="time" name="start_time" id="start_time" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                       value="{{ old('start_time', $classReport->start_time->format('H:i')) }}" required>
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                                    Waktu Selesai Kelas
                                </label>
                                <input type="time" name="end_time" id="end_time" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                       value="{{ old('end_time', $classReport->end_time->format('H:i')) }}" required>
                            </div>
                        </div>

                        <!-- Meeting Number -->
                        <div class="mb-6">
                            <label for="meeting_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Pertemuan ke-
                            </label>
                            <input type="number" name="meeting_number" id="meeting_number" min="1" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('meeting_number', $classReport->meeting_number) }}" required>
                        </div>

                        <!-- Curriculum, Syllabus, Material -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div>
                                <label for="curriculum_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kurikulum
                                </label>
                                <select name="curriculum_id" id="curriculum_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">Pilih Kurikulum</option>
                                    @foreach($curriculums as $curriculum)
                                        <option value="{{ $curriculum->id }}" {{ old('curriculum_id', $classReport->curriculum_id) == $curriculum->id ? 'selected' : '' }}>
                                            {{ $curriculum->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="syllabus_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Silabus
                                </label>
                                <select name="syllabus_id" id="syllabus_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">Pilih Silabus</option>
                                    @foreach($syllabuses as $syllabus)
                                        <option value="{{ $syllabus->id }}" {{ old('syllabus_id', $classReport->syllabus_id) == $syllabus->id ? 'selected' : '' }}>
                                            {{ $syllabus->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="material_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Materi
                                </label>
                                <select name="material_id" id="material_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">Pilih Materi</option>
                                    @foreach($materials as $material)
                                        <option value="{{ $material->id }}" {{ old('material_id', $classReport->material_id) == $material->id ? 'selected' : '' }}>
                                            {{ $material->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Learning Concept -->
                        <div class="mb-6">
                            <label for="learning_concept" class="block text-sm font-medium text-gray-700 mb-2">
                                Materi / Konsep Pembelajaran
                            </label>
                            <textarea name="learning_concept" id="learning_concept" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                      required>{{ old('learning_concept', $classReport->learning_concept) }}</textarea>
                        </div>

                        <!-- Bloom's Taxonomy -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Taksonomi Bloom</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="remember_understanding" class="block text-sm font-medium text-gray-700 mb-2">
                                        Remember (Mengingat)
                                    </label>
                                    <textarea name="remember_understanding" id="remember_understanding" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('remember_understanding', $classReport->remember_understanding) }}</textarea>
                                </div>
                                <div>
                                    <label for="understand_comprehension" class="block text-sm font-medium text-gray-700 mb-2">
                                        Understand (Memahami)
                                    </label>
                                    <textarea name="understand_comprehension" id="understand_comprehension" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('understand_comprehension', $classReport->understand_comprehension) }}</textarea>
                                </div>
                                <div>
                                    <label for="apply_application" class="block text-sm font-medium text-gray-700 mb-2">
                                        Apply (Menerapkan)
                                    </label>
                                    <textarea name="apply_application" id="apply_application" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('apply_application', $classReport->apply_application) }}</textarea>
                                </div>
                                <div>
                                    <label for="analyze_analysis" class="block text-sm font-medium text-gray-700 mb-2">
                                        Analyze (Menganalisis)
                                    </label>
                                    <textarea name="analyze_analysis" id="analyze_analysis" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('analyze_analysis', $classReport->analyze_analysis) }}</textarea>
                                </div>
                                <div>
                                    <label for="evaluate_evaluation" class="block text-sm font-medium text-gray-700 mb-2">
                                        Evaluate (Mengevaluasi)
                                    </label>
                                    <textarea name="evaluate_evaluation" id="evaluate_evaluation" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('evaluate_evaluation', $classReport->evaluate_evaluation) }}</textarea>
                                </div>
                                <div>
                                    <label for="create_creation" class="block text-sm font-medium text-gray-700 mb-2">
                                        Create (Mencipta)
                                    </label>
                                    <textarea name="create_creation" id="create_creation" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('create_creation', $classReport->create_creation) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Notes and Recommendations -->
                        <div class="mb-6">
                            <label for="notes_recommendations" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan dan Rekomendasi
                            </label>
                            <textarea name="notes_recommendations" id="notes_recommendations" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes_recommendations', $classReport->notes_recommendations) }}</textarea>
                        </div>

                        <!-- Follow Up Notes -->
                        <div class="mb-6">
                            <label for="follow_up_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Rencana Tindak Lanjut
                            </label>
                            <textarea name="follow_up_notes" id="follow_up_notes" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('follow_up_notes', $classReport->follow_up_notes) }}</textarea>
                        </div>

                        <!-- Learning Media Link -->
                        <div class="mb-6">
                            <label for="learning_media_link" class="block text-sm font-medium text-gray-700 mb-2">
                                Upload Foto Bukti Belajar (Link Google Drive)
                            </label>
                            <input type="url" name="learning_media_link" id="learning_media_link" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('learning_media_link', $classReport->learning_media_link) }}" 
                                   placeholder="https://drive.google.com/...">
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('class-reports.show', $classReport) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" {{ !$isWithinGracePeriod ? 'disabled' : '' }}>
                                Update Berita Acara
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>