@extends('layouts.app')

@section('title', 'Create Certificate')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Create Certificate</h1>
                <p class="text-gray-600">Issue a new certificate to a student</p>
            </div>

            <form method="POST" action="{{ route('certificates.store') }}" class="space-y-6">
                @csrf

                <!-- Template -->
                <div>
                    <label for="template_id" class="block text-sm font-medium text-gray-700 mb-2">Certificate Template</label>
                    <select name="template_id" id="template_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('template_id') border-red-500 @enderror">
                        <option value="">Select a template</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }} ({{ ucfirst($template->type) }})
                            </option>
                        @endforeach
                    </select>
                    @error('template_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Student -->
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <select name="student_id" id="student_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('student_id') border-red-500 @enderror">
                        <option value="">Select a student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Certificate Type</label>
                    <select name="type" id="type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('type') border-red-500 @enderror">
                        <option value="">Select type</option>
                        <option value="completion" {{ old('type') == 'completion' ? 'selected' : '' }}>Completion</option>
                        <option value="achievement" {{ old('type') == 'achievement' ? 'selected' : '' }}>Achievement</option>
                        <option value="participation" {{ old('type') == 'participation' ? 'selected' : '' }}>Participation</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Course (Optional) -->
                <div>
                    <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">Course (Optional)</label>
                    <select name="course_id" id="course_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('course_id') border-red-500 @enderror">
                        <option value="">Select a course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('course_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Class (Optional) -->
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class (Optional)</label>
                    <select name="class_id" id="class_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('class_id') border-red-500 @enderror">
                        <option value="">Select a class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Certificate Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                           placeholder="e.g., Certificate of Completion">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                              placeholder="Additional description for the certificate">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Issue Date -->
                <div>
                    <label for="issue_date" class="block text-sm font-medium text-gray-700 mb-2">Issue Date</label>
                    <input type="date" name="issue_date" id="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('issue_date') border-red-500 @enderror">
                    @error('issue_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expiry Date -->
                <div>
                    <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-2">Expiry Date (Optional)</label>
                    <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('expiry_date') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Leave empty for no expiration</p>
                    @error('expiry_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Additional Metadata -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Information (Optional)</label>
                    <div class="space-y-3" id="metadata-fields">
                        <div class="flex gap-2">
                            <input type="text" name="metadata[grade]" placeholder="Grade (e.g., A+)" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="{{ old('metadata.grade') }}">
                            <input type="text" name="metadata[score]" placeholder="Score (e.g., 95%)" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="{{ old('metadata.score') }}">
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-6">
                    <a href="{{ route('certificates.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Create Certificate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-populate title based on type selection
    document.getElementById('type').addEventListener('change', function() {
        const titleField = document.getElementById('title');
        const typeValue = this.value;
        
        if (typeValue && !titleField.value) {
            const titles = {
                'completion': 'Certificate of Completion',
                'achievement': 'Certificate of Achievement',
                'participation': 'Certificate of Participation'
            };
            titleField.value = titles[typeValue] || '';
        }
    });
});
</script>
@endsection