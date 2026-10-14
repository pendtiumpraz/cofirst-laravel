<x-app-layout title="Ubah laporan perkembangan">
    <x-ui.page-header title="Ubah laporan" :subtitle="$report->title"
        :breadcrumbs="['Laporan siswa' => route('teacher.reports.index'), $report->title => route('teacher.reports.show', $report), 'Ubah' => null]" />

    @include('teacher.reports.partials.form', [
        'action' => route('teacher.reports.update', $report),
        'method' => 'PUT',
        'submit' => 'Simpan perubahan',
    ])
</x-app-layout>
