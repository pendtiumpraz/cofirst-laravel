<x-app-layout title="Buat kelas">
    <x-ui.page-header title="Buat kelas" subtitle="Tentukan kursus, periode, kapasitas dan pengajar. Siswa dan jadwal ditambahkan setelahnya."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kelas' => route('admin.classes.index'), 'Buat' => null]" />

    <form method="POST" action="{{ route('admin.classes.store') }}" enctype="multipart/form-data" class="mt-8" novalidate
        x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        <x-ui.card>
            @include('admin.classes.partials.form')
        </x-ui.card>
        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.classes.index')">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan kelas</x-ui.button>
        </div>
    </form>
</x-app-layout>
