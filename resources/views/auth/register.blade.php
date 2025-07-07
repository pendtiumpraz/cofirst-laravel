<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Register - {{ config('app.name', 'CoFirst') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#3B82F6">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .bg-gradient-coding {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-coding min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-3xl">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Coding First</h1>
            <p class="text-blue-100">Student Registration</p>
        </div>

        <!-- Registration Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100/90 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <!-- Student Information Section -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Student Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Student Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-white mb-2">Student Full Name</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500"
                                placeholder="Enter student name">
                            @error('name')
                                <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Birth Date -->
                        <div>
                            <label for="birth_date" class="block text-sm font-medium text-white mb-2">Birth Date</label>
                            <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date') }}" required
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/90 backdrop-blur-sm text-gray-900">
                            @error('birth_date')
                                <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Gender -->
                        <div>
                            <label for="gender" class="block text-sm font-medium text-white mb-2">Gender</label>
                            <select id="gender" name="gender" required
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/90 backdrop-blur-sm text-gray-900">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-white mb-2">Phone Number</label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500"
                                placeholder="08xxxxxxxxxx">
                            @error('phone')
                                <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="mt-4">
                        <label for="address" class="block text-sm font-medium text-white mb-2">Home Address</label>
                        <textarea id="address" name="address" rows="2" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500"
                            placeholder="Enter complete address">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Parent Information Section -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Parent Information</h3>
                    <div>
                        <label for="parent_name" class="block text-sm font-medium text-white mb-2">Parent Full Name</label>
                        <input id="parent_name" type="text" name="parent_name" value="{{ old('parent_name') }}" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500"
                            placeholder="Enter parent name">
                        @error('parent_name')
                            <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Course Selection -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Select Course</h3>
                    <div>
                        <label for="course_id" class="block text-sm font-medium text-white mb-2">Available Courses</label>
                        <select id="course_id" name="course_id" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/90 backdrop-blur-sm text-gray-900">
                            <option value="">Select a course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }} - {{ ucfirst($course->level) }} - Rp {{ number_format($course->price ?? 0, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                        @enderror
                        @if($courses->isEmpty())
                            <p class="mt-2 text-sm text-red-200">No courses available for registration at this time.</p>
                        @endif
                        <p class="mt-2 text-sm text-blue-100">Class will be automatically assigned based on availability after payment.</p>
                    </div>
                </div>

                <!-- Account Security -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Account Security</h3>
                    <p class="text-sm text-blue-100 mb-4">This password will be used for both student and parent accounts.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-white mb-2">Password</label>
                            <input id="password" type="password" name="password" required autocomplete="new-password"
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500"
                                placeholder="Create a password">
                            @error('password')
                                <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-white mb-2">Confirm Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500"
                                placeholder="Confirm password">
                            @error('password_confirmation')
                                <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Account Info Notice -->
                <div class="bg-white/20 border border-white/30 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-200" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-white">Important Information</h3>
                            <div class="mt-2 text-sm text-blue-100">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Student login: <strong>[student-name]@coding1st.com</strong></li>
                                    <li>Parent login: <strong>parent-[student-name]@coding1st.com</strong></li>
                                    <li>Both accounts will be inactive until payment is confirmed</li>
                                    <li>You will receive login credentials after registration</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-between">
                    <a class="text-sm text-blue-100 hover:text-white underline transition-colors" href="{{ route('login') }}">
                        Already registered? Sign in
                    </a>

                    <button type="submit" 
                        class="px-6 py-3 bg-white text-blue-600 rounded-lg font-semibold hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                        Complete Registration
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-blue-100 text-sm">
                © {{ date('Y') }} Coding First. All rights reserved.
            </p>
        </div>
    </div>

    <!-- Background Animation -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/5 rounded-full animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/5 rounded-full animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-60 h-60 bg-white/3 rounded-full animate-pulse" style="animation-delay: 4s;"></div>
    </div>
</body>
</html>
