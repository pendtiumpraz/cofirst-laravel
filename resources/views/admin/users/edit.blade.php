<x-app-layout :title="'Ubah '.$user->name">
    <x-ui.page-header :title="'Ubah '.$user->name" subtitle="Perbarui akun, peran, profil dan tautan keluarga."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Pengguna' => route('admin.users.index'), $user->name => route('admin.users.show', $user), 'Ubah' => null]" />

    <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="mt-8" novalidate
        x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        @method('PUT')

        <x-ui.card>
            @include('admin.users.partials.form', ['user' => $user])
        </x-ui.card>

        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.users.show', $user)">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan perubahan</x-ui.button>
        </div>
    </form>
</x-app-layout>
