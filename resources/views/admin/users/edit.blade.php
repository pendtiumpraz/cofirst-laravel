@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
                <p class="text-gray-600">Update user information</p>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Profile Photo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                    <x-photo-upload 
                        name="photo"
                        :current-photo="$user->profile_photo_url"
                        :max-size="5120"
                        accept="image/jpeg,image/png,image/jpg,image/gif"
                    />
                    @error('photo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" id="password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Leave blank to keep current password</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role" id="role" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror"
                            onchange="toggleStudentSelection()">
                        <option value="">Select a role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" 
                                {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Student Selection (visible only when parent role is selected) -->
                <div id="student-selection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Students (Children)
                    </label>
                    <div class="border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto">
                        @if($availableStudents->count() > 0)
                            @foreach($availableStudents as $student)
                                @php
                                    $isCurrentChild = in_array($student->id, $currentChildrenIds);
                                    $isSelected = in_array($student->id, old('student_ids', $currentChildrenIds));
                                @endphp
                                <label class="flex items-center py-2 px-3 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                           {{ $isSelected ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 flex items-center">
                                            {{ $student->name }}
                                            @if($isCurrentChild)
                                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Current Child
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $student->email }}</div>
                                    </div>
                                </label>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <div class="text-sm text-gray-500">
                                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    No students available
                                </div>
                            </div>
                        @endif
                    </div>
                    @error('student_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('student_ids.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Select students to link with this parent account. Current children are highlighted.
                    </p>
                </div>

                <!-- Status -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" 
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }} 
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-6">
                    <a href="{{ route('admin.users.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleStudentSelection() {
    const roleSelect = document.getElementById('role');
    const studentSelection = document.getElementById('student-selection');
    const studentCheckboxes = document.querySelectorAll('input[name="student_ids[]"]');
    
    if (roleSelect.value === 'parent') {
        studentSelection.classList.remove('hidden');
    } else {
        studentSelection.classList.add('hidden');
        // Don't uncheck current children when hiding - they'll be handled by the controller
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleStudentSelection();
});
</script>
@endsection 