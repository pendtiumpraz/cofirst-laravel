@php
    use App\Services\Admin\AdminClock;

    $statusTone = ['pending' => 'warning', 'active' => 'success', 'suspended' => 'warning', 'completed' => 'info', 'dropped' => 'neutral', 'cancelled' => 'neutral', 'inactive' => 'neutral'];
    $paymentTone = ['unpaid' => 'danger', 'pending' => 'warning', 'paid' => 'success', 'refunded' => 'info'];
    $current = request('status', 'all');
    $tab = fn ($key) => route('admin.enrollments.index', ($key === 'all' ? [] : ['status' => $key]) + request()->except(['status', 'page']));
@endphp

<x-app-layout title="Pendaftaran">
    <x-ui.page-header title="Pendaftaran" subtitle="Siswa di setiap kelas, antrean persetujuan dan status pembayarannya."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Pendaftaran' => null]">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="arrow-down-tray" :href="route('admin.enrollments.export', request()->query())">Ekspor CSV</x-ui.button>
            @can('create', App\Models\Enrollment::class)
                <x-ui.button icon="plus" :href="route('admin.enrollments.create')">Daftarkan siswa</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    @if (($counts['pending'] ?? 0) > 0 && $current !== 'pending')
        <x-ui.alert tone="warning" icon="clock" class="mt-6" :title="$counts['pending'].' pendaftaran menunggu persetujuan'">
            Periksa pembayaran lalu setujui agar siswa mendapat jadwal kelas.
            <x-slot:actions><x-ui.button size="sm" variant="secondary" :href="$tab('pending')">Buka antrean</x-ui.button></x-slot:actions>
        </x-ui.alert>
    @endif

    <x-ui.tabs class="mt-6" label="Status pendaftaran" :active="$current" :tabs="[
        'all' => ['label' => 'Semua', 'count' => (int) $counts->sum(), 'href' => $tab('all')],
        'pending' => ['label' => 'Menunggu', 'count' => (int) ($counts['pending'] ?? 0), 'href' => $tab('pending')],
        'active' => ['label' => 'Aktif', 'count' => (int) ($counts['active'] ?? 0), 'href' => $tab('active')],
        'suspended' => ['label' => 'Ditangguhkan', 'count' => (int) ($counts['suspended'] ?? 0), 'href' => $tab('suspended')],
        'completed' => ['label' => 'Selesai', 'count' => (int) ($counts['completed'] ?? 0), 'href' => $tab('completed')],
        'dropped' => ['label' => 'Keluar', 'count' => (int) ($counts['dropped'] ?? 0), 'href' => $tab('dropped')],
    ]" />

    <x-ui.table caption="Daftar pendaftaran" mobile="stack" class="mt-6">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari nama atau email siswa…" class="w-full" :keep="['sort', 'direction', 'status']">
                <x-ui.select name="class" :options="$classes" placeholder="Semua kelas" :value="request('class')" class="sm:w-48" aria-label="Kelas" />
                <x-ui.select name="course" :options="$courses" placeholder="Semua kursus" :value="request('course')" class="sm:w-44" aria-label="Kursus" />
                <x-ui.select name="payment" :options="$paymentStatuses" placeholder="Semua pembayaran" :value="request('payment')" class="sm:w-44" aria-label="Pembayaran" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th>Siswa</x-ui.th>
            <x-ui.th>Kelas</x-ui.th>
            <x-ui.th sort="status">Status</x-ui.th>
            <x-ui.th>Pembayaran</x-ui.th>
            <x-ui.th sort="enrollment_date" numeric default-direction="desc">Tanggal daftar</x-ui.th>
            <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($enrollments as $enrollment)
            <tr>
                <td class="cell-primary" data-label="Siswa">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :user="$enrollment->student" size="sm" decorative />
                        <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="min-w-0 truncate font-medium text-fg hover:text-brand-text">{{ $enrollment->student?->name ?? 'Siswa terhapus' }}</a>
                    </div>
                </td>
                <td data-label="Kelas">
                    <span class="text-fg">{{ $enrollment->className?->name ?? 'Belum ada kelas' }}</span>
                    <p class="cell-secondary">{{ $enrollment->className?->course?->name ?? $enrollment->course?->name ?? '—' }}</p>
                </td>
                <td data-label="Status"><x-ui.badge :tone="$statusTone[$enrollment->status] ?? 'neutral'" dot>{{ $enrollment->status_label }}</x-ui.badge></td>
                <td data-label="Pembayaran"><x-ui.badge :tone="$paymentTone[$enrollment->payment_status] ?? 'neutral'">{{ $paymentStatuses[$enrollment->payment_status] ?? $enrollment->payment_status }}</x-ui.badge></td>
                <td class="num" data-label="Tanggal daftar">{{ AdminClock::format($enrollment->enrollment_date) }}</td>
                <td class="cell-actions">
                    <div class="inline-flex items-center gap-1">
                        @can('update', $enrollment)
                            @if ($enrollment->status === 'pending')
                                <form method="POST" action="{{ route('admin.enrollments.toggle-status', $enrollment) }}">
                                    @csrf
                                    <x-ui.button type="submit" size="sm" variant="soft" icon="check">Setujui</x-ui.button>
                                </form>
                            @endif
                            <x-ui.button variant="ghost" size="sm" icon="pencil-square" :href="route('admin.enrollments.edit', $enrollment)" :label="'Ubah pendaftaran '.($enrollment->student?->name ?? '')" />
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            @if (request()->hasAny(['search', 'class', 'course', 'payment', 'status']))
                <x-ui.empty-state icon="magnifying-glass" title="Tidak ada pendaftaran yang cocok." description="Coba kata kunci lain atau hapus filter.">
                    <x-slot:actions><x-ui.button variant="secondary" size="sm" :href="route('admin.enrollments.index')">Hapus filter</x-ui.button></x-slot:actions>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state icon="clipboard-document-check" title="Belum ada pendaftaran." description="Daftarkan siswa ke kelas yang masih punya kursi." />
            @endif
        </x-slot:empty>

        <x-slot:footer>{{ $enrollments->links() }}</x-slot:footer>
    </x-ui.table>
</x-app-layout>
