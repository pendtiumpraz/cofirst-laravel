<x-app-layout title="Ubah pertemuan">
    <x-ui.page-header title="Ubah pertemuan" :subtitle="'Pertemuan '.$syllabus->meeting_number.' — '.$syllabus->title"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Silabus' => route('admin.syllabuses.index'), 'Pertemuan '.$syllabus->meeting_number => route('admin.syllabuses.show', $syllabus), 'Ubah' => null]" />

    @include('admin.syllabuses.partials.form', [
        'action' => route('admin.syllabuses.update', $syllabus),
        'method' => 'PUT',
        'submit' => 'Simpan perubahan',
    ])
</x-app-layout>
