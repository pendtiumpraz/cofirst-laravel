<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Orang Tua
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-500 bg-opacity-75 rounded-full">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">{{ $stats['total_children'] }}</h3>
                                <p class="text-sm text-gray-600">Anak Terdaftar</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-500 bg-opacity-75 rounded-full">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">{{ $stats['active_classes'] }}</h3>
                                <p class="text-sm text-gray-600">Kelas Aktif</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-orange-500 bg-opacity-75 rounded-full">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">{{ $stats['available_classes'] }}</h3>
                                <p class="text-sm text-gray-600">Kelas Tersedia</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-500 bg-opacity-75 rounded-full">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</h3>
                                <p class="text-sm text-gray-600">Total Pengeluaran</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchased Classes Section -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Kelas yang Telah Dibeli</h2>
                </div>
                
                @if($purchasedClasses->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($purchasedClasses as $item)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                            Aktif
                                        </span>
                                        <span class="text-lg font-bold text-blue-600">
                                            {{ $item['class']->course->formatted_price }}
                                        </span>
                                    </div>
                                    
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        {{ $item['class']->course->name }}
                                    </h3>
                                    
                                    <p class="text-sm text-gray-600 mb-2">
                                        <strong>Kelas:</strong> {{ $item['class']->name }}
                                    </p>
                                    
                                    <p class="text-sm text-gray-600 mb-2">
                                        <strong>Anak:</strong> {{ $item['child']->name }}
                                    </p>
                                    
                                    <p class="text-sm text-gray-600 mb-4">
                                        <strong>Pengajar:</strong> {{ $item['class']->teacher->name }}
                                    </p>
                                    
                                    <div class="flex space-x-2">
                                        <a href="{{ route('parent.curriculum.child-progress', [$item['child']->id, $item['class']->id]) }}" 
                                           class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                                            Lihat Progress
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
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Kelas yang Dibeli</h3>
                        <p class="text-gray-600">Anak Anda belum terdaftar di kelas manapun.</p>
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
                        @foreach($availableClasses as $class)
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
                                        <strong>Pengajar:</strong> {{ $class->teacher->name }}
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
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Jadwal Kelas Anak-Anak 7 Hari Ke Depan</h3>
                    <x-schedule-calendar :schedules="$schedules" />
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Jadwal Hari Ini</h3>
                    <div class="space-y-4">
                        @forelse($todaySchedules ?? [] as $schedule)
                            <div class="border-l-4 border-blue-500 pl-4 py-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $schedule->className->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $schedule->enrollment->student->name }}</p>
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
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                <p class="mt-2">Tidak ada kelas hari ini</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <a href="{{ route('parent.children.index') }}" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">Data Anak</h3>
                            <p class="text-sm text-gray-600">Kelola informasi anak</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('parent.reports.index') }}" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">Laporan Belajar</h3>
                            <p class="text-sm text-gray-600">Lihat progress belajar</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('parent.curriculum.index') }}" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">Program Kursus</h3>
                            <p class="text-sm text-gray-600">Lihat kurikulum</p>
                        </div>
                    </div>
                </a>

                <a href="#" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">Hubungi Admin</h3>
                            <p class="text-sm text-gray-600">Bantuan & dukungan</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Children List -->
            @if($children->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Anak-anak Anda</h3>
                    <div class="space-y-4">
                        @foreach($children as $child)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-semibold">{{ substr($child->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-medium text-gray-800">{{ $child->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $child->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 bg-{{ $child->is_active ? 'green' : 'red' }}-100 text-{{ $child->is_active ? 'green' : 'red' }}-800 text-xs font-medium rounded-full">
                                    {{ $child->is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                                <a href="#" class="text-blue-600 hover:text-blue-800">
                                    Lihat Detail →
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Belum Ada Anak Terdaftar</h3>
                    <p class="text-gray-600 mb-4">Hubungi admin untuk mendaftarkan anak Anda ke program Coding First</p>
                    <a href="#" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Hubungi Admin
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>