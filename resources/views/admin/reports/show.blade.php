<x-app-layout :title="$report->title">
    <x-ui.page-header :title="$report->title" eyebrow="Laporan perkembangan"
        :subtitle="$report->student?->name"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Laporan akademik' => route('admin.reports.index'), $report->title => null]">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="arrow-left" :href="route('admin.reports.index')">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @include('teacher.reports.partials.report-body')
</x-app-layout>
