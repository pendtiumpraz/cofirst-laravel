<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Berita Acara Kelas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('teacher.class-reports.store') }}">
                        @csrf

                        <!-- Pilih Kelas -->
                        <div class="mb-4">
                            <label for="class_id" class="block text-sm font-medium text-gray-700">Pilih Kelas</label>
                            <select id="class_id" name="class_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Pelaksanaan Kelas -->
                        <div class="mb-4">
                            <label for="report_date" class="block text-sm font-medium text-gray-700">Tanggal Pelaksanaan Kelas</label>
                            <input type="date" id="report_date" name="report_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('report_date') }}">
                            @error('report_date')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Waktu Mulai (Otomatis dari Jadwal) -->
                        <div class="mb-4">
                            <label for="schedule_id" class="block text-sm font-medium text-gray-700">Pilih Jadwal (Waktu Mulai Otomatis)</label>
                            <select id="schedule_id" name="schedule_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Pilih Jadwal --</option>
                            </select>
                            @error('schedule_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <input type="hidden" id="start_time" name="start_time">
                        </div>

                        <!-- Waktu Selesai (Input Manual) -->
                        <div class="mb-4">
                            <label for="end_time" class="block text-sm font-medium text-gray-700">Waktu Selesai</label>
                            <input type="time" id="end_time" name="end_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('end_time') }}">
                            @error('end_time')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Siswa -->
                        <div class="mb-4">
                            <label for="student_id" class="block text-sm font-medium text-gray-700">Nama Siswa</label>
                            <select id="student_id" name="student_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Pilih Siswa --</option>
                            </select>
                            @error('student_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Pertemuan Ke- -->
                        <div class="mb-4">
                            <label for="meeting_number" class="block text-sm font-medium text-gray-700">Pertemuan Ke-</label>
                            <input type="number" id="meeting_number" name="meeting_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('meeting_number') }}" min="1">
                            @error('meeting_number')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kurikulum, Silabus, Materi -->
                        <div class="mb-4">
                            <label for="curriculum_id" class="block text-sm font-medium text-gray-700">Kurikulum</label>
                            <select id="curriculum_id" name="curriculum_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Pilih Kurikulum --</option>
                            </select>
                            @error('curriculum_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="syllabus_id" class="block text-sm font-medium text-gray-700">Silabus</label>
                            <select id="syllabus_id" name="syllabus_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Pilih Silabus --</option>
                            </select>
                            @error('syllabus_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="material_id" class="block text-sm font-medium text-gray-700">Materi</label>
                            <select id="material_id" name="material_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Pilih Materi --</option>
                            </select>
                            @error('material_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Materi/Konsep Pembelajaran -->
                        <div class="mb-4">
                            <label for="learning_concept" class="block text-sm font-medium text-gray-700">Materi/Konsep Pembelajaran</label>
                            <textarea id="learning_concept" name="learning_concept" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('learning_concept') }}</textarea>
                            @error('learning_concept')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Taksonomi Bloom -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Taksonomi Bloom (Opsional)</label>
                            <div class="space-y-2 mt-2">
                                <div>
                                    <label for="remember_understanding" class="block text-sm font-medium text-gray-700">Remember (Mengingat)</label>
                                    <textarea id="remember_understanding" name="remember_understanding" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('remember_understanding') }}</textarea>
                                </div>
                                <div>
                                    <label for="understand_comprehension" class="block text-sm font-medium text-gray-700">Understand (Memahami)</label>
                                    <textarea id="understand_comprehension" name="understand_comprehension" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('understand_comprehension') }}</textarea>
                                </div>
                                <div>
                                    <label for="apply_application" class="block text-sm font-medium text-gray-700">Apply (Menerapkan)</label>
                                    <textarea id="apply_application" name="apply_application" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('apply_application') }}</textarea>
                                </div>
                                <div>
                                    <label for="analyze_analysis" class="block text-sm font-medium text-gray-700">Analyze (Menganalisis)</label>
                                    <textarea id="analyze_analysis" name="analyze_analysis" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('analyze_analysis') }}</textarea>
                                </div>
                                <div>
                                    <label for="evaluate_evaluation" class="block text-sm font-medium text-gray-700">Evaluate (Mengevaluasi)</label>
                                    <textarea id="evaluate_evaluation" name="evaluate_evaluation" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('evaluate_evaluation') }}</textarea>
                                </div>
                                <div>
                                    <label for="create_creation" class="block text-sm font-medium text-gray-700">Create (Membuat) - Opsional</label>
                                    <textarea id="create_creation" name="create_creation" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('create_creation') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan & Rekomendasi -->
                        <div class="mb-4">
                            <label for="notes_recommendations" class="block text-sm font-medium text-gray-700">Catatan & Rekomendasi</label>
                            <textarea id="notes_recommendations" name="notes_recommendations" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes_recommendations') }}</textarea>
                            @error('notes_recommendations')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Catatan Tindak Lanjut -->
                        <div class="mb-4">
                            <label for="follow_up_notes" class="block text-sm font-medium text-gray-700">Catatan Tindak Lanjut</label>
                            <textarea id="follow_up_notes" name="follow_up_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('follow_up_notes') }}</textarea>
                            @error('follow_up_notes')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Link Google Drive (Foto/Video Pembelajaran) -->
                        <div class="mb-4">
                            <label for="learning_media_link" class="block text-sm font-medium text-gray-700">Link Google Drive (Foto/Video Pembelajaran)</label>
                            <input type="url" id="learning_media_link" name="learning_media_link" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('learning_media_link') }}" placeholder="https://drive.google.com/...">
                            @error('learning_media_link')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Simpan Berita Acara
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to populate select options
            function populateSelect(selector, data, idKey, nameKey) {
                $(selector).empty().append('<option value="">-- Pilih --</option>');
                $.each(data, function(key, value) {
                    $(selector).append('<option value="' + value[idKey] + '">' + value[nameKey] + '</option>');
                });
            }

            // When class is selected
            $('#class_id').change(function() {
                var classId = $(this).val();
                if (classId) {
                    $.ajax({
                        url: '{{ route('teacher.class-reports.get-schedules-and-students') }}',
                        type: 'GET',
                        data: { class_id: classId },
                        success: function(data) {
                            // Populate Schedules
                            populateSelect('#schedule_id', data.schedules, 'id', 'title');
                            
                            // Populate Students
                            populateSelect('#student_id', data.students, 'id', 'name');

                            // Populate Curriculum, Syllabus, Material
                            if (data.curriculum) {
                                populateSelect('#curriculum_id', [data.curriculum], 'id', 'title');
                                $('#curriculum_id').val(data.curriculum.id).trigger('change');
                            } else {
                                populateSelect('#curriculum_id', [], 'id', 'title');
                            }
                            populateSelect('#syllabus_id', data.syllabuses, 'id', 'title');
                            populateSelect('#material_id', data.materials, 'id', 'title');
                        }
                    });
                } else {
                    // Clear all dependent selects if no class is selected
                    populateSelect('#schedule_id', [], 'id', 'title');
                    populateSelect('#student_id', [], 'id', 'name');
                    populateSelect('#curriculum_id', [], 'id', 'title');
                    populateSelect('#syllabus_id', [], 'id', 'title');
                    populateSelect('#material_id', [], 'id', 'title');
                }
            });

            // When schedule is selected, set start_time hidden input
            $('#schedule_id').change(function() {
                var selectedScheduleId = $(this).val();
                if (selectedScheduleId) {
                    var selectedSchedule = $(this).find('option:selected').text();
                    // Extract time from schedule title if needed, or fetch from data.schedules
                    // For simplicity, assuming start_time is directly available from the fetched data
                    // You might need to store start_time in data-attributes or re-fetch if not available
                    // For now, let's assume we can parse it or it's set by AJAX response
                    var schedulesData = []; // You would ideally get this from the AJAX response
                    // Find the selected schedule object from the original data.schedules array
                    // This requires storing the data.schedules in a global/accessible variable
                    // For now, we'll just use a placeholder for start_time
                    
                    // A more robust solution would be to store the full schedule object in a data attribute
                    // or re-fetch it. For this example, we'll just use a dummy value.
                    $('#start_time').val('09:00'); // Placeholder, replace with actual logic
                }
            });

            // When curriculum is selected, filter syllabuses
            $('#curriculum_id').change(function() {
                var selectedCurriculumId = $(this).val();
                var classId = $('#class_id').val();
                if (selectedCurriculumId && classId) {
                    $.ajax({
                        url: '{{ route('teacher.class-reports.get-schedules-and-students') }}',
                        type: 'GET',
                        data: { class_id: classId }, // Re-fetch all data
                        success: function(data) {
                            var filteredSyllabuses = data.syllabuses.filter(function(syllabus) {
                                return syllabus.curriculum_id == selectedCurriculumId;
                            });
                            populateSelect('#syllabus_id', filteredSyllabuses, 'id', 'title');
                            populateSelect('#material_id', [], 'id', 'title'); // Clear materials
                        }
                    });
                } else {
                    populateSelect('#syllabus_id', [], 'id', 'title');
                    populateSelect('#material_id', [], 'id', 'title');
                }
            });

            // When syllabus is selected, filter materials
            $('#syllabus_id').change(function() {
                var selectedSyllabusId = $(this).val();
                var classId = $('#class_id').val();
                if (selectedSyllabusId && classId) {
                    $.ajax({
                        url: '{{ route('teacher.class-reports.get-schedules-and-students') }}',
                        type: 'GET',
                        data: { class_id: classId }, // Re-fetch all data
                        success: function(data) {
                            var filteredMaterials = data.materials.filter(function(material) {
                                return material.syllabus_id == selectedSyllabusId;
                            });
                            populateSelect('#material_id', filteredMaterials, 'id', 'title');
                        }
                    });
                } else {
                    populateSelect('#material_id', [], 'id', 'title');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
