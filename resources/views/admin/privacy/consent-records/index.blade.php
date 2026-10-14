{{--
    Admin › Privasi & PDP › Catatan persetujuan: the append-only consent ledger (UU PDP Pasal 24).
    Variables: $records (paginator), $filters, $purposes (key => name), $stats (active, withdrawn, parental).
--}}
@php
    use App\Models\Privacy\ConsentRecord;
    use App\Services\Privacy\PolicyService;

    $crumbs = (Route::has('admin.dashboard') ? ['Admin' => route('admin.dashboard')] : []) + ['Privasi & PDP' => null, 'Catatan persetujuan' => null];
    $active = array_filter($filters);
@endphp

<x-app-layout title="Catatan persetujuan">
    <x-ui.page-header title="Catatan persetujuan" :breadcrumbs="$crumbs"
        subtitle="Bukti setiap persetujuan yang diberikan, ditolak, atau ditarik. Catatan tidak dapat diubah atau dihapus; setiap tampilan dan ekspor tercatat di log audit.">
        <x-slot:actions>
            @if (Route::has('admin.privacy.consent-purposes.index') && auth()->user()->can('manage privacy'))
                <x-ui.button variant="secondary" icon="hand-raised" :href="route('admin.privacy.consent-purposes.index')">Tujuan persetujuan</x-ui.button>
            @endif
            <x-ui.button variant="secondary" icon="arrow-down-tray" :href="route('admin.privacy.consent-records.export', request()->query())">Ekspor CSV</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.metric-strip class="mb-8" :stats="[
        ['label' => 'Persetujuan aktif', 'value' => number_format($stats['active'], 0, ',', '.'), 'hint' => 'keputusan terakhir per orang & tujuan', 'icon' => 'check-badge'],
        ['label' => 'Ditarik 30 hari terakhir', 'value' => number_format($stats['withdrawn'], 0, ',', '.'), 'icon' => 'arrow-uturn-left'],
        ['label' => 'Diberikan orang tua/wali', 'value' => number_format($stats['parental'], 0, ',', '.'), 'icon' => 'users'],
    ]" />

    <x-ui.table caption="Catatan persetujuan" mobile="stack">
        <x-slot:toolbar>
            <x-ui.filter-bar class="w-full" search-name="q" :value="$filters['q']" placeholder="Cari nama, email subjek atau wali…">
                <x-ui.select name="purpose" :options="$purposes" :value="$filters['purpose']" placeholder="Semua tujuan" aria-label="Tujuan" class="sm:w-48" />
                <x-ui.select name="decision" :options="ConsentRecord::DECISIONS" :value="$filters['decision']" placeholder="Semua keputusan" aria-label="Keputusan" class="sm:w-40" />
                <x-ui.select name="method" :options="ConsentRecord::METHODS" :value="$filters['method']" placeholder="Semua metode" aria-label="Metode" class="sm:w-44" />
                <x-ui.input name="from" type="date" :value="$filters['from']" aria-label="Dari tanggal" class="sm:w-40" />
                <x-ui.input name="to" type="date" :value="$filters['to']" aria-label="Sampai tanggal" class="sm:w-40" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th>Waktu</x-ui.th>
            <x-ui.th>Subjek</x-ui.th>
            <x-ui.th>Tujuan</x-ui.th>
            <x-ui.th>Keputusan</x-ui.th>
            <x-ui.th>Metode & pelaku</x-ui.th>
        </x-slot:head>

        @foreach ($records as $record)
            <tr>
                <td data-label="Waktu" class="whitespace-nowrap text-sm text-fg-muted">{{ PolicyService::date($record->created_at, true) }}</td>
                <td class="cell-primary" data-label="Subjek">
                    @if ($record->subject)
                        <span class="font-medium text-fg">{{ $record->subject->name }}</span>
                        <span class="cell-secondary block break-all">{{ $record->subject->email }}</span>
                    @else
                        <span class="text-fg-subtle">Akun dihapus</span>
                    @endif
                </td>
                <td data-label="Tujuan">
                    <span class="text-sm text-fg">{{ $record->purpose?->name ?? $record->purpose_key }}</span>
                    <span class="cell-secondary block font-mono">v{{ $record->purpose_version }}</span>
                </td>
                <td data-label="Keputusan"><x-ui.badge :tone="$record->decision_tone" dot>{{ $record->decision_label }}</x-ui.badge></td>
                <td data-label="Metode & pelaku" class="text-sm">
                    <span class="text-fg">{{ $record->method_label }}</span>
                    <span class="cell-secondary block">{{ $record->actor_label }}</span>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="clipboard-document-check"
                :title="$active ? 'Tidak ada catatan yang cocok dengan filter.' : 'Belum ada catatan persetujuan.'"
                :description="$active ? 'Ubah atau atur ulang filter untuk melihat catatan lain.' : 'Catatan muncul saat pengguna mendaftar, memilih cookie, atau orang tua/wali menyetujui pendaftaran anak.'" />
        </x-slot:empty>

        @if ($records->hasPages())
            <x-slot:footer>{{ $records->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
