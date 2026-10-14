<x-app-layout title="Tambah jadwal">
    <x-ui.page-header title="Tambah jadwal" subtitle="Atur hari, jam, ruang dan peserta sesi mingguan. Bentrok guru, ruang dan siswa dicek otomatis."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Jadwal' => route('admin.schedules.index'), 'Tambah' => null]" />

    @if (empty($classes))
        <x-ui.empty-state class="mt-8" size="page" icon="rectangle-group" title="Belum ada kelas yang siap dijadwalkan."
            description="Jadwal membutuhkan kelas aktif yang sudah memiliki pengajar dan minimal satu siswa.">
            <x-slot:actions><x-ui.button :href="route('admin.classes.index')" variant="secondary">Buka daftar kelas</x-ui.button></x-slot:actions>
        </x-ui.empty-state>
    @else
        <form method="POST" action="{{ route('admin.schedules.store') }}" class="mt-8" novalidate
            x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
            @csrf
            <x-ui.card>
                @include('admin.schedules.partials.form')
            </x-ui.card>
            <div class="form-actions mt-6">
                <x-ui.button variant="secondary" :href="route('admin.schedules.index')">Batal</x-ui.button>
                <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan jadwal</x-ui.button>
            </div>
        </form>
    @endif
</x-app-layout>
