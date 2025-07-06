@extends('layouts.app')

@section('title', 'Edit Badge')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Badge</h1>
            <p class="text-gray-600 mt-2">Update the badge details and criteria</p>
        </div>

        <form action="{{ route('badges.update', $badge->id) }}" method="POST" class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Badge Name</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $badge->name) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                          required>{{ old('description', $badge->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category and Level -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category" 
                            id="category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('category') border-red-500 @enderror"
                            required>
                        <option value="">Select a category</option>
                        <option value="academic" {{ old('category', $badge->category) == 'academic' ? 'selected' : '' }}>Academic</option>
                        <option value="social" {{ old('category', $badge->category) == 'social' ? 'selected' : '' }}>Social</option>
                        <option value="attendance" {{ old('category', $badge->category) == 'attendance' ? 'selected' : '' }}>Attendance</option>
                        <option value="special" {{ old('category', $badge->category) == 'special' ? 'selected' : '' }}>Special</option>
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level -->
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                    <select name="level" 
                            id="level"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('level') border-red-500 @enderror"
                            required>
                        <option value="">Select a level</option>
                        <option value="bronze" {{ old('level', $badge->level) == 'bronze' ? 'selected' : '' }}>Bronze</option>
                        <option value="silver" {{ old('level', $badge->level) == 'silver' ? 'selected' : '' }}>Silver</option>
                        <option value="gold" {{ old('level', $badge->level) == 'gold' ? 'selected' : '' }}>Gold</option>
                        <option value="platinum" {{ old('level', $badge->level) == 'platinum' ? 'selected' : '' }}>Platinum</option>
                    </select>
                    @error('level')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Points and Sort Order -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Points Required -->
                <div>
                    <label for="points_required" class="block text-sm font-medium text-gray-700 mb-2">Points Awarded</label>
                    <input type="number" 
                           name="points_required" 
                           id="points_required" 
                           value="{{ old('points_required', $badge->points_required) }}"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('points_required') border-red-500 @enderror"
                           required>
                    @error('points_required')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <input type="number" 
                           name="sort_order" 
                           id="sort_order" 
                           value="{{ old('sort_order', $badge->sort_order) }}"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('sort_order') border-red-500 @enderror">
                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Criteria -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Badge Criteria</label>
                <p class="text-sm text-gray-500 mb-2">Define the conditions that must be met to earn this badge</p>
                <div id="criteria-container">
                    @if(old('criteria'))
                        @foreach(old('criteria') as $index => $criterion)
                            <div class="criteria-item mb-2 flex">
                                <input type="text" 
                                       name="criteria[]" 
                                       value="{{ $criterion }}"
                                       placeholder="Enter a criterion"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @if($index > 0)
                                    <button type="button" onclick="removeCriterion(this)" class="ml-2 text-red-600 hover:text-red-800">
                                        Remove
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    @else
                        @foreach($badge->criteria ?? [] as $index => $criterion)
                            <div class="criteria-item mb-2 flex">
                                <input type="text" 
                                       name="criteria[]" 
                                       value="{{ $criterion }}"
                                       placeholder="Enter a criterion"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @if($index > 0)
                                    <button type="button" onclick="removeCriterion(this)" class="ml-2 text-red-600 hover:text-red-800">
                                        Remove
                                    </button>
                                @endif
                            </div>
                        @endforeach
                        @if(empty($badge->criteria))
                            <div class="criteria-item mb-2">
                                <input type="text" 
                                       name="criteria[]" 
                                       placeholder="Enter a criterion"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        @endif
                    @endif
                </div>
                <button type="button" onclick="addCriterion()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                    + Add another criterion
                </button>
                @error('criteria')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Icon -->
            <div class="mb-6">
                <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">Icon Class (Optional)</label>
                <input type="text" 
                       name="icon" 
                       id="icon" 
                       value="{{ old('icon', $badge->icon) }}"
                       placeholder="e.g., fas fa-star"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('icon') border-red-500 @enderror">
                <p class="mt-1 text-sm text-gray-500">Font Awesome class name for the badge icon</p>
                @error('icon')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Is Active -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $badge->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Active (Badge can be earned)</span>
                </label>
            </div>

            <!-- Badge Stats -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Badge Statistics</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Total Users Earned:</span>
                        <span class="font-medium text-gray-900 ml-2">{{ $badge->users()->count() }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Created:</span>
                        <span class="font-medium text-gray-900 ml-2">{{ $badge->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.badges.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update Badge
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function addCriterion() {
    const container = document.getElementById('criteria-container');
    const div = document.createElement('div');
    div.className = 'criteria-item mb-2 flex';
    div.innerHTML = `
        <input type="text" 
               name="criteria[]" 
               placeholder="Enter a criterion"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <button type="button" onclick="removeCriterion(this)" class="ml-2 text-red-600 hover:text-red-800">
            Remove
        </button>
    `;
    container.appendChild(div);
}

function removeCriterion(button) {
    button.parentElement.remove();
}
</script>
@endsection