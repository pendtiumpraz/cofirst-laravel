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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 3rem 0;
        }
        
        @media (min-width: 768px) {
            .section {
                padding: 4rem 0;
            }
        }
        
        .gradient-text {
            background: linear-gradient(to right, #60A5FA, #A78BFA, #F472B6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-black text-white">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 glass-effect">
        <div class="container mx-auto px-4 sm:px-6 py-3 sm:py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg sm:text-xl">CF</span>
                    </div>
                    <span class="text-lg sm:text-xl font-bold text-white">Coding First</span>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#home" class="text-white/80 hover:text-white transition">Home</a>
                    <a href="#courses" class="text-white/80 hover:text-white transition">Courses</a>
                    <a href="#teachers" class="text-white/80 hover:text-white transition">Teachers</a>
                    <a href="#students" class="text-white/80 hover:text-white transition">Students</a>
                    <a href="#testimonials" class="text-white/80 hover:text-white transition">Testimonials</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-purple-700 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-white/80 hover:text-white transition">Login</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-purple-700 transition">
                            Register
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden glass-effect">
            <div class="px-6 pt-2 pb-4 space-y-2">
                <a href="#home" class="block py-2 text-white/80 hover:text-white transition">Home</a>
                <a href="#courses" class="block py-2 text-white/80 hover:text-white transition">Courses</a>
                <a href="#teachers" class="block py-2 text-white/80 hover:text-white transition">Teachers</a>
                <a href="#students" class="block py-2 text-white/80 hover:text-white transition">Students</a>
                <a href="#testimonials" class="block py-2 text-white/80 hover:text-white transition">Testimonials</a>
                <div class="pt-4 border-t border-white/20">
                    @auth
                        <a href="{{ route('dashboard') }}" class="block py-2 px-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-purple-700 transition text-center">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="block py-2 text-white/80 hover:text-white transition">Login</a>
                        <a href="{{ route('register') }}" class="block py-2 px-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-purple-700 transition text-center mt-2">
                            Register
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="section bg-gradient-to-br from-blue-900 via-purple-900 to-indigo-900 relative">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="container mx-auto px-4 sm:px-6 relative z-10">
            <div class="text-center">
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-8xl font-bold mb-6">
                    Learn to <span class="gradient-text">Code</span>
                    <span class="block text-2xl sm:text-3xl md:text-4xl lg:text-6xl mt-4">From Industry Experts</span>
                </h1>
                <p class="text-lg sm:text-xl md:text-2xl text-white/80 mb-8 sm:mb-12 max-w-3xl mx-auto px-4">
                    Bergabunglah dengan Coding First dan raih karir impian Anda di dunia teknologi. 
                    Belajar langsung dari praktisi dengan pengalaman perusahaan multinasional.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center px-4">
                    <a href="#courses" class="px-6 sm:px-8 py-3 sm:py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all transform hover:scale-105">
                        Explore Courses
                    </a>
                    <a href="{{ route('register') }}" class="px-6 sm:px-8 py-3 sm:py-4 glass-effect text-white font-semibold rounded-lg hover:bg-white/20 transition-all transform hover:scale-105">
                        Get Started Free
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Courses Section -->
    <section id="courses" class="section bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 relative">
        <div class="container mx-auto px-4 sm:px-6 relative z-10">
            <div class="text-center mb-12 sm:mb-16">
                <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 sm:mb-6">
                    Our <span class="gradient-text">Courses</span>
                </h2>
                <p class="text-lg sm:text-xl text-white/80 max-w-3xl mx-auto px-4">
                    Pilih program yang sesuai dengan minat dan level kemampuan Anda
                </p>
            </div>
            
            @if($featuredCourses->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
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
                <div class="text-center">
                    <p class="text-white/60 text-lg">No courses available at the moment.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Teachers Section -->
    <section id="teachers" class="section bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 relative">
        <div class="container mx-auto px-4 sm:px-6 relative z-10">
            <div class="text-center mb-12 sm:mb-16">
                <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 sm:mb-6">
                    Expert <span class="gradient-text">Instructors</span>
                </h2>
                <p class="text-lg sm:text-xl text-white/80 max-w-3xl mx-auto px-4">
                    Belajar langsung dari praktisi profesional dengan pengalaman industri
                </p>
            </div>
            
            @if($featuredTeachers->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($featuredTeachers as $teacher)
                        <div class="glass-effect rounded-2xl p-6 text-center hover:bg-white/20 transition-all transform hover:scale-105">
                            <div class="mb-4">
                                @if($teacher->profile_photo_path)
                                    <img src="{{ $teacher->profile_photo_url }}" alt="{{ $teacher->name }}" 
                                         class="w-24 h-24 rounded-full mx-auto object-cover border-4 border-white/20">
                                @else
                                    <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mx-auto flex items-center justify-center">
                                        <span class="text-white text-3xl font-bold">{{ substr($teacher->name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <h3 class="text-lg font-bold text-white mb-1">{{ $teacher->name }}</h3>
                            @if($teacher->profile && $teacher->profile->specialization)
                                <p class="text-sm text-white/70 mb-3">{{ $teacher->profile->specialization }}</p>
                            @else
                                <p class="text-sm text-white/70 mb-3">Expert Developer</p>
                            @endif
                            <div class="flex justify-center space-x-2">
                                @for($i = 0; $i < 5; $i++)
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- Top Students Section -->
    <section id="students" class="section bg-gradient-to-br from-blue-900 via-teal-900 to-green-900 relative">
        <div class="container mx-auto px-4 sm:px-6 relative z-10">
            <div class="text-center mb-12 sm:mb-16">
                <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 sm:mb-6">
                    Top <span class="gradient-text">Students</span>
                </h2>
                <p class="text-lg sm:text-xl text-white/80 max-w-3xl mx-auto px-4">
                    Siswa-siswa berprestasi dengan dedikasi tinggi dalam pembelajaran
                </p>
            </div>
            
            @if($topStudents->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($topStudents as $student)
                        <div class="glass-effect rounded-2xl p-6 hover:bg-white/20 transition-all transform hover:scale-105">
                            <div class="flex items-center mb-4">
                                @if($student->profile_photo_path)
                                    <img src="{{ $student->profile_photo_url }}" alt="{{ $student->name }}" 
                                         class="w-16 h-16 rounded-full object-cover border-2 border-white/20">
                                @else
                                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xl font-bold">{{ substr($student->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-bold text-white">{{ $student->name }}</h3>
                                    @if($student->points)
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-white/70">Level {{ $student->points->level }}</span>
                                            <span class="text-sm text-yellow-400 font-medium">{{ number_format($student->points->total_earned) }} pts</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @if($student->featuredBadges && $student->featuredBadges->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($student->featuredBadges->take(3) as $badge)
                                        <span class="px-2 py-1 bg-white/20 text-white text-xs font-medium rounded-full">
                                            {{ $badge->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- Parent Testimonials Section -->
    <section id="testimonials" class="section bg-gradient-to-br from-green-900 via-teal-900 to-blue-900 relative">
        <div class="container mx-auto px-4 sm:px-6 relative z-10">
            <div class="text-center mb-12 sm:mb-16">
                <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 sm:mb-6">
                    Parent <span class="gradient-text">Testimonials</span>
                </h2>
                <p class="text-lg sm:text-xl text-white/80 max-w-3xl mx-auto px-4">
                    Apa kata orang tua tentang perkembangan anak-anak mereka di Coding First
                </p>
            </div>
            
            @if($testimonials->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($testimonials as $testimonial)
                        <div class="glass-effect rounded-2xl p-8 hover:bg-white/20 transition-all">
                            <div class="flex mb-4">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-white/30' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                            </div>
                            
                            <h3 class="text-xl font-bold text-white mb-3">{{ $testimonial->title }}</h3>
                            <p class="text-white/80 mb-6 italic">"{{ $testimonial->content }}"</p>
                            
                            <div class="flex items-center">
                                @if($testimonial->user->profile_photo_path)
                                    <img src="{{ $testimonial->user->profile_photo_url }}" alt="{{ $testimonial->user->name }}" 
                                         class="w-12 h-12 rounded-full object-cover border-2 border-white/20">
                                @else
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold">{{ substr($testimonial->user->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div class="ml-3">
                                    <p class="text-white font-medium">{{ $testimonial->user->name }}</p>
                                    @if($testimonial->child_name)
                                        <p class="text-sm text-white/60">Orang tua dari {{ $testimonial->child_name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-white/60 text-lg">Testimonials coming soon...</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="section bg-gradient-to-br from-gray-900 to-black relative">
        <div class="container mx-auto px-4 sm:px-6 relative z-10">
            <div class="text-center mb-12 sm:mb-16">
                <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 sm:mb-6">
                    Our <span class="gradient-text">Impact</span>
                </h2>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8">
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-blue-400 mb-2 sm:mb-4">1000+</div>
                    <div class="text-white/80 text-sm sm:text-base md:text-lg">Active Students</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-green-400 mb-2 sm:mb-4">50+</div>
                    <div class="text-white/80 text-sm sm:text-base md:text-lg">Expert Instructors</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-purple-400 mb-2 sm:mb-4">95%</div>
                    <div class="text-white/80 text-sm sm:text-base md:text-lg">Success Rate</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-orange-400 mb-2 sm:mb-4">20+</div>
                    <div class="text-white/80 text-sm sm:text-base md:text-lg">Partner Companies</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section bg-gradient-to-br from-purple-900 via-pink-900 to-red-900">
        <div class="container mx-auto px-4 sm:px-6 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-7xl font-bold text-white mb-6 sm:mb-8">
                Ready to Start Your
                <span class="block gradient-text">Coding Journey?</span>
            </h2>
            <p class="text-lg sm:text-xl text-white/80 mb-8 sm:mb-12 max-w-3xl mx-auto px-4">
                Bergabunglah dengan ribuan siswa yang telah sukses mengubah karir mereka.
                Dapatkan konsultasi gratis dengan expert kami sekarang.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center px-4">
                <a href="{{ route('register') }}" class="px-6 sm:px-8 py-3 sm:py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all transform hover:scale-105">
                    Start Learning Now
                </a>
                <a href="#" class="px-6 sm:px-8 py-3 sm:py-4 bg-white/10 backdrop-blur-md text-white font-semibold rounded-lg hover:bg-white/20 transition-all transform hover:scale-105">
                    Schedule Consultation
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black/80 py-8 sm:py-12">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xl">CF</span>
                    </div>
                    <span class="text-lg sm:text-xl font-bold text-white">Coding First</span>
                </div>
                <p class="text-sm sm:text-base text-white/60">© 2024 Coding First. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript for smooth scrolling and mobile menu -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuIcon = mobileMenuButton.querySelector('svg');
            
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
                
                // Toggle icon between hamburger and X
                if (mobileMenu.classList.contains('hidden')) {
                    mobileMenuIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    `;
                } else {
                    mobileMenuIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    `;
                }
            });
            
            // Close mobile menu when clicking on links
            document.querySelectorAll('#mobile-menu a').forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.add('hidden');
                    mobileMenuIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    `;
                });
            });
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
                    mobileMenu.classList.add('hidden');
                    mobileMenuIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    `;
                }
            });
            
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
        });
    </script>
</body>
</html>