@php($f = \App\Services\Academic\Format::class)
<x-app-layout title="Laporan belajar">
    <x-ui.page-header title="Laporan belajar"
        subtitle="Laporan perkembangan dari pengajarmu dan catatan setiap pertemuan.">
        <x-ui.tabs :active="$tab" label="Jenis laporan" :tabs="[
            'perkembangan' => ['label' => 'Laporan perkembangan', 'count' => (int) $stats->total, 'href' => route('student.reports.index')],
            'pertemuan' => ['label' => 'Catatan pertemuan', 'count' => $meetingCount, 'href' => route('student.reports.index', ['tab' => 'pertemuan'])],
        ]" />
    </x-ui.page-header>

    @if ($tab === 'perkembangan')
        <x-ui.metric-strip class="mb-8" :stats="[
            ['label' => 'Jumlah laporan', 'value' => (string) (int) $stats->total, 'icon' => 'document-chart-bar'],
            ['label' => 'Rata-rata progres', 'value' => $stats->total ? $f::percent(round((float) $stats->average, 1)) : null, 'icon' => 'chart-bar'],
            ['label' => 'Laporan akhir', 'value' => (string) (int) ($byType['final'] ?? 0), 'icon' => 'trophy'],
            ['label' => 'Terbaru', 'value' => $stats->latest ? $f::date($stats->latest, 'd M Y') : null, 'icon' => 'calendar'],
        ]" />

        @forelse ($reports as $report)
            <x-ui.card :href="route('student.reports.show', $report)" :title="$report->title" class="mb-3" as="article"
                :subtitle="$f::date($report->report_date, 'd F Y').' · '.($report->className?->name ?? '').' · '.($report->teacher?->name ?? '')">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <x-ui.badge :tone="\App\Services\Academic\ProgressReportService::typeTone($report->type)">{{ $report->type_label }}</x-ui.badge>
                    <x-ui.progress class="sm:max-w-xs" :value="(float) $report->progress_percentage" size="sm" show-value label="Progres"
                        :tone="\App\Services\Academic\ProgressReportService::progressTone($report->progress_percentage)" />
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state size="page" icon="document-chart-bar" title="Belum ada laporan perkembangan."
                description="Pengajarmu menulis laporan secara berkala. Laporan pertamamu akan muncul di sini." />
        @endforelse

        @if ($reports->hasPages())
            <div class="mt-6">{{ $reports->links() }}</div>
        @endif
    @else
        <x-ui.table caption="Catatan pertemuan" mobile="stack">
            <x-slot:head>
                <x-ui.th>Tanggal</x-ui.th>
                <x-ui.th>Pertemuan</x-ui.th>
                <x-ui.th>Kelas</x-ui.th>
                <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
            </x-slot:head>
            @foreach ($meetings as $meeting)
                <tr>
                    <td class="num" data-label="Tanggal">{{ $f::date($meeting->report_date) }}</td>
                    <td class="cell-primary" data-label="Pertemuan">
                        <a href="{{ route('student.reports.meeting', $meeting) }}" class="font-medium text-fg hover:text-brand-text">Pertemuan {{ $meeting->meeting_number }}</a>
                        <p class="cell-secondary line-clamp-1">{{ $meeting->syllabus?->title ?? $meeting->learning_concept }}</p>
                    </td>
                    <td data-label="Kelas">{{ $meeting->className?->name }}<p class="cell-secondary">{{ $meeting->teacher?->name }}</p></td>
                    <td class="cell-actions">
                        <x-ui.button variant="ghost" size="sm" icon="arrow-right" label="Buka catatan pertemuan {{ $meeting->meeting_number }}" :href="route('student.reports.meeting', $meeting)" />
                    </td>
                </tr>
            @endforeach
            <x-slot:empty>
                <x-ui.empty-state icon="clipboard-document-list" title="Belum ada catatan pertemuan."
                    description="Setelah pertemuan, pengajarmu menulis apa yang sudah kamu pelajari di sini." />
            </x-slot:empty>
            @if ($meetings->hasPages())
                <x-slot:footer>{{ $meetings->links() }}</x-slot:footer>
            @endif
        </x-ui.table>
    @endif
</x-app-layout>
