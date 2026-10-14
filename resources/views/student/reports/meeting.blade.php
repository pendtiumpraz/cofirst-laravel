@php($f = \App\Services\Academic\Format::class)
<x-app-layout :title="'Pertemuan '.$report->meeting_number">
    <x-ui.page-header :title="'Pertemuan '.$report->meeting_number" eyebrow="Catatan pertemuan"
        :subtitle="$f::date($report->report_date, 'l, d F Y').' · '.($report->className?->name ?? '').' · '.($report->teacher?->name ?? '')"
        :breadcrumbs="['Laporan belajar' => route('student.reports.index', ['tab' => 'pertemuan']), 'Pertemuan '.$report->meeting_number => null]" />

    <div class="max-w-4xl">
        @include('class-reports.partials.report-body', ['showInternal' => false, 'showMedia' => false])
    </div>
</x-app-layout>
