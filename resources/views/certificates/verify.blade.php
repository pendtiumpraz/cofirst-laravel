<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification - CoFirst</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <img class="mx-auto h-12 w-auto" src="{{ asset('images/logo.png') }}" alt="CoFirst">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Certificate Verification
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Verify the authenticity of CoFirst certificates
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                @if(isset($error))
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    Certificate Not Found
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>The verification code you entered is invalid or the certificate does not exist.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif(isset($certificate))
                    @if($isValid)
                        <div class="rounded-md bg-green-50 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">
                                        Valid Certificate
                                    </h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p>This is a valid certificate issued by CoFirst.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-md bg-red-50 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        Invalid Certificate
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>This certificate has been invalidated or has expired.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-6">
                        <div class="border rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Certificate Details</h3>
                            
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Certificate Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $certificate->certificate_number }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($certificate->type) }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Recipient</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $certificate->student->name }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Issue Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $certificate->issue_date->format('d F Y') }}</dd>
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
                                
                                @if($certificate->expiry_date)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Expiry Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $certificate->expiry_date->format('d F Y') }}</dd>
                                </div>
                                @endif
                            </dl>
                            
                            @if($certificate->description)
                            <div class="mt-6">
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $certificate->description }}</dd>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mt-8">
                    <form method="GET" action="{{ route('certificate.verify', '') }}" class="space-y-4">
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">
                                Enter Verification Code
                            </label>
                            <div class="mt-1">
                                <input type="text" name="code" id="code" 
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="Enter 12-character verification code"
                                       required>
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" 
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Verify Certificate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Handle verification code from URL
    document.addEventListener('DOMContentLoaded', function() {
        const urlPath = window.location.pathname;
        const parts = urlPath.split('/');
        const code = parts[parts.length - 1];
        
        if (code && code.length === 12) {
            document.getElementById('code').value = code;
        }
    });
    </script>
</body>
</html>