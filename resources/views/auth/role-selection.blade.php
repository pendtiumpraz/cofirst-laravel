<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Pilih Role - {{ config('app.name', 'Coding First') }}</title>
    
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
        .role-card {
            transition: all 0.3s ease;
        }
        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-coding min-h-screen">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="w-full sm:max-w-2xl mt-6 px-6 py-8 glass-effect shadow-md overflow-hidden sm:rounded-lg">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Selamat Datang, {{ Auth::user()->name }}!</h1>
                <p class="text-white/80 text-lg">Pilih cara Anda ingin menggunakan sistem hari ini</p>
            </div>

            <!-- Role Selection Form -->
            <form method="POST" action="{{ route('role.set') }}">
                @csrf
                
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Parent Role Card -->
                    <div class="role-card bg-white rounded-xl p-6 cursor-pointer border-2 border-transparent hover:border-blue-500 transition-all duration-300" onclick="selectRole('parent')">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">Masuk sebagai Orang Tua</h3>
                            <p class="text-gray-600 text-sm mb-4">Monitor progress belajar anak, lihat laporan guru, dan pantau perkembangan akademik</p>
                            <div class="text-left text-sm text-gray-500">
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Lihat progress anak
                                </div>
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Monitor materi yang dipelajari
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Lihat laporan dari guru
                                </div>
                            </div>
                        </div>
                        <input type="radio" name="role" value="parent" class="hidden" id="parent-role">
                    </div>

                    <!-- Student Role Card -->
                    <div class="role-card bg-white rounded-xl p-6 cursor-pointer border-2 border-transparent hover:border-green-500 transition-all duration-300" onclick="selectRole('student')">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">Masuk sebagai Murid</h3>
                            <p class="text-gray-600 text-sm mb-4">Akses materi pembelajaran, lihat jadwal kelas, dan pantau progress belajar Anda</p>
                            <div class="text-left text-sm text-gray-500">
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Akses materi pembelajaran
                                </div>
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Lihat jadwal kelas
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Track progress belajar
                                </div>
                            </div>
                        </div>
                        <input type="radio" name="role" value="student" class="hidden" id="student-role">
                    </div>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Submit Button -->
                <div class="mt-8 text-center">
                    <button type="submit" id="submit-btn" class="bg-white text-purple-600 font-semibold py-3 px-8 rounded-lg shadow-lg hover:bg-gray-50 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Lanjutkan
                    </button>
                </div>
            </form>

            <!-- Switch Role Later -->
            <div class="mt-6 text-center">
                <p class="text-white/70 text-sm">
                    Anda dapat mengganti role kapan saja dari menu profil
                </p>
            </div>
        </div>
    </div>

    <script>
        function selectRole(role) {
            // Remove previous selections
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('border-blue-500', 'border-green-500', 'ring-2', 'ring-blue-200', 'ring-green-200');
            });
            
            // Remove checked state from all radio buttons
            document.querySelectorAll('input[name="role"]').forEach(radio => {
                radio.checked = false;
            });
            
            // Select the clicked role
            const selectedCard = event.currentTarget;
            const radioButton = selectedCard.querySelector('input[name="role"]');
            
            radioButton.checked = true;
            
            if (role === 'parent') {
                selectedCard.classList.add('border-blue-500', 'ring-2', 'ring-blue-200');
            } else {
                selectedCard.classList.add('border-green-500', 'ring-2', 'ring-green-200');
            }
            
            // Enable submit button
            document.getElementById('submit-btn').disabled = false;
        }
    </script>
</body>
</html>