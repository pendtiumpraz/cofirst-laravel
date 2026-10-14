<x-app-layout title="Ubah pendaftaran">
    <x-ui.page-header :title="'Ubah pendaftaran · '.($enrollment->student?->name ?? 'Siswa')" subtitle="Ubah kelas, status atau status pembayaran."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Pendaftaran' => route('admin.enrollments.index'), 'Detail' => route('admin.enrollments.show', $enrollment), 'Ubah' => null]" />

    <form method="POST" action="{{ route('admin.enrollments.update', $enrollment) }}" class="mt-8" novalidate
        x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        @method('PUT')
        <x-ui.card>
            @include('admin.enrollments.partials.form')
        </x-ui.card>
        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.enrollments.show', $enrollment)">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan perubahan</x-ui.button>
        </div>
    </form>
</x-app-layout>
