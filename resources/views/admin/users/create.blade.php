<x-app-layout title="Tambah pengguna">
    <x-ui.page-header title="Tambah pengguna" subtitle="Buat akun baru lalu kirim tautan agar pemiliknya mengatur kata sandi sendiri."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Pengguna' => route('admin.users.index'), 'Tambah' => null]" />

    <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="mt-8" novalidate
        x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf

        <x-ui.card>
            @include('admin.users.partials.form', ['user' => null, 'canChangeRoles' => true, 'isSelf' => false])
        </x-ui.card>

        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.users.index')">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan pengguna</x-ui.button>
        </div>
    </form>
</x-app-layout>
