<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Lembaga Pelatihan Programming Terpercaya</title>
    <meta name="description" content="Coding First adalah lembaga pelatihan programming dengan pengajar berpengalaman dari perusahaan Jepang. Belajar coding dari praktisi profesional.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        
        .section {
            height: 100vh;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .scroll-indicator {
            position: fixed;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 50;
        }
        
        .scroll-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            margin: 8px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .scroll-dot.active {
            background: white;
            transform: scale(1.2);
        }
        
        .floating-nav {
            position: fixed;
            top: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 50;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 0.5rem 1rem;
        }
        
        .parallax-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 120%;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: -1;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .glass-effect {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .scroll-down {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
            40% { transform: translateX(-50%) translateY(-10px); }
            60% { transform: translateX(-50%) translateY(-5px); }
        }
    </style>
</head>
<body class="antialiased">
    <!-- Floating Navigation -->
    <nav class="floating-nav">
        <div class="flex items-center space-x-6">
            <div class="text-white font-bold text-lg">Coding First</div>
            <div class="hidden md:flex items-center space-x-4">
                <a href="#hero" class="text-white/80 hover:text-white transition-colors text-sm">Home</a>
                <a href="#about" class="text-white/80 hover:text-white transition-colors text-sm">About</a>
                <a href="#courses" class="text-white/80 hover:text-white transition-colors text-sm">Courses</a>
                <a href="#contact" class="text-white/80 hover:text-white transition-colors text-sm">Contact</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-white/20 text-white px-4 py-2 rounded-full hover:bg-white/30 transition-colors text-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-white/80 hover:text-white transition-colors text-sm">Login</a>
                    <a href="{{ route('register') }}" class="bg-white/20 text-white px-4 py-2 rounded-full hover:bg-white/30 transition-colors text-sm">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <div class="scroll-dot active" data-section="hero"></div>
        <div class="scroll-dot" data-section="about"></div>
        <div class="scroll-dot" data-section="courses"></div>
        <div class="scroll-dot" data-section="stats"></div>
        <div class="scroll-dot" data-section="contact"></div>
    </div>

    <!-- Hero Section -->
    <section id="hero" class="section bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 relative overflow-hidden">
        <div class="parallax-bg bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900"></div>
        
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-4 -right-4 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float"></div>
            <div class="absolute -bottom-8 -left-4 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float" style="animation-delay: 2s;"></div>
            <div class="absolute top-1/2 left-1/2 w-72 h-72 bg-indigo-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float" style="animation-delay: 4s;"></div>
        </div>
        
        <div class="container mx-auto px-6 text-center relative z-10">
            <h1 class="text-6xl md:text-8xl font-bold text-white mb-6 leading-tight">
                Coding <span class="gradient-text">First</span>
            </h1>
            <p class="text-xl md:text-2xl text-white/80 mb-8 max-w-3xl mx-auto">
                Belajar Programming dari Praktisi Profesional Jepang
            </p>
            <p class="text-lg text-white/60 mb-12 max-w-2xl mx-auto">
                Dapatkan skill programming yang dibutuhkan industri global dengan pengajar berpengalaman internasional
            </p>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="#courses" class="glass-effect text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white/20 transition-all transform hover:scale-105">
                    Explore Courses
                </a>
                <a href="#about" class="border-2 border-white/30 text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white/10 transition-all transform hover:scale-105">
                    Learn More
                </a>
            </div>
        </div>
        
        <div class="scroll-down">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section bg-gradient-to-br from-gray-900 via-gray-800 to-black relative">
        <div class="container mx-auto px-6 relative z-10">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-5xl md:text-6xl font-bold text-white mb-8">
                        Why <span class="gradient-text">Choose Us?</span>
                    </h2>
                    <p class="text-xl text-white/80 mb-8">
                        Kami berkomitmen memberikan pendidikan programming terbaik dengan standar internasional dari pengajar yang berpengalaman di perusahaan Jepang.
                    </p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white mb-2">Expert Instructors</h3>
                                <p class="text-white/70">Professional programmers dari perusahaan Jepang terkemuka</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white mb-2">Modern Curriculum</h3>
                                <p class="text-white/70">Kurikulum selalu update sesuai kebutuhan industri terkini</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white mb-2">Personal Mentoring</h3>
                                <p class="text-white/70">Bimbingan personal untuk memastikan pemahaman optimal</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="glass-effect rounded-3xl p-8 text-center">
                        <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mx-auto mb-6 flex items-center justify-center">
                            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                            </svg>
                        </div>
                        <h3 class="text-3xl font-bold text-white mb-4">1000+ Graduates</h3>
                        <p class="text-white/80">Berhasil berkarir di perusahaan teknologi terkemuka</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Courses Section -->
    <section id="courses" class="section bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 relative">
        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-5xl md:text-6xl font-bold text-white mb-6">
                    Our <span class="gradient-text">Courses</span>
                </h2>
                <p class="text-xl text-white/80 max-w-3xl mx-auto">
                    Pilih program yang sesuai dengan minat dan level kemampuan Anda
                </p>
            </div>
            
            @if($featuredCourses->count() > 0)
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($featuredCourses as $course)
                        <div class="glass-effect rounded-2xl p-6 hover:bg-white/20 transition-all transform hover:scale-105">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl mb-6 flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                                </svg>
                            </div>
                            
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-3 py-1 bg-white/20 text-white text-sm font-medium rounded-full">
                                    {{ ucfirst($course->level) }}
                                </span>
                                <span class="text-2xl font-bold text-white">{{ $course->formatted_price }}</span>
                            </div>
                            
                            <h3 class="text-xl font-bold text-white mb-3">{{ $course->name }}</h3>
                            <p class="text-white/70 mb-6">{{ Str::limit($course->description, 100) }}</p>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-white/60">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $course->duration_weeks }} weeks
                                </span>
                                <a href="#" class="text-white hover:text-white/80 font-medium inline-flex items-center">
                                    Learn More
                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="glass-effect rounded-2xl p-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl mb-6 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Web Development</h3>
                        <p class="text-white/70 mb-6">Full-stack web development dengan React, Node.js, dan database modern</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-white/60">12 weeks</span>
                            <span class="text-2xl font-bold text-white">Rp 2.500.000</span>
                        </div>
                    </div>
                    
                    <div class="glass-effect rounded-2xl p-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-blue-600 rounded-2xl mb-6 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Mobile Development</h3>
                        <p class="text-white/70 mb-6">Pengembangan aplikasi mobile dengan React Native dan Flutter</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-white/60">10 weeks</span>
                            <span class="text-2xl font-bold text-white">Rp 2.000.000</span>
                        </div>
                    </div>
                    
                    <div class="glass-effect rounded-2xl p-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl mb-6 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Data Science</h3>
                        <p class="text-white/70 mb-6">Analisis data dan machine learning dengan Python dan R</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-white/60">16 weeks</span>
                            <span class="text-2xl font-bold text-white">Rp 3.000.000</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Statistics Section -->
    <section id="stats" class="section bg-gradient-to-br from-gray-900 to-black relative">
        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-5xl md:text-6xl font-bold text-white mb-6">
                    Our <span class="gradient-text">Impact</span>
                </h2>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-6xl font-bold text-blue-400 mb-4">1000+</div>
                    <div class="text-white/80 text-lg">Active Students</div>
                </div>
                <div class="text-center">
                    <div class="text-6xl font-bold text-green-400 mb-4">50+</div>
                    <div class="text-white/80 text-lg">Expert Instructors</div>
                </div>
                <div class="text-center">
                    <div class="text-6xl font-bold text-purple-400 mb-4">95%</div>
                    <div class="text-white/80 text-lg">Success Rate</div>
                </div>
                <div class="text-center">
                    <div class="text-6xl font-bold text-orange-400 mb-4">20+</div>
                    <div class="text-white/80 text-lg">Partner Companies</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section bg-gradient-to-br from-blue-900 via-purple-900 to-indigo-900 relative">
        <div class="container mx-auto px-6 text-center relative z-10">
            <h2 class="text-5xl md:text-6xl font-bold text-white mb-8">
                Ready to <span class="gradient-text">Start?</span>
            </h2>
            <p class="text-xl text-white/80 mb-12 max-w-2xl mx-auto">
                Bergabunglah dengan ribuan siswa yang telah berhasil mengembangkan karir di bidang teknologi
            </p>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="glass-effect text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white/20 transition-all transform hover:scale-105">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="glass-effect text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white/20 transition-all transform hover:scale-105">
                        Join Now
                    </a>
                    <a href="{{ route('login') }}" class="border-2 border-white/30 text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white/10 transition-all transform hover:scale-105">
                        Sign In
                    </a>
                @endauth
            </div>
            
            <div class="mt-16 grid md:grid-cols-3 gap-8">
                <div class="glass-effect rounded-2xl p-6">
                    <div class="w-12 h-12 bg-blue-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Email</h3>
                    <p class="text-white/70">info@codingfirst.id</p>
                </div>
                
                <div class="glass-effect rounded-2xl p-6">
                    <div class="w-12 h-12 bg-green-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Phone</h3>
                    <p class="text-white/70">+62 812-3456-7890</p>
                </div>
                
                <div class="glass-effect rounded-2xl p-6">
                    <div class="w-12 h-12 bg-purple-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Address</h3>
                    <p class="text-white/70">Jakarta, Indonesia</p>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript for smooth scrolling and interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Scroll indicator functionality
            const sections = document.querySelectorAll('.section');
            const scrollDots = document.querySelectorAll('.scroll-dot');

            function updateActiveSection() {
                const scrollPosition = window.scrollY + window.innerHeight / 2;

                sections.forEach((section, index) => {
                    const sectionTop = section.offsetTop;
                    const sectionBottom = sectionTop + section.offsetHeight;

                    if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                        scrollDots.forEach(dot => dot.classList.remove('active'));
                        scrollDots[index].classList.add('active');
                    }
                });
            }

            // Scroll dot click handlers
            scrollDots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    sections[index].scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });

            // Update active section on scroll
            window.addEventListener('scroll', updateActiveSection);
            
            // Initialize
            updateActiveSection();
        });
    </script>
</body>
</html> 