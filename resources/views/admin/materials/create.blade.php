<x-app-layout title="Tambah materi">
    <x-ui.page-header title="Tambah materi"
        subtitle="Materi muncul untuk pengajar dan terbuka bagi siswa sesuai progres pertemuan."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Materi' => route('admin.materials.index'), 'Tambah' => null]" />

    @include('admin.materials.partials.form', [
        'action' => route('admin.materials.store'),
        'method' => 'POST',
        'submit' => 'Simpan materi',
        'hasFile' => false,
    ])
</x-app-layout>
