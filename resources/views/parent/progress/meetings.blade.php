@php($f = \App\Services\Academic\Format::class)
<x-app-layout :title="'Catatan pertemuan · '.$child->name">
    <x-ui.page-header title="Catatan pertemuan" :eyebrow="$child->name"
        subtitle="Yang dipelajari anak Anda di setiap pertemuan, ditulis langsung oleh pengajarnya."
        :breadcrumbs="['Progres anak' => route('parent.progress.index'), $child->name => route('parent.progress.child', $child), 'Catatan pertemuan' => null]" />

    @if (! $visible)
        <x-ui.alert tone="info" title="Catatan pertemuan belum dibagikan">
            Sekolah sedang tidak membagikan catatan pertemuan kepada orang tua. Anda tetap dapat melihat progres dan materi anak.
        </x-ui.alert>
    @else
        <x-ui.table caption="Catatan pertemuan" mobile="stack">
            @if ($classes->count() > 1)
                <x-slot:toolbar>
                    <x-ui.filter-bar :search="false">
                        <x-ui.select name="class_id" :options="$classes->pluck('name', 'id')" placeholder="Semua kelas" :value="request('class_id')" class="sm:w-56" aria-label="Filter kelas" />
                    </x-ui.filter-bar>
                </x-slot:toolbar>
            @endif
            <x-slot:head>
                <x-ui.th>Tanggal</x-ui.th>
                <x-ui.th>Pertemuan</x-ui.th>
                <x-ui.th>Kelas</x-ui.th>
                <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
            </x-slot:head>
            @foreach ($reports as $report)
                <tr>
                    <td class="num" data-label="Tanggal">{{ $f::date($report->report_date) }}</td>
                    <td class="cell-primary" data-label="Pertemuan">
                        <a href="{{ route('parent.progress.meeting', [$child, $report]) }}" class="font-medium text-fg hover:text-brand-text">Pertemuan {{ $report->meeting_number }}</a>
                        <p class="cell-secondary line-clamp-1">{{ $report->syllabus?->title ?? $report->learning_concept }}</p>
                    </td>
                    <td data-label="Kelas">{{ $report->className?->name }}<p class="cell-secondary">{{ $report->teacher?->name }}</p></td>
                    <td class="cell-actions">
                        <x-ui.button variant="ghost" size="sm" icon="arrow-right" label="Baca catatan pertemuan {{ $report->meeting_number }}" :href="route('parent.progress.meeting', [$child, $report])" />
                    </td>
                </tr>
            @endforeach
            <x-slot:empty>
                <x-ui.empty-state icon="clipboard-document-list" title="Belum ada catatan pertemuan."
                    description="Catatan muncul setelah pengajar melaporkan pertemuan." />
            </x-slot:empty>
            @if ($reports->hasPages())
                <x-slot:footer>{{ $reports->links() }}</x-slot:footer>
            @endif
        </x-ui.table>
    @endif
</x-app-layout>
