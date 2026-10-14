<x-app-layout title="Tambah kurikulum">
    <x-ui.page-header title="Tambah kurikulum"
        subtitle="Setelah disimpan, Anda dapat menambahkan pertemuan dan materinya."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kurikulum' => route('admin.curriculums.index'), 'Tambah' => null]" />

    @include('admin.curriculums.partials.form', [
        'action' => route('admin.curriculums.store'),
        'method' => 'POST',
        'submit' => 'Simpan kurikulum',
    ])
</x-app-layout>
