@extends('layouts.app')

@section('title', 'Create Class')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Class</h1>
            <p class="text-gray-600">Add a new class to the system</p>
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
                    <form action="{{ route('admin.classes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Class Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Class Type</label>
                                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select Type</option>
                                    <option value="private_home_call" {{ old('type') == 'private_home_call' ? 'selected' : '' }}>Private - Home Call</option>
                                    <option value="private_office_1on1" {{ old('type') == 'private_office_1on1' ? 'selected' : '' }}>Private - Office 1-on-1</option>
                                    <option value="private_online_1on1" {{ old('type') == 'private_online_1on1' ? 'selected' : '' }}>Private - Online 1-on-1</option>
                                    <option value="public_school_extracurricular" {{ old('type') == 'public_school_extracurricular' ? 'selected' : '' }}>Public School Extracurricular</option>
                                    <option value="offline_seminar" {{ old('type') == 'offline_seminar' ? 'selected' : '' }}>Offline Seminar</option>
                                    <option value="online_webinar" {{ old('type') == 'online_webinar' ? 'selected' : '' }}>Online Webinar</option>
                                    <option value="group_class_3_5_kids" {{ old('type') == 'group_class_3_5_kids' ? 'selected' : '' }}>Group Class (3-5 Kids)</option>
                                    <option value="free_webinar" {{ old('type') == 'free_webinar' ? 'selected' : '' }}>Free Webinar</option>
                                    <option value="free_trial_30min" {{ old('type') == 'free_trial_30min' ? 'selected' : '' }}>Free Trial 30 Min</option>
                                </select>
                            </div>
                            <div>
                                <label for="course_id" class="block text-sm font-medium text-gray-700">Course</label>
                                <select name="course_id" id="course_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="teacher_id" class="block text-sm font-medium text-gray-700">Teacher</label>
                                <select name="teacher_id" id="teacher_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                                <input type="number" name="capacity" id="capacity" value="{{ old('capacity') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Class Photo</label>
                                <x-photo-upload 
                                    name="photo"
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
                                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-600">Active</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Create Class') }}
                            </button>
                        </div>
                    </form>
        </div>
    </div>
</div>
@endsection