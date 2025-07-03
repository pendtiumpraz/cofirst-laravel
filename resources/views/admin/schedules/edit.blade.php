<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Schedule') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Schedule</h3>
                    <form action="{{ route('admin.schedules.update', $schedule->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label for="class_id" class="block text-sm font-medium text-gray-700">Class</label>
                                <select name="class_id" id="class_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select a Class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id', $schedule->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }} ({{ $class->course->name ?? 'N/A' }})</option>
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
                                    <option value="1" {{ old('day_of_week', $schedule->day_of_week) == '1' ? 'selected' : '' }}>Monday</option>
                                    <option value="2" {{ old('day_of_week', $schedule->day_of_week) == '2' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="3" {{ old('day_of_week', $schedule->day_of_week) == '3' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="4" {{ old('day_of_week', $schedule->day_of_week) == '4' ? 'selected' : '' }}>Thursday</option>
                                    <option value="5" {{ old('day_of_week', $schedule->day_of_week) == '5' ? 'selected' : '' }}>Friday</option>
                                    <option value="6" {{ old('day_of_week', $schedule->day_of_week) == '6' ? 'selected' : '' }}>Saturday</option>
                                    <option value="0" {{ old('day_of_week', $schedule->day_of_week) == '0' ? 'selected' : '' }}>Sunday</option>
                                </select>
                                @error('day_of_week')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                                <input type="time" name="start_time" id="start_time" value="{{ old('start_time', $schedule->start_time ? $schedule->start_time->format('H:i') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('start_time')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                                <input type="time" name="end_time" id="end_time" value="{{ old('end_time', $schedule->end_time ? $schedule->end_time->format('H:i') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('end_time')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="room" class="block text-sm font-medium text-gray-700">Room</label>
                                <input type="text" name="room" id="room" value="{{ old('room', $schedule->room) }}" placeholder="e.g., Room A1, Online" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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
                                            $selectedAssignment = $teacher->teacherAssignments->where('id', $schedule->teacher_assignment_id)->first();
                                            $anyAssignment = $teacher->teacherAssignments->first();
                                        @endphp
                                        @if($selectedAssignment)
                                            <option value="{{ $selectedAssignment->id }}" selected>
                                                {{ $teacher->name }}
                                            </option>
                                        @elseif($anyAssignment)
                                            <option value="{{ $anyAssignment->id }}">
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
                                            $selectedEnrollment = $student->enrollments->where('id', $schedule->enrollment_id)->first();
                                            $anyEnrollment = $student->enrollments->first();
                                        @endphp
                                        @if($selectedEnrollment)
                                            <option value="{{ $selectedEnrollment->id }}" selected>
                                                {{ $student->name }}
                                            </option>
                                        @elseif($anyEnrollment)
                                            <option value="{{ $anyEnrollment->id }}">
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
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $schedule->is_active) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Update Schedule') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // No filtering - show all teachers and students regardless of class selection
        // This allows flexibility to change teachers and students across different classes
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Edit schedule page loaded - all teachers and students are available for selection');
        });
    </script>
</x-app-layout>
