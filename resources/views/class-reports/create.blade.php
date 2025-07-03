<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Berita Acara Kelas') }}
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

                    <form action="{{ route('class-reports.store') }}" method="POST">
                        @csrf

                        <!-- Hidden fields for class and student -->                        
                        <input type="hidden" name="class_id" id="class_id" value="{{ old('class_id') }}">
                        <input type="hidden" name="student_id" id="student_id" value="{{ old('student_id') }}">

                        <!-- Schedule Selection -->
                        <div class="mb-6">
                            <label for="schedule_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Jadwal Kelas
                            </label>
                            <select name="schedule_id" id="schedule_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="">Pilih Jadwal</option>
                                @foreach($availableSchedules as $schedule)
                                    <option value="{{ $schedule->id }}" 
                                        {{ $selectedSchedule && $selectedSchedule->id == $schedule->id ? 'selected' : '' }}
                                        data-class-id="{{ $schedule->className->id }}"
                                        data-student-id="{{ $schedule->enrollment->student->id }}"
                                        data-class-name="{{ $schedule->className->name }}"
                                        data-course-name="{{ $schedule->className->course->name }}"
                                        data-student-name="{{ $schedule->enrollment->student->name }}"
                                        data-day="{{ $schedule->day_name }}"
                                        data-time="{{ $schedule->time_range }}">
                                        {{ $schedule->className->name }} - {{ $schedule->enrollment->student->name }} ({{ $schedule->day_name }}, {{ $schedule->time_range }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Schedule Info Display -->
                        <div id="schedule-info" class="mb-6 p-4 bg-gray-50 rounded-lg" style="display: none;">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Jadwal</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium">Kelas:</span> <span id="info-class"></span>
                                </div>
                                <div>
                                    <span class="font-medium">Kursus:</span> <span id="info-course"></span>
                                </div>
                                <div>
                                    <span class="font-medium">Siswa:</span> <span id="info-student"></span>
                                </div>
                                <div>
                                    <span class="font-medium">Hari & Waktu:</span> <span id="info-time"></span>
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
                                   value="{{ old('report_date', date('Y-m-d')) }}" required>
                        </div>

                        <!-- Time -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                                    Waktu Mulai Kelas
                                </label>
                                <input type="time" name="start_time" id="start_time" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                       value="{{ old('start_time') }}" required>
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                                    Waktu Selesai Kelas
                                </label>
                                <input type="time" name="end_time" id="end_time" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                       value="{{ old('end_time') }}" required>
                            </div>
                        </div>

                        <!-- Meeting Number -->
                        <div class="mb-6">
                            <label for="meeting_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Pertemuan ke-
                            </label>
                            <input type="number" name="meeting_number" id="meeting_number" min="1" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('meeting_number', 1) }}" required>
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
                                        <option value="{{ $curriculum->id }}" {{ old('curriculum_id') == $curriculum->id ? 'selected' : '' }}>
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
                                        <option value="{{ $syllabus->id }}" {{ old('syllabus_id') == $syllabus->id ? 'selected' : '' }}>
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
                                        <option value="{{ $material->id }}" {{ old('material_id') == $material->id ? 'selected' : '' }}>
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
                                      required>{{ old('learning_concept') }}</textarea>
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
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('remember_understanding') }}</textarea>
                                </div>
                                <div>
                                    <label for="understand_comprehension" class="block text-sm font-medium text-gray-700 mb-2">
                                        Understand (Memahami)
                                    </label>
                                    <textarea name="understand_comprehension" id="understand_comprehension" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('understand_comprehension') }}</textarea>
                                </div>
                                <div>
                                    <label for="apply_application" class="block text-sm font-medium text-gray-700 mb-2">
                                        Apply (Menerapkan)
                                    </label>
                                    <textarea name="apply_application" id="apply_application" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('apply_application') }}</textarea>
                                </div>
                                <div>
                                    <label for="analyze_analysis" class="block text-sm font-medium text-gray-700 mb-2">
                                        Analyze (Menganalisis)
                                    </label>
                                    <textarea name="analyze_analysis" id="analyze_analysis" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('analyze_analysis') }}</textarea>
                                </div>
                                <div>
                                    <label for="evaluate_evaluation" class="block text-sm font-medium text-gray-700 mb-2">
                                        Evaluate (Mengevaluasi)
                                    </label>
                                    <textarea name="evaluate_evaluation" id="evaluate_evaluation" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('evaluate_evaluation') }}</textarea>
                                </div>
                                <div>
                                    <label for="create_creation" class="block text-sm font-medium text-gray-700 mb-2">
                                        Create (Mencipta)
                                    </label>
                                    <textarea name="create_creation" id="create_creation" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('create_creation') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Notes and Recommendations -->
                        <div class="mb-6">
                            <label for="notes_recommendations" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan dan Rekomendasi
                            </label>
                            <textarea name="notes_recommendations" id="notes_recommendations" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes_recommendations') }}</textarea>
                        </div>

                        <!-- Follow Up Notes -->
                        <div class="mb-6">
                            <label for="follow_up_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Rencana Tindak Lanjut
                            </label>
                            <textarea name="follow_up_notes" id="follow_up_notes" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('follow_up_notes') }}</textarea>
                        </div>

                        <!-- Learning Media Link -->
                        <div class="mb-6">
                            <label for="learning_media_link" class="block text-sm font-medium text-gray-700 mb-2">
                                Upload Foto Bukti Belajar (Link Google Drive)
                            </label>
                            <input type="url" name="learning_media_link" id="learning_media_link" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('learning_media_link') }}" 
                                   placeholder="https://drive.google.com/...">
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('class-reports.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Simpan Berita Acara
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('schedule_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const scheduleInfo = document.getElementById('schedule-info');
            
            if (selectedOption.value) {
                // Set hidden fields
                document.getElementById('class_id').value = selectedOption.dataset.classId;
                document.getElementById('student_id').value = selectedOption.dataset.studentId;
                
                // Update info display
                document.getElementById('info-class').textContent = selectedOption.dataset.className;
                document.getElementById('info-course').textContent = selectedOption.dataset.courseName;
                document.getElementById('info-student').textContent = selectedOption.dataset.studentName;
                document.getElementById('info-time').textContent = selectedOption.dataset.day + ', ' + selectedOption.dataset.time;
                scheduleInfo.style.display = 'block';
            } else {
                // Clear hidden fields
                document.getElementById('class_id').value = '';
                document.getElementById('student_id').value = '';
                scheduleInfo.style.display = 'none';
            }
        });

        // Trigger change event if there's a selected schedule
        if (document.getElementById('schedule_id').value) {
            document.getElementById('schedule_id').dispatchEvent(new Event('change'));
        }
    </script>
</x-app-layout>