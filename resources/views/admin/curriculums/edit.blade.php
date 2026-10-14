<x-app-layout title="Ubah kurikulum">
    <x-ui.page-header title="Ubah kurikulum" :subtitle="$curriculum->title"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kurikulum' => route('admin.curriculums.index'), $curriculum->title => route('admin.curriculums.show', $curriculum), 'Ubah' => null]" />

    @include('admin.curriculums.partials.form', [
        'action' => route('admin.curriculums.update', $curriculum),
        'method' => 'PUT',
        'submit' => 'Simpan perubahan',
    ])
</x-app-layout>
