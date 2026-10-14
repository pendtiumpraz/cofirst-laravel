<x-app-layout title="Tambah kursus">
    <x-ui.page-header title="Tambah kursus" subtitle="Program baru untuk katalog Coding First."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kursus' => route('admin.courses.index'), 'Tambah' => null]" />

    <form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data" class="mt-8" novalidate
        x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        <x-ui.card>
            @include('admin.courses.partials.form')
        </x-ui.card>
        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.courses.index')">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan kursus</x-ui.button>
        </div>
    </form>
</x-app-layout>
