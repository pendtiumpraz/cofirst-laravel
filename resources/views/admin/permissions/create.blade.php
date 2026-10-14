<x-app-layout title="Izin kustom">
    <x-ui.page-header title="Izin kustom" subtitle="Tambahkan izin baru lalu berikan ke peran yang membutuhkannya."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Izin akses' => route('admin.permissions.index'), 'Baru' => null]" />

    <form method="POST" action="{{ route('admin.permissions.store') }}" class="mt-8" novalidate x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        <x-ui.card>
            @include('admin.permissions.partials.form', ['isSystem' => false])
        </x-ui.card>
        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.permissions.index')">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan izin</x-ui.button>
        </div>
    </form>
</x-app-layout>
