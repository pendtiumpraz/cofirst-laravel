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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                <label for="schedule_date" class="block text-sm font-medium text-gray-700">Schedule Date</label>
                                <input type="date" name="schedule_date" id="schedule_date" value="{{ old('schedule_date', now()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('schedule_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="schedule_time" class="block text-sm font-medium text-gray-700">Schedule Time</label>
                                <input type="time" name="schedule_time" id="schedule_time" value="{{ old('schedule_time') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('schedule_time')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="teacher_assignment_id" class="block text-sm font-medium text-gray-700">Teacher</label>
                                <select name="teacher_assignment_id" id="teacher_assignment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select a Teacher</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->teacherAssignment->id ?? '' }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                                @error('teacher_assignment_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="enrollment_id" class="block text-sm font-medium text-gray-700">Student</label>
                                <select name="enrollment_id" id="enrollment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select a Student</option>
                                    @foreach ($enrollments as $enrollment)
                                        <option value="{{ $enrollment->id }}">{{ $enrollment->student->name ?? 'N/A' }} ({{ $enrollment->class->name ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                                @error('enrollment_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
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

                // Fetch Teachers
                fetch(`/api/v1/schedule-data/teachers/${classId}`)
                    .then(response => response.json())
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
                fetch(`/api/v1/schedule-data/enrollments/${classId}`)
                    .then(response => response.json())
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
