<x-app-layout title="Tambah pertemuan">
    <x-ui.page-header title="Tambah pertemuan"
        subtitle="Setelah disimpan, tambahkan materi untuk pertemuan ini."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Silabus' => route('admin.syllabuses.index'), 'Tambah' => null]" />

    @include('admin.syllabuses.partials.form', [
        'action' => route('admin.syllabuses.store'),
        'method' => 'POST',
        'submit' => 'Simpan pertemuan',
    ])
</x-app-layout>
