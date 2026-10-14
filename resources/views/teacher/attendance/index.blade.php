@php
    use App\Services\Teacher\TeachingSchedule;

    $order = ['present', 'late', 'excused', 'absent'];
    $exportQuery = array_filter($filters);
@endphp

<x-app-layout title="Riwayat Absensi">
    <x-ui.page-header title="Riwayat absensi" subtitle="Kehadiran siswa di semua kelas yang Anda ajar.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="arrow-down-tray" :href="route('teacher.attendance.export', $exportQuery)">Unduh CSV</x-ui.button>
            <x-ui.button icon="clipboard-document-check" :href="route('teacher.attendance.today')">Absensi hari ini</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.metric-strip class="mb-6" label="Ringkasan kehadiran" :stats="[
        ['label' => 'Tingkat kehadiran', 'value' => $rate !== null ? number_format($rate, 1, ',', '.') : null, 'unit' => $rate !== null ? '%' : null, 'hint' => 'hadir + terlambat', 'icon' => 'chart-bar', 'tone' => 'primary'],
        ['label' => $statuses['present'], 'value' => (string) ($summary['present'] ?? 0), 'icon' => 'check-circle', 'tone' => 'success'],
        ['label' => $statuses['late'], 'value' => (string) ($summary['late'] ?? 0), 'icon' => 'clock', 'tone' => 'warning'],
        ['label' => $statuses['excused'].' / '.mb_strtolower($statuses['absent']), 'value' => (string) (($summary['excused'] ?? 0) + ($summary['absent'] ?? 0)), 'icon' => 'x-circle', 'tone' => 'danger'],
    ]" />

    <x-ui.table caption="Riwayat absensi" mobile="stack">
        <x-slot:toolbar>
            <x-ui.filter-bar :search="false" class="w-full">
                <x-ui.select name="class" :value="$filters['class']" placeholder="Semua kelas" aria-label="Kelas" class="sm:w-52"
                    :options="$classes->pluck('name', 'id')" />
                <x-ui.select name="status" :value="$filters['status']" placeholder="Semua status" aria-label="Status" class="sm:w-40"
                    :options="collect($order)->mapWithKeys(fn ($s) => [$s => $statuses[$s]])" />
                <div class="flex items-center gap-2">
                    <label for="filter-from" class="sr-only">Dari tanggal</label>
                    <input id="filter-from" type="date" name="from" value="{{ $filters['from'] }}" class="input sm:w-40" aria-label="Dari tanggal">
                    <span class="text-fg-subtle" aria-hidden="true">–</span>
                    <label for="filter-to" class="sr-only">Sampai tanggal</label>
                    <input id="filter-to" type="date" name="to" value="{{ $filters['to'] }}" class="input sm:w-40" aria-label="Sampai tanggal">
                </div>
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th>Tanggal</x-ui.th>
            <x-ui.th>Siswa</x-ui.th>
            <x-ui.th>Kelas</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Catatan</x-ui.th>
            <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($records as $record)
            <tr>
                <td data-label="Tanggal">
                    {{ TeachingSchedule::format($record->attendance_date, 'D, j M Y') }}
                    @if ($record->meeting_number)
                        <span class="cell-secondary block">Pertemuan {{ $record->meeting_number }}</span>
                    @endif
                </td>
                <td class="cell-primary" data-label="Siswa">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :user="$record->student" :name="$record->student?->name ?? 'Siswa'" size="xs" decorative />
                        <span>{{ $record->student?->name ?? 'Siswa' }}</span>
                    </div>
                </td>
                <td data-label="Kelas">{{ $record->className?->name ?? '—' }}</td>
                <td data-label="Status">
                    <x-ui.badge :tone="$tones[$record->status] ?? 'neutral'" dot>{{ $record->status_label }}</x-ui.badge>
                    @if ($record->minutes_late)
                        <span class="cell-secondary block">{{ $record->minutes_late }} menit</span>
                    @endif
                </td>
                <td data-label="Catatan" class="max-w-xs">
                    <span class="line-clamp-2 text-fg-muted">{{ $record->notes ?: '—' }}</span>
                </td>
                <td class="cell-actions">
                    @if ($record->className?->canShowInCalendar())
                        <x-ui.button variant="ghost" size="xs" icon="pencil-square"
                            :href="route('teacher.attendance.take', [$record->class_id, 'date' => $record->attendance_date->toDateString()])"
                            label="Buka lembar absensi {{ TeachingSchedule::format($record->attendance_date, 'j M') }}" />
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            @if (array_filter($filters))
                <x-ui.empty-state icon="magnifying-glass" title="Tidak ada data yang cocok."
                    description="Ubah filter kelas, status atau rentang tanggal.">
                    <x-slot:actions><x-ui.button variant="secondary" size="sm" :href="route('teacher.attendance.index')">Hapus filter</x-ui.button></x-slot:actions>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state icon="clipboard-document-check" title="Belum ada data absensi."
                    description="Absensi yang Anda catat setelah setiap pertemuan akan tampil di sini.">
                    <x-slot:actions><x-ui.button size="sm" :href="route('teacher.attendance.today')">Isi absensi hari ini</x-ui.button></x-slot:actions>
                </x-ui.empty-state>
            @endif
        </x-slot:empty>

        @if ($records->hasPages())
            <x-slot:footer>{{ $records->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
