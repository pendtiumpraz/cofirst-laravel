<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-800">Daftar Anak</h2>
                    <p class="text-gray-600 mt-1">Kelola dan pantau perkembangan anak Anda</p>
                </div>
            </div>

            @if($children->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($children as $child)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <!-- Child Avatar -->
                                <div class="flex items-center mb-4">
                                    <x-user-avatar :user="$child" size="md" gradient="blue" />
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-800">{{ $child->name }}</h3>
                                        <p class="text-sm text-gray-600">{{ $child->email }}</p>
                                    </div>
                                </div>

                                <!-- Stats -->
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div class="bg-blue-50 p-3 rounded-lg text-center">
                                        <div class="text-2xl font-bold text-blue-600">{{ $child->enrollments->count() }}</div>
                                        <div class="text-xs text-blue-800">Kelas Aktif</div>
                                    </div>
                                    <div class="bg-green-50 p-3 rounded-lg text-center">
                                        <div class="text-2xl font-bold text-green-600">{{ $child->studentReports->count() }}</div>
                                        <div class="text-xs text-green-800">Laporan</div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="space-y-2">
                                    <a href="{{ route('parent.child-reports', $child) }}" 
                                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Lihat Laporan
                                    </a>
                                    
                                    <a href="{{ route('parent.child-schedule', $child) }}" 
                                       class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Jadwal Kelas
                                    </a>
                                    
                                    <a href="{{ route('parent.child-payments', $child) }}" 
                                       class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Pembayaran
                                    </a>
                                </div>

                                <!-- Status Badge -->
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $child->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $child->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="max-w-md mx-auto">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum Ada Anak Terdaftar</h3>
                            <p class="mt-1 text-sm text-gray-500">Hubungi admin untuk mendaftarkan anak Anda ke sistem.</p>
                            <div class="mt-6">
                                <a href="{{ route('dashboard') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Kembali ke Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 