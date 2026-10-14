<x-app-layout :title="'Ubah '.$course->name">
    <x-ui.page-header :title="'Ubah '.$course->name" subtitle="Perbarui informasi, harga dan publikasi kursus."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kursus' => route('admin.courses.index'), $course->name => route('admin.courses.show', $course), 'Ubah' => null]" />

    <form method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data" class="mt-8" novalidate
        x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        @method('PUT')
        <x-ui.card>
            @include('admin.courses.partials.form')
        </x-ui.card>
        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.courses.show', $course)">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan perubahan</x-ui.button>
        </div>
    </form>
</x-app-layout>
