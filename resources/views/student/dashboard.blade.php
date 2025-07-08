<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Student Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-white">
                    <h1 class="text-2xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}!</h1>
                    <p class="text-blue-100">Mari kita mulai perjalanan belajar coding hari ini.</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Enrolled Courses</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['enrolled_courses'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Classes Today</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['classes_today'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Attendance Rate</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['attendance_rate'] }}%</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Available Classes</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['available_classes'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Enrolled Classes Section -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Kelas Saya</h2>
                    <a href="{{ route('student.classes') }}" class="text-blue-600 hover:text-blue-800 font-medium">Lihat Semua</a>
                </div>
                
                @if($enrollments->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($enrollments as $enrollment)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                            Aktif
                                        </span>
                                        <span class="text-lg font-bold text-blue-600">
                                            {{ $enrollment->class->course->formatted_price }}
                                        </span>
                                    </div>
                                    
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        {{ $enrollment->class->course->name }}
                                    </h3>
                                    
                                    <p class="text-sm text-gray-600 mb-2">
                                        <strong>Kelas:</strong> {{ $enrollment->class->name }}
                                    </p>
                                    
                                    <p class="text-sm text-gray-600 mb-4">
                                        <strong>Pengajar:</strong> {{ $enrollment->class->teachers->first()->name ?? 'N/A' }}
                                    </p>
                                    
                                    <div class="flex space-x-2">
                                        <a href="{{ route('student.curriculum.show', $enrollment->class->id) }}" 
                                           class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                                            Lihat Materi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Kelas</h3>
                        <p class="text-gray-600">Anda belum terdaftar di kelas manapun.</p>
                    </div>
                @endif
            </div>

            <!-- Available Classes Section -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Kelas yang Tersedia</h2>
                </div>
                
                @if($availableClasses->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($availableClasses->take(6) as $class)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                            Tersedia
                                        </span>
                                        <span class="text-lg font-bold text-green-600">
                                            {{ $class->course->formatted_price }}
                                        </span>
                                    </div>
                                    
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        {{ $class->course->name }}
                                    </h3>
                                    
                                    <p class="text-sm text-gray-600 mb-2">
                                        <strong>Kelas:</strong> {{ $class->name }}
                                    </p>
                                    
                                    <p class="text-sm text-gray-600 mb-2">
                                        <strong>Pengajar:</strong> {{ $class->teachers->first()->name ?? 'N/A' }}
                                    </p>
                                    
                                    <p class="text-sm text-gray-600 mb-2">
                                        <strong>Level:</strong> {{ ucfirst($class->course->level) }}
                                    </p>
                                    
                                    <p class="text-sm text-gray-600 mb-4">
                                        <strong>Durasi:</strong> {{ $class->course->duration_weeks }} minggu
                                    </p>
                                    
                                    <div class="flex space-x-2">
                                        <button class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-md text-sm font-medium hover:bg-green-700 transition-colors"
                                                onclick="alert('Fitur pendaftaran akan segera tersedia!')">
                                            Daftar Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Kelas Tersedia</h3>
                        <p class="text-gray-600">Saat ini tidak ada kelas yang tersedia untuk didaftarkan.</p>
                    </div>
                @endif
            </div>

            <!-- Schedule Calendar -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Jadwal Kelas 7 Hari Ke Depan</h3>
                    <x-schedule-calendar :schedules="$schedules" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Today's Schedule -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Schedule</h3>
                        <div class="space-y-4">
                            @forelse($todaySchedules ?? [] as $schedule)
                                <div class="border-l-4 border-blue-500 pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $schedule->className->name }}</h4>
                                            <p class="text-sm text-gray-600">{{ $schedule->title ?? 'Regular Class' }}</p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM1.49 15.326a.78.78 0 01-.358-.442 3 3 0 014.308-3.516 6.484 6.484 0 00-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 01-2.07-.655zM16.44 15.98a4.97 4.97 0 002.07-.654.78.78 0 00.357-.442 3 3 0 00-4.308-3.517 6.484 6.484 0 011.907 3.96 2.32 2.32 0 01-.026.654z"/>
                                                </svg>
                                                Teacher: {{ $schedule->teacherAssignment->teacher->name ?? 'N/A' }}
                                            </p>
                                        </div>
                                        @if($schedule->meeting_link)
                                            <a href="{{ $schedule->meeting_link }}" target="_blank" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Join Class
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="mt-2">No classes scheduled for today</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <div class="mt-6">
                            <a href="{{ route('student.schedules.index') }}" class="text-sm text-blue-600 hover:text-blue-900">View full schedule →</a>
                        </div>
                    </div>
                </div>

                <!-- Recent Progress & Announcements -->
                <div class="space-y-6">
                    <!-- Progress Overview -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Progress</h3>
                            <div class="space-y-4">
                                @forelse($enrollments ?? [] as $enrollment)
                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-sm font-medium text-gray-700">{{ $enrollment->class->course->name }}</span>
                                            <span class="text-sm text-gray-500">{{ $enrollment->progress ?? 0 }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $enrollment->progress ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No courses enrolled yet</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Recent Announcements -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Announcements</h3>
                            <div class="space-y-3">
                                @forelse($announcements ?? [] as $announcement)
                                    <div class="border-l-4 border-yellow-400 pl-3">
                                        <h4 class="font-medium text-gray-900">{{ $announcement->title }}</h4>
                                        <p class="text-sm text-gray-600">{{ Str::limit($announcement->content, 100) }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $announcement->created_at->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No new announcements</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Courses -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">My Courses</h3>
                        <a href="{{ route('student.courses.index') }}" class="text-sm text-blue-600 hover:text-blue-900">Browse more courses</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @forelse($myCourses ?? [] as $enrollment)
                            <div class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                @if($enrollment->class->course->thumbnail)
                                    <img src="{{ $enrollment->class->course->thumbnail }}" alt="{{ $enrollment->class->course->name }}" class="w-full h-32 object-cover">
                                @else
                                    <div class="w-full h-32 bg-gradient-to-br from-blue-400 to-purple-500"></div>
                                @endif
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-900">{{ $enrollment->class->course->name }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $enrollment->class->name }}</p>
                                    <div class="mt-3 flex justify-between items-center">
                                        <span class="text-xs text-gray-500">Progress: {{ $enrollment->progress ?? 0 }}%</span>
                                        <a href="{{ route('student.materials.index', ['course' => $enrollment->class->course]) }}" class="text-sm text-blue-600 hover:text-blue-900">
                                            View Materials →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-8">
                                <p class="text-gray-500">You haven't enrolled in any courses yet.</p>
                                <a href="{{ route('student.courses.index') }}" class="mt-2 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Browse Courses
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>