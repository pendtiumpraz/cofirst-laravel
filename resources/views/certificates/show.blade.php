@extends('layouts.app')

@section('title', 'Certificate Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Certificate Details</h1>
                    <p class="text-gray-600">{{ $certificate->certificate_number }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('certificates.download', $certificate) }}" 
                       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Download PDF
                    </a>
                    @if(auth()->user()->hasRole(['admin', 'superadmin']) && $certificate->is_valid)
                    <form action="{{ route('certificates.invalidate', $certificate) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                onclick="return confirm('Are you sure you want to invalidate this certificate?')">
                            Invalidate
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <!-- Status -->
            <div class="mb-6">
                @if($certificate->is_valid && !$certificate->isExpired())
                    <div class="rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">Valid Certificate</h3>
                                <div class="mt-2 text-sm text-green-700">
                                    <p>This certificate is valid and can be verified.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Invalid Certificate</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>This certificate is no longer valid.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Certificate Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Certificate Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($certificate->type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Title</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->title }}</dd>
                        </div>
                        @if($certificate->description)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->description }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Issue Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->issue_date->format('d F Y') }}</dd>
                        </div>
                        @if($certificate->expiry_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Expiry Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->expiry_date->format('d F Y') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recipient Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Student Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->student->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Student Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->student->email }}</dd>
                        </div>
                        @if($certificate->course)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Course</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->course->name }}</dd>
                        </div>
                        @endif
                        @if($certificate->class)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Class</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->class->name }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Issued By</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->issuer->name }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Additional Metadata -->
            @if($certificate->metadata && count($certificate->metadata) > 0)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($certificate->metadata as $key => $value)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
            @endif

            <!-- Verification Section -->
            <div class="mt-8 border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Verification</h3>
                <div class="flex items-start space-x-4">
                    @if($certificate->qr_code)
                    <div class="flex-shrink-0">
                        <img src="{{ $certificate->qr_code_url }}" alt="QR Code" class="w-32 h-32 border rounded">
                    </div>
                    @endif
                    <div class="flex-1">
                        <p class="text-sm text-gray-600 mb-2">
                            Share this certificate verification link:
                        </p>
                        <div class="flex items-center space-x-2">
                            <input type="text" 
                                   value="{{ $certificate->verification_url }}" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-gray-50"
                                   readonly
                                   id="verification-url">
                            <button onclick="copyToClipboard()" 
                                    class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm">
                                Copy
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Verification Code: <span class="font-mono">{{ $certificate->verification_code }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const input = document.getElementById('verification-url');
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    button.classList.add('bg-green-600');
    
    setTimeout(() => {
        button.textContent = originalText;
        button.classList.remove('bg-green-600');
    }, 2000);
}
</script>
@endsection