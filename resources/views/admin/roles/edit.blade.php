@php $label = App\Support\Admin\AccessControl::roleLabel($role->name); @endphp

<x-app-layout :title="'Ubah peran '.$label">
    <x-ui.page-header :title="'Ubah peran '.$label" subtitle="Perubahan izin langsung berlaku untuk semua pengguna dengan peran ini dan tercatat di log audit."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Peran' => route('admin.roles.index'), $label => route('admin.roles.show', $role), 'Ubah' => null]" />

    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="mt-8" novalidate x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        @method('PUT')
        <x-ui.card>
            @include('admin.roles.partials.form')
        </x-ui.card>
        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.roles.show', $role)">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan perubahan</x-ui.button>
        </div>
    </form>
</x-app-layout>
