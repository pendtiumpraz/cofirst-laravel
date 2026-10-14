<x-app-layout :title="'Ubah '.$class->name">
    <x-ui.page-header :title="'Ubah '.$class->name" subtitle="Perbarui informasi, periode, kapasitas dan pengajar kelas."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kelas' => route('admin.classes.index'), $class->name => route('admin.classes.show', $class), 'Ubah' => null]" />

    <form method="POST" action="{{ route('admin.classes.update', $class) }}" enctype="multipart/form-data" class="mt-8" novalidate
        x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        @method('PUT')
        <x-ui.card>
            @include('admin.classes.partials.form')
        </x-ui.card>
        <div class="form-actions mt-6">
            <x-ui.button variant="secondary" :href="route('admin.classes.show', $class)">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan perubahan</x-ui.button>
        </div>
    </form>
</x-app-layout>
