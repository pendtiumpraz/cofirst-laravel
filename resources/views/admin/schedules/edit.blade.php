<x-app-layout title="Ubah jadwal">
    <x-ui.page-header :title="'Ubah jadwal · '.($slot->class()?->name ?? 'Kelas')" :subtitle="$slot->dayName().', '.$slot->timeRange().' — perubahan berlaku untuk semua siswa di sesi ini.'"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Jadwal' => route('admin.schedules.index'), 'Detail' => route('admin.schedules.show', $schedule), 'Ubah' => null]" />

    <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" class="mt-8" novalidate
        x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        @method('PUT')
        <x-ui.card>
            @include('admin.schedules.partials.form')
        </x-ui.card>
        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.schedules.show', $schedule)">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan perubahan</x-ui.button>
        </div>
    </form>
</x-app-layout>
