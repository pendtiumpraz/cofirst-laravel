<x-app-layout title="Ubah materi">
    <x-ui.page-header title="Ubah materi" :subtitle="$material->title"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Materi' => route('admin.materials.index'), $material->title => route('admin.materials.show', $material), 'Ubah' => null]" />

    @include('admin.materials.partials.form', [
        'action' => route('admin.materials.update', $material),
        'method' => 'PUT',
        'submit' => 'Simpan perubahan',
    ])
</x-app-layout>
