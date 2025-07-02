<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Classes - Simple Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">Student Classes - Simple Test</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-2xl font-bold text-blue-600 mb-6">CSS Test</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="bg-red-500 text-white p-6 rounded-lg">
                                <h3 class="text-xl font-bold">Red Card</h3>
                                <p>This should be RED if CSS works</p>
                            </div>
                            <div class="bg-green-500 text-white p-6 rounded-lg">
                                <h3 class="text-xl font-bold">Green Card</h3>
                                <p>This should be GREEN if CSS works</p>
                            </div>
                            <div class="bg-blue-500 text-white p-6 rounded-lg">
                                <h3 class="text-xl font-bold">Blue Card</h3>
                                <p>This should be BLUE if CSS works</p>
                            </div>
                        </div>

                        <h2 class="text-2xl font-bold text-gray-800 mb-6">My Classes</h2>
                        
                        @if(isset($enrollments) && $enrollments->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($enrollments as $enrollment)
                                    @php $class = $enrollment->class @endphp
                                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $class->name }}</h3>
                                        <p class="text-sm text-gray-600 mb-2">{{ $class->course->name ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-500">Teacher: {{ $class->teacher->name ?? 'N/A' }}</p>
                                        <span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 mt-2">
                                            {{ ucfirst($enrollment->status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="text-gray-500 mb-4">
                                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">No classes found</h3>
                                <p class="text-gray-500">You are not enrolled in any classes yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 