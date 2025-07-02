<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Riwayat Pembayaran</h2>
                            <p class="text-gray-600 mt-1">{{ $student->name }}</p>
                        </div>
                        <a href="{{ route('parent.children.index') }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors">
                            ← Kembali ke Daftar Anak
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-blue-800">Nama Lengkap</h3>
                            <p class="text-blue-600">{{ $student->name }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-green-800">Email</h3>
                            <p class="text-green-600">{{ $student->email }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-purple-800">Total Transaksi</h3>
                            <p class="text-purple-600 text-2xl font-bold">{{ $transactions->count() }}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-yellow-800">Status Pembayaran</h3>
                            @php
                                $paidCount = $transactions->where('status', 'paid')->count();
                                $pendingCount = $transactions->where('status', 'pending')->count();
                            @endphp
                            <p class="text-yellow-600 text-sm">
                                Lunas: {{ $paidCount }} | Pending: {{ $pendingCount }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions List -->
            @if($transactions->count() > 0)
                <div class="space-y-4">
                    @foreach($transactions as $transaction)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $transaction->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $transaction->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $transaction->status === 'overdue' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $transaction->type === 'tuition' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $transaction->type === 'registration' ? 'bg-purple-100 text-purple-800' : '' }}
                                                {{ $transaction->type === 'material' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                        </div>
                                        
                                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                            {{ $transaction->description ?: $transaction->course->name }}
                                        </h3>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <div class="text-sm text-gray-600 mb-1">
                                                    <span class="font-medium">Jumlah:</span>
                                                    <span class="text-lg font-bold text-gray-800">{{ $transaction->formatted_amount }}</span>
                                                </div>
                                                <div class="text-sm text-gray-600 mb-1">
                                                    <span class="font-medium">Metode Pembayaran:</span>
                                                    {{ $transaction->payment_method ? ucfirst($transaction->payment_method) : '-' }}
                                                </div>
                                                @if($transaction->reference_number)
                                                    <div class="text-sm text-gray-600">
                                                        <span class="font-medium">No. Referensi:</span>
                                                        {{ $transaction->reference_number }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div>
                                                <div class="text-sm text-gray-600 mb-1">
                                                    <span class="font-medium">Tanggal Transaksi:</span>
                                                    {{ $transaction->transaction_date->format('d M Y') }}
                                                </div>
                                                @if($transaction->due_date)
                                                    <div class="text-sm text-gray-600 mb-1">
                                                        <span class="font-medium">Jatuh Tempo:</span>
                                                        {{ $transaction->due_date->format('d M Y') }}
                                                        @if($transaction->due_date->isPast() && $transaction->status !== 'paid')
                                                            <span class="text-red-600 font-medium">(Terlambat)</span>
                                                        @endif
                                                    </div>
                                                @endif
                                                @if($transaction->course)
                                                    <div class="text-sm text-gray-600">
                                                        <span class="font-medium">Kursus:</span>
                                                        {{ $transaction->course->name }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($transaction->status === 'pending' && $transaction->due_date && $transaction->due_date->isFuture())
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                                <div class="flex items-center">
                                                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                    </svg>
                                                    <span class="text-sm text-yellow-800">
                                                        Pembayaran belum lunas. Silakan lakukan pembayaran sebelum {{ $transaction->due_date->format('d M Y') }}.
                                                    </span>
                                                </div>
                                            </div>
                                        @elseif($transaction->status === 'paid')
                                            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                                <div class="flex items-center">
                                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="text-sm text-green-800">Pembayaran telah lunas.</span>
                                                </div>
                                            </div>
                                        @endif
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum Ada Transaksi</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $student->name }} belum memiliki riwayat pembayaran.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 