@php
    use App\Services\Admin\AdminClock;

    $tone = ['paid' => 'success', 'pending' => 'warning', 'overdue' => 'danger', 'failed' => 'danger', 'refunded' => 'info', 'cancelled' => 'neutral'];
@endphp

<div class="space-y-gutter">
    <x-ui.metric-strip :cols="2" label="Ringkasan pembayaran" :stats="[
        ['label' => 'Tagihan belum dibayar', 'value' => AdminClock::rupiah($outstanding), 'icon' => 'receipt-percent', 'tone' => $outstanding > 0 ? 'warning' : 'neutral'],
        ['label' => 'Transaksi terakhir', 'value' => (string) $transactions->count(), 'hint' => 'ditampilkan di bawah', 'icon' => 'banknotes'],
    ]" />

    <x-ui.table caption="Transaksi terakhir" mobile="stack">
        <x-slot:head>
            <x-ui.th>Tagihan</x-ui.th>
            @if ($user->hasRole('parent'))
                <x-ui.th>Anak</x-ui.th>
            @endif
            <x-ui.th>Status</x-ui.th>
            <x-ui.th numeric>Jumlah</x-ui.th>
            <x-ui.th numeric>Tanggal</x-ui.th>
        </x-slot:head>
        @foreach ($transactions as $tx)
            <tr>
                <td class="cell-primary" data-label="Tagihan">
                    <span class="font-mono text-xs text-fg-muted">{{ $tx->invoice_number ?? '#'.$tx->id }}</span>
                    <p class="text-sm text-fg">{{ $tx->description ?: ($tx->type === 'refund' ? 'Pengembalian dana' : 'Pembayaran kursus') }}</p>
                </td>
                @if ($user->hasRole('parent'))
                    <td data-label="Anak">{{ $tx->student?->name ?? '—' }}</td>
                @endif
                <td data-label="Status"><x-ui.badge :tone="$tone[$tx->display_status] ?? 'neutral'" dot>{{ $tx->status_label }}</x-ui.badge></td>
                <td class="num" data-label="Jumlah">{{ $tx->type === 'refund' ? '−' : '' }}{{ AdminClock::rupiah($tx->amount) }}</td>
                <td class="num" data-label="Tanggal">{{ AdminClock::format($tx->paid_date ?? $tx->transaction_date) }}</td>
            </tr>
        @endforeach
        <x-slot:empty>
            <x-ui.empty-state icon="banknotes" title="Belum ada transaksi." description="Tagihan dan pembayaran dicatat oleh tim keuangan." />
        </x-slot:empty>
    </x-ui.table>
</div>
