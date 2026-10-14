<x-app-layout title="Ubah izin">
    <x-ui.page-header :title="'Ubah izin · '.$permission->name" subtitle="Atur peran yang memiliki izin ini."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Izin akses' => route('admin.permissions.index'), $permission->name => route('admin.permissions.show', $permission), 'Ubah' => null]" />

    <form method="POST" action="{{ route('admin.permissions.update', $permission) }}" class="mt-8" novalidate x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        @method('PUT')
        <x-ui.card>
            @include('admin.permissions.partials.form')
        </x-ui.card>
        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.permissions.show', $permission)">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan perubahan</x-ui.button>
        </div>
    </form>
</x-app-layout>
