<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Schedule') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Schedule</h3>
                    <form action="{{ route('admin.schedules.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label for="class_id" class="block text-sm font-medium text-gray-700">Class</label>
                                <select name="class_id" id="class_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select a Class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->course->name ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="day_of_week" class="block text-sm font-medium text-gray-700">Day of Week</label>
                                <select name="day_of_week" id="day_of_week" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select Day</option>
                                    <option value="1" {{ old('day_of_week') == '1' ? 'selected' : '' }}>Monday</option>
                                    <option value="2" {{ old('day_of_week') == '2' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="3" {{ old('day_of_week') == '3' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="4" {{ old('day_of_week') == '4' ? 'selected' : '' }}>Thursday</option>
                                    <option value="5" {{ old('day_of_week') == '5' ? 'selected' : '' }}>Friday</option>
                                    <option value="6" {{ old('day_of_week') == '6' ? 'selected' : '' }}>Saturday</option>
                                    <option value="0" {{ old('day_of_week') == '0' ? 'selected' : '' }}>Sunday</option>
                                </select>
                                @error('day_of_week')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                                <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('start_time')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                                <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('end_time')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="room" class="block text-sm font-medium text-gray-700">Room</label>
                                <input type="text" name="room" id="room" value="{{ old('room') }}" placeholder="e.g., Room A1, Online" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('room')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="teacher_assignment_id" class="block text-sm font-medium text-gray-700">Teacher</label>
                                <select name="teacher_assignment_id" id="teacher_assignment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select a Teacher</option>
                                    @foreach ($teachers as $teacher)
                                        @php
                                            $firstAssignment = $teacher->teacherAssignments->first();
                                        @endphp
                                        @if($firstAssignment)
                                            <option value="{{ $firstAssignment->id }}" 
                                                {{ old('teacher_assignment_id') == $firstAssignment->id ? 'selected' : '' }}>
                                                {{ $teacher->name }}
                                            </option>
                                        @else
                                            <option value="teacher_{{ $teacher->id }}">
                                                {{ $teacher->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('teacher_assignment_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="enrollment_id" class="block text-sm font-medium text-gray-700">Student</label>
                                <select name="enrollment_id" id="enrollment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select a Student</option>
                                    @foreach ($students as $student)
                                        @php
                                            $firstEnrollment = $student->enrollments->first();
                                        @endphp
                                        @if($firstEnrollment)
                                            <option value="{{ $firstEnrollment->id }}" 
                                                {{ old('enrollment_id') == $firstEnrollment->id ? 'selected' : '' }}>
                                                {{ $student->name }}
                                            </option>
                                        @else
                                            <option value="student_{{ $student->id }}">
                                                {{ $student->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('enrollment_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700">Is Active</label>
                                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Create Schedule') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const classSelect = document.getElementById('class_id');
            const teacherAssignmentSelect = document.getElementById('teacher_assignment_id');
            const enrollmentSelect = document.getElementById('enrollment_id');

            function fetchTeachersAndEnrollments(classId) {
                // Clear previous options
                teacherAssignmentSelect.innerHTML = '<option value="">Select a Teacher</option>';
                enrollmentSelect.innerHTML = '<option value="">Select a Student</option>';

                if (!classId) {
                    return;
                }

                // Get CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Fetch Teachers
                fetch(`/api/v1/schedule-data/teachers/${classId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        data.forEach(assignment => {
                            const option = document.createElement('option');
                            option.value = assignment.id;
                            option.textContent = assignment.teacher.name;
                            teacherAssignmentSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching teachers:', error));

                // Fetch Enrollments
                fetch(`/api/v1/schedule-data/enrollments/${classId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        data.forEach(enrollment => {
                            const option = document.createElement('option');
                            option.value = enrollment.id;
                            option.textContent = `${enrollment.student.name} (${enrollment.class.name})`;
                            enrollmentSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching enrollments:', error));
            }

            classSelect.addEventListener('change', function () {
                fetchTeachersAndEnrollments(this.value);
            });

            // Initial fetch if a class is already selected (e.g., on form validation error)
            if (classSelect.value) {
                fetchTeachersAndEnrollments(classSelect.value);
            }
        });
    </script>
</x-app-layout>
