<x-app-layout :title="$report->title">
    <x-ui.page-header :title="$report->title" eyebrow="Laporan perkembangan"
        :breadcrumbs="['Laporan belajar' => route('student.reports.index'), $report->title => null]">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="printer" x-data x-on:click="window.print()">Cetak</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @include('teacher.reports.partials.report-body', ['hideStudent' => true])
</x-app-layout>
