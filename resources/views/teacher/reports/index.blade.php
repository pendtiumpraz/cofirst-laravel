@php($f = \App\Services\Academic\Format::class)
<x-app-layout title="Laporan siswa">
    <x-ui.page-header title="Laporan siswa"
        subtitle="Laporan perkembangan berkala yang Anda tulis untuk siswa dan orang tuanya.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="clipboard-document-list" :href="route('class-reports.index')">Berita acara</x-ui.button>
            <x-ui.button icon="plus" :href="route('teacher.reports.create')">Tulis laporan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Laporan perkembangan" mobile="stack">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari judul atau nama siswa…">
                <x-ui.select name="class_id" :options="$classOptions" placeholder="Semua kelas" :value="request('class_id')" class="sm:w-48" aria-label="Filter kelas" />
                <x-ui.select name="type" :options="$types" placeholder="Semua jenis" :value="request('type')" class="sm:w-40" aria-label="Filter jenis" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th>Laporan</x-ui.th>
            <x-ui.th>Siswa</x-ui.th>
            <x-ui.th>Jenis</x-ui.th>
            <x-ui.th>Progres</x-ui.th>
            <x-ui.th numeric>Tanggal</x-ui.th>
        </x-slot:head>

        @foreach ($reports as $report)
            <tr>
                <td class="cell-primary" data-label="Laporan">
                    <a href="{{ route('teacher.reports.show', $report) }}" class="font-medium text-fg hover:text-brand-text">{{ $report->title }}</a>
                    <p class="cell-secondary">{{ $report->className?->name }}</p>
                </td>
                <td data-label="Siswa">
                    @if ($report->student)
                        <a href="{{ route('teacher.reports.by-student', $report->student) }}" class="link">{{ $report->student->name }}</a>
                    @else
                        —
                    @endif
                </td>
                <td data-label="Jenis"><x-ui.badge :tone="\App\Services\Academic\ProgressReportService::typeTone($report->type)">{{ $report->type_label }}</x-ui.badge></td>
                <td data-label="Progres" class="min-w-[8rem]">
                    <x-ui.progress :value="(float) $report->progress_percentage" size="sm" show-value
                        :tone="\App\Services\Academic\ProgressReportService::progressTone($report->progress_percentage)" label="Progres" />
                </td>
                <td class="num" data-label="Tanggal">{{ $f::date($report->report_date) }}</td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="document-chart-bar" title="Belum ada laporan perkembangan."
                description="Tulis laporan mingguan, bulanan atau akhir agar orang tua memahami kemajuan anaknya.">
                <x-slot:actions>
                    <x-ui.button icon="plus" :href="route('teacher.reports.create')">Tulis laporan</x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        </x-slot:empty>

        @if ($reports->hasPages())
            <x-slot:footer>{{ $reports->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
