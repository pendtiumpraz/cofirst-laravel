<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Login - {{ config('app.name', 'Coding First') }}</title>
    
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
    <div class="w-full max-w-md">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Coding First</h1>
            <p class="text-blue-100">Programming Training Institution</p>
        </div>

        <!-- Login Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-white mb-2">Welcome Back!</h2>
                <p class="text-blue-100">Please sign in to your account</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-white mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500"
                               placeholder="Enter your email">
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-white mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/90 backdrop-blur-sm text-gray-900 placeholder-gray-500"
                               placeholder="Enter your password">
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label for="remember_me" class="flex items-center">
                        <input id="remember_me" 
                               type="checkbox" 
                               name="remember"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-white">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-200 hover:text-white transition-colors">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-white text-blue-600 py-3 px-4 rounded-lg font-semibold hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                    Sign In
                </button>
            </form>

            <!-- Demo Accounts -->
            <div class="mt-8 pt-6 border-t border-white/20">
                <p class="text-sm text-blue-100 text-center mb-4">Demo Accounts (Click to auto-fill):</p>
                <div class="grid grid-cols-2 gap-2 text-xs text-blue-100">
                    <div class="bg-white/10 rounded p-2 cursor-pointer hover:bg-white/20 transition-colors demo-account" 
                         data-email="superadmin@codingfirst.com" data-password="password">
                        <div class="font-medium">Super Admin</div>
                        <div>superadmin@codingfirst.com</div>
                        <div>password</div>
                    </div>
                    <div class="bg-white/10 rounded p-2 cursor-pointer hover:bg-white/20 transition-colors demo-account" 
                         data-email="admin@codingfirst.com" data-password="password">
                        <div class="font-medium">Admin</div>
                        <div>admin@codingfirst.com</div>
                        <div>password</div>
                    </div>
                    <div class="bg-white/10 rounded p-2 cursor-pointer hover:bg-white/20 transition-colors demo-account" 
                         data-email="teacher@codingfirst.com" data-password="password">
                        <div class="font-medium">Teacher</div>
                        <div>teacher@codingfirst.com</div>
                        <div>password</div>
                    </div>
                    <div class="bg-white/10 rounded p-2 cursor-pointer hover:bg-white/20 transition-colors demo-account" 
                         data-email="parent@codingfirst.com" data-password="password">
                        <div class="font-medium">Parent</div>
                        <div>parent@codingfirst.com</div>
                        <div>password</div>
                    </div>
                    <div class="bg-white/10 rounded p-2 cursor-pointer hover:bg-white/20 transition-colors demo-account" 
                         data-email="student@codingfirst.com" data-password="password">
                        <div class="font-medium">Student</div>
                        <div>student@codingfirst.com</div>
                        <div>password</div>
                    </div>
                    <div class="bg-white/10 rounded p-2 cursor-pointer hover:bg-white/20 transition-colors demo-account" 
                         data-email="finance@codingfirst.com" data-password="password">
                        <div class="font-medium">Finance</div>
                        <div>finance@codingfirst.com</div>
                        <div>password</div>
                    </div>
                </div>
            </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const demoAccounts = document.querySelectorAll('.demo-account');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            demoAccounts.forEach(account => {
                account.addEventListener('click', function() {
                    const email = this.getAttribute('data-email');
                    const password = this.getAttribute('data-password');
                    
                    emailInput.value = email;
                    passwordInput.value = password;
                    
                    // Add visual feedback
                    this.style.background = 'rgba(255, 255, 255, 0.3)';
                    setTimeout(() => {
                        this.style.background = 'rgba(255, 255, 255, 0.1)';
                    }, 200);
                });
            });
        });
    </script>
</body>
</html>