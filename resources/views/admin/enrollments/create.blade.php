<x-app-layout title="Daftarkan siswa">
    <x-ui.page-header title="Daftarkan siswa" subtitle="Masukkan siswa ke kelas. Jadwal mingguan kelas otomatis ditambahkan."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Pendaftaran' => route('admin.enrollments.index'), 'Daftarkan' => null]" />

    <form method="POST" action="{{ route('admin.enrollments.store') }}" class="mt-8" novalidate
        x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        <x-ui.card>
            @include('admin.enrollments.partials.form')
        </x-ui.card>
        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.enrollments.index')">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Daftarkan</x-ui.button>
        </div>
    </form>
</x-app-layout>
