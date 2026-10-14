@php($f = \App\Services\Academic\Format::class)
<x-app-layout title="Laporan akademik">
    <x-ui.page-header title="Laporan akademik"
        subtitle="Laporan perkembangan siswa, kepatuhan berita acara pengajar, dan progres belajar per kelas."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Laporan akademik' => null]">
        @if ($tab === 'laporan')
            <x-slot:actions>
                <x-ui.button variant="secondary" icon="arrow-down-tray" :href="route('admin.reports.export', request()->except(['page', 'tab']))">Ekspor CSV</x-ui.button>
            </x-slot:actions>
        @endif
        <x-ui.tabs :active="$tab" label="Bagian laporan" :tabs="collect($tabs)->map(fn ($label, $key) => ['label' => $label, 'href' => route('admin.reports.index', ['tab' => $key])])->all()" />
    </x-ui.page-header>

    @if ($tab === 'laporan')
        @include('admin.reports.partials.reports-tab')
    @elseif ($tab === 'berita-acara')
        @include('admin.reports.partials.compliance-tab')
    @else
        @include('admin.reports.partials.progress-tab')
    @endif
</x-app-layout>
