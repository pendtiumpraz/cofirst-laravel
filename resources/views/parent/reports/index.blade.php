<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-800">Laporan Belajar Anak</h2>
                    <p class="text-gray-600 mt-1">Pantau perkembangan dan progress belajar anak Anda</p>
                </div>
            </div>

            @if($children->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($children as $child)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <!-- Child Header -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                            <span class="text-white font-semibold text-lg">{{ substr($child->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-semibold text-gray-800">{{ $child->name }}</h3>
                                            <p class="text-sm text-gray-600">{{ $child->email }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $child->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $child->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>

                                <!-- Stats -->
                                <div class="grid grid-cols-3 gap-4 mb-6">
                                    <div class="bg-blue-50 p-3 rounded-lg text-center">
                                        <div class="text-2xl font-bold text-blue-600">{{ $child->enrollments->count() }}</div>
                                        <div class="text-xs text-blue-800">Kelas</div>
                                    </div>
                                    <div class="bg-green-50 p-3 rounded-lg text-center">
                                        <div class="text-2xl font-bold text-green-600">{{ $child->studentReports->count() }}</div>
                                        <div class="text-xs text-green-800">Laporan</div>
                                    </div>
                                    <div class="bg-purple-50 p-3 rounded-lg text-center">
                                        @php
                                            $avgScore = $child->studentReports->where('score', '!=', null)->avg('score');
                                        @endphp
                                        <div class="text-2xl font-bold text-purple-600">
                                            {{ $avgScore ? number_format($avgScore, 1) : '-' }}
                                        </div>
                                        <div class="text-xs text-purple-800">Rata-rata</div>
                                    </div>
                                </div>

                                <!-- Latest Report -->
                                @if($child->studentReports->count() > 0)
                                    @php
                                        $latestReport = $child->studentReports->sortByDesc('created_at')->first();
                                    @endphp
                                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                        <h4 class="font-medium text-gray-800 mb-2">Laporan Terbaru</h4>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-700">{{ $latestReport->title }}</p>
                                                <p class="text-xs text-gray-500">{{ $latestReport->created_at->format('d M Y') }}</p>
                                            </div>
                                            @if($latestReport->score)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    {{ $latestReport->score }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-gray-50 p-4 rounded-lg mb-4 text-center">
                                        <p class="text-sm text-gray-500">Belum ada laporan</p>
                                    </div>
                                @endif

                                <!-- Action Button -->
                                <div class="flex space-x-2">
                                    <a href="{{ route('parent.child-reports', $child) }}" 
                                       class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors text-center">
                                        Lihat Semua Laporan
                                    </a>
                                    <a href="{{ route('parent.child-schedule', $child) }}" 
                                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                        Jadwal
                                    </a>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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