<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Registration Successful - {{ config('app.name', 'CoFirst') }}</title>
    
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
        @media print {
            body {
                background: white !important;
            }
            .no-print {
                display: none !important;
            }
            .glass-effect {
                background: white !important;
                border: 1px solid #e5e7eb !important;
            }
            .text-white, .text-blue-100 {
                color: #1f2937 !important;
            }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-coding min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <svg class="w-8 h-8 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Registration Successful!</h1>
            <p class="text-blue-100">{{ session('message') }}</p>
        </div>

        <!-- Account Information -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <h3 class="text-xl font-semibold text-white mb-6">Your Account Information</h3>
            
            <div class="space-y-4">
                <!-- Student Account -->
                <div class="bg-white/20 border border-white/30 rounded-lg p-4">
                    <h4 class="font-medium text-white mb-3">Student Account</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-blue-100">Email:</span>
                            <strong class="text-white">{{ session('student_email') }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-100">Role:</span>
                            <span class="text-white">Student</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-100">Course:</span>
                            <span class="text-white">{{ session('course_name') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-100">Class:</span>
                            <span class="text-white">{{ session('class_name') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Parent Account -->
                <div class="bg-white/20 border border-white/30 rounded-lg p-4">
                    <h4 class="font-medium text-white mb-3">Parent Account</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-blue-100">Email:</span>
                            <strong class="text-white">{{ session('parent_email') }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-100">Role:</span>
                            <span class="text-white">Parent</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Notice -->
            <div class="mt-6 bg-yellow-500/20 border border-yellow-500/30 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-200" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-white">Next Steps</h3>
                        <div class="mt-2 text-sm text-yellow-100">
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Please make payment according to the selected course fee</li>
                                <li>Contact admin with proof of payment</li>
                                <li>Admin will activate your accounts after payment verification</li>
                                <li>You will be notified when your accounts are activated</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex items-center justify-center space-x-4 no-print">
                <a href="{{ route('landing') }}" 
                    class="px-4 py-2 glass-effect text-white font-medium rounded-lg hover:bg-white/30 transition-all">
                    Back to Home
                </a>
                <button onclick="window.print()" 
                    class="px-4 py-2 bg-white text-blue-600 font-medium rounded-lg hover:bg-blue-50 transition-all">
                    Print This Page
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 no-print">
            <p class="text-blue-100 text-sm">
                © {{ date('Y') }} Coding First. All rights reserved.
            </p>
        </div>
    </div>

    <!-- Background Animation -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none no-print">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/5 rounded-full animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/5 rounded-full animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-60 h-60 bg-white/3 rounded-full animate-pulse" style="animation-delay: 4s;"></div>
    </div>
</body>
</html>