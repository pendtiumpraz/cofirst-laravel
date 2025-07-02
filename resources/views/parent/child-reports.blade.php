<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Laporan Belajar</h2>
                            <p class="text-gray-600 mt-1">{{ $student->name }}</p>
                        </div>
                        <a href="{{ route('parent.reports.index') }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors">
                            ← Kembali ke Daftar Laporan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-blue-800">Nama Lengkap</h3>
                            <p class="text-blue-600">{{ $student->name }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-green-800">Email</h3>
                            <p class="text-green-600">{{ $student->email }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-purple-800">Total Laporan</h3>
                            <p class="text-purple-600 text-2xl font-bold">{{ $reports->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports List -->
            @if($reports->count() > 0)
                <div class="space-y-4">
                    @foreach($reports as $report)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $report->report_type === 'weekly' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $report->report_type === 'monthly' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $report->report_type === 'final' ? 'bg-purple-100 text-purple-800' : '' }}
                                                {{ $report->report_type === 'incident' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($report->report_type) }}
                                            </span>
                                            @if($report->score)
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Nilai: {{ $report->score }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $report->title }}</h3>
                                        
                                        <div class="text-sm text-gray-600 mb-3">
                                            <div class="flex items-center space-x-4">
                                                <span>👨‍🏫 {{ $report->teacher->name }}</span>
                                                <span>📚 {{ $report->class->course->name }}</span>
                                                <span>📅 {{ $report->created_at->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <p class="text-gray-700 leading-relaxed">{{ $report->content }}</p>
                                        </div>
                                    </div>
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
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum Ada Laporan</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $student->name }} belum memiliki laporan belajar.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 