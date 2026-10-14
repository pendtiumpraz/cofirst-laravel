<x-app-layout title="Peran baru">
    <x-ui.page-header title="Peran baru" subtitle="Buat peran kustom lalu pilih izinnya."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Peran' => route('admin.roles.index'), 'Baru' => null]" />

    <form method="POST" action="{{ route('admin.roles.store') }}" class="mt-8" novalidate x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        <x-ui.card>
            @include('admin.roles.partials.form')
        </x-ui.card>
        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.roles.index')">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan peran</x-ui.button>
        </div>
    </form>
</x-app-layout>
