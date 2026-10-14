<x-app-layout title="Tulis laporan perkembangan">
    <x-ui.page-header title="Tulis laporan perkembangan"
        subtitle="Ringkasan berkala untuk siswa dan orang tua. Untuk catatan tiap pertemuan, gunakan berita acara."
        :breadcrumbs="['Laporan siswa' => route('teacher.reports.index'), 'Tulis laporan' => null]" />

    @include('teacher.reports.partials.form', [
        'action' => route('teacher.reports.store'),
        'method' => 'POST',
        'submit' => 'Simpan laporan',
    ])
</x-app-layout>
