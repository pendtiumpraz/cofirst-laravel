@extends('layouts.app')

@section('title', 'Edit Class')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Class</h1>
            <p class="text-gray-600">Update class information for: {{ $class->name }}</p>
        </div>
        <a href="{{ route('admin.classes.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6">
                    <form action="{{ route('admin.classes.update', $class) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Class Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $class->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Class Type</label>
                                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select Type</option>
                                    <option value="private_home_call" {{ old('type', $class->type) == 'private_home_call' ? 'selected' : '' }}>Private - Home Call</option>
                                    <option value="private_office_1on1" {{ old('type', $class->type) == 'private_office_1on1' ? 'selected' : '' }}>Private - Office 1-on-1</option>
                                    <option value="private_online_1on1" {{ old('type', $class->type) == 'private_online_1on1' ? 'selected' : '' }}>Private - Online 1-on-1</option>
                                    <option value="public_school_extracurricular" {{ old('type', $class->type) == 'public_school_extracurricular' ? 'selected' : '' }}>Public School Extracurricular</option>
                                    <option value="offline_seminar" {{ old('type', $class->type) == 'offline_seminar' ? 'selected' : '' }}>Offline Seminar</option>
                                    <option value="online_webinar" {{ old('type', $class->type) == 'online_webinar' ? 'selected' : '' }}>Online Webinar</option>
                                    <option value="group_class_3_5_kids" {{ old('type', $class->type) == 'group_class_3_5_kids' ? 'selected' : '' }}>Group Class (3-5 Kids)</option>
                                    <option value="free_webinar" {{ old('type', $class->type) == 'free_webinar' ? 'selected' : '' }}>Free Webinar</option>
                                    <option value="free_trial_30min" {{ old('type', $class->type) == 'free_trial_30min' ? 'selected' : '' }}>Free Trial 30 Min</option>
                                </select>
                            </div>
                            <div>
                                <label for="course_id" class="block text-sm font-medium text-gray-700">Course</label>
                                <select name="course_id" id="course_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onchange="loadCurriculumByCourse()">
                                    <option value="">Select Course</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id', $class->course_id) == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="curriculum_id" class="block text-sm font-medium text-gray-700">Curriculum</label>
                                <select name="curriculum_id" id="curriculum_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @if($class->curriculum)
                                        <option value="{{ $class->curriculum->id }}" selected>{{ $class->curriculum->title }}</option>
                                    @else
                                        <option value="">Select Course First</option>
                                    @endif
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Teachers <span class="text-sm text-gray-500">(Select one or more teachers)</span></label>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    @foreach ($teachers as $teacher)
                                        @php
                                            $isSelected = in_array($teacher->id, old('teacher_ids', $class->teachers->pluck('id')->toArray()));
                                        @endphp
                                        <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-300 cursor-pointer transition-colors {{ $isSelected ? 'bg-blue-50 border-blue-300' : '' }}">
                                            <input 
                                                type="checkbox" 
                                                name="teacher_ids[]" 
                                                value="{{ $teacher->id }}" 
                                                {{ $isSelected ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                            >
                                            <div class="ml-3 flex items-center">
                                                <x-user-avatar 
                                                    :user="$teacher" 
                                                    size="sm" 
                                                    gradient="blue"
                                                />
                                                <div class="ml-2">
                                                    <div class="text-sm font-medium text-gray-900">{{ $teacher->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $teacher->email }}</div>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-sm text-gray-500">
                                    <span class="inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Select one or more teachers for this class. All teachers have equal access.
                                    </span>
                                </p>
                                @error('teacher_ids')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                                <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $class->max_students) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $class->start_date->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $class->end_date->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Class Photo</label>
                                
                                <x-photo-upload 
                                    name="photo"
                                    :currentPhoto="$class->photo_url"
                                    :maxSize="5120"
                                    accept="image/jpeg,image/png,image/jpg,image/gif"
                                />
                                @error('photo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label for="is_active" class="inline-flex items-center">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $class->status === 'active') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-600">Active</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Update Class') }}
                            </button>
                        </div>
                    </form>
        </div>
    </div>
</div>

<script>
async function loadCurriculumByCourse() {
    const courseId = document.getElementById('course_id').value;
    const curriculumSelect = document.getElementById('curriculum_id');
    
    // Clear existing options
    curriculumSelect.innerHTML = '<option value="">Loading...</option>';
    
    if (!courseId) {
        curriculumSelect.innerHTML = '<option value="">Select Course First</option>';
        return;
    }
    
    try {
        const response = await fetch(`/admin/courses/${courseId}/curriculum`);
        if (response.ok) {
            const curriculum = await response.json();
            
            if (curriculum) {
                curriculumSelect.innerHTML = `<option value="${curriculum.id}">${curriculum.title}</option>`;
            } else {
                curriculumSelect.innerHTML = '<option value="">No curriculum found for this course</option>';
            }
        } else {
            curriculumSelect.innerHTML = '<option value="">Error loading curriculum</option>';
        }
    } catch (error) {
        console.error('Error loading curriculum:', error);
        curriculumSelect.innerHTML = '<option value="">Error loading curriculum</option>';
    }
}

// Load curriculum on page load if course is already selected
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('course_id').value) {
        loadCurriculumByCourse();
    }
});
</script>
@endsection
