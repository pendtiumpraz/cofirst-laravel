@extends('layouts.app')

@section('title', 'Edit Transaction')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Transaction</h1>
            <p class="text-gray-600 mt-2">Update transaction details</p>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('finance.transactions.update', $transaction) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Student Selection -->
                <div class="mb-6">
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <select name="student_id" id="student_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="">Select a student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ (old('student_id', $transaction->student_id) == $student->id) ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Course Selection (Optional) -->
                <div class="mb-6">
                    <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">Course <span class="text-gray-500 text-sm">(Optional)</span></label>
                    <select name="course_id" id="course_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select a course (optional)</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ (old('course_id', $transaction->course_id) == $course->id) ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('course_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ old('amount', $transaction->amount) }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $transaction->description) }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select name="type" id="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="">Select type</option>
                        <option value="payment" {{ old('type', $transaction->type) == 'payment' ? 'selected' : '' }}>Payment</option>
                        <option value="refund" {{ old('type', $transaction->type) == 'refund' ? 'selected' : '' }}>Refund</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required onchange="togglePaidDate()">
                        <option value="">Select status</option>
                        <option value="pending" {{ old('status', $transaction->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ old('status', $transaction->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ old('status', $transaction->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Paid Date (Only show when status is 'paid') -->
                <div id="paid_date_field" class="mb-6" style="display: none;">
                    <label for="paid_date" class="block text-sm font-medium text-gray-700 mb-2">Paid Date</label>
                    <input type="datetime-local" name="paid_date" id="paid_date" 
                           value="{{ old('paid_date', $transaction->paid_date ? $transaction->paid_date->format('Y-m-d\TH:i') : '') }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('paid_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('finance.transactions.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Update Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePaidDate() {
    const statusSelect = document.getElementById('status');
    const paidDateField = document.getElementById('paid_date_field');
    const paidDateInput = document.getElementById('paid_date');
    
    if (statusSelect.value === 'paid') {
        paidDateField.style.display = 'block';
        // Set current datetime as default if empty
        if (!paidDateInput.value) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            paidDateInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    } else {
        paidDateField.style.display = 'none';
        paidDateInput.value = '';
    }
}

// Check on page load if status is already selected
document.addEventListener('DOMContentLoaded', function() {
    togglePaidDate();
});
</script>
@endsection 