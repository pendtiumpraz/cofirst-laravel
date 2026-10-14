{{-- Admin reports hub · tab "Laporan perkembangan". Vars: $reports, $classOptions, $teacherOptions, $types, $f --}}
<x-ui.table caption="Laporan perkembangan" mobile="stack">
    <x-slot:toolbar>
        <x-ui.filter-bar placeholder="Cari judul atau nama siswa…" :keep="['tab']">
            <x-ui.select name="class_id" :options="$classOptions" placeholder="Semua kelas" :value="request('class_id')" class="sm:w-48" aria-label="Filter kelas" />
            <x-ui.select name="teacher_id" :options="$teacherOptions" placeholder="Semua pengajar" :value="request('teacher_id')" class="sm:w-44" aria-label="Filter pengajar" />
            <x-ui.select name="type" :options="$types" placeholder="Semua jenis" :value="request('type')" class="sm:w-40" aria-label="Filter jenis" />
            <label class="flex items-center gap-2 text-sm text-fg-muted">
                <span class="shrink-0">Dari</span>
                <input type="date" name="from" value="{{ request('from') }}" class="input sm:w-40" aria-label="Dari tanggal">
            </label>
            <label class="flex items-center gap-2 text-sm text-fg-muted">
                <span class="shrink-0">s.d.</span>
                <input type="date" name="to" value="{{ request('to') }}" class="input sm:w-40" aria-label="Sampai tanggal">
            </label>
        </x-ui.filter-bar>
    </x-slot:toolbar>

    <x-slot:head>
        <x-ui.th>Laporan</x-ui.th>
        <x-ui.th>Siswa</x-ui.th>
        <x-ui.th>Kelas</x-ui.th>
        <x-ui.th>Jenis</x-ui.th>
        <x-ui.th numeric>Progres</x-ui.th>
        <x-ui.th numeric>Tanggal</x-ui.th>
    </x-slot:head>

    @foreach ($reports as $report)
        <tr>
            <td class="cell-primary" data-label="Laporan">
                <a href="{{ route('admin.reports.show', $report) }}" class="font-medium text-fg hover:text-brand-text">{{ $report->title }}</a>
                <p class="cell-secondary">oleh {{ $report->teacher?->name ?? 'pengajar dihapus' }}</p>
            </td>
            <td data-label="Siswa">{{ $report->student?->name ?? '—' }}</td>
            <td data-label="Kelas">{{ $report->className?->name ?? '—' }}</td>
            <td data-label="Jenis"><x-ui.badge :tone="\App\Services\Academic\ProgressReportService::typeTone($report->type)">{{ $report->type_label }}</x-ui.badge></td>
            <td class="num" data-label="Progres">{{ $f::percent($report->progress_percentage) }}</td>
            <td class="num" data-label="Tanggal">{{ $f::date($report->report_date) }}</td>
        </tr>
    @endforeach

    <x-slot:empty>
        <x-ui.empty-state icon="document-chart-bar" title="Belum ada laporan perkembangan yang cocok."
            description="Laporan ditulis pengajar dari menu Laporan Siswa. Ubah filter untuk melihat periode lain." />
    </x-slot:empty>

    @if ($reports->hasPages())
        <x-slot:footer>{{ $reports->links() }}</x-slot:footer>
    @endif
</x-ui.table>

<p class="mt-4 flex items-start gap-2 text-sm text-fg-subtle">
    <x-ui.icon name="shield-check" class="mt-0.5 shrink-0" />
    <span>Laporan memuat data pribadi anak. Akses dan ekspor dicatat di log audit sesuai UU No. 27/2022.</span>
</p>
