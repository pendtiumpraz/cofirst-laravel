{{--
    Create / edit a processing purpose. Variables: $purpose (ConsentPurpose, maybe unsaved).
--}}
@php
    use App\Models\Privacy\ConsentPurpose;

    $editing = $purpose->exists;
    $title = $editing ? 'Ubah tujuan' : 'Tambah tujuan';
    $crumbs = (Route::has('admin.dashboard') ? ['Admin' => route('admin.dashboard')] : [])
        + ['Tujuan persetujuan' => route('admin.privacy.consent-purposes.index'), $title => null];
    $selectedRoles = old('applies_to', $purpose->applies_to ?? []);
@endphp

<x-app-layout :title="$title">
    <x-ui.page-header :title="$title" :breadcrumbs="$crumbs"
        subtitle="Jelaskan tujuan dengan bahasa sederhana. Teks ini ditampilkan apa adanya kepada pengguna dan orang tua saat diminta persetujuan.">
        @if ($editing)
            <x-slot:meta><span>{{ $purpose->key }}</span><span>Versi {{ $purpose->version }}</span></x-slot:meta>
        @endif
    </x-ui.page-header>

    <form method="POST" action="{{ $editing ? route('admin.privacy.consent-purposes.update', $purpose) : route('admin.privacy.consent-purposes.store') }}"
        x-data="{ saving: false }" x-on:submit="saving = true">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="font-display text-card-title font-title text-fg">Tujuan</h2>
                <p class="mt-1 text-sm text-fg-muted">Satu tujuan untuk satu keperluan, agar persetujuan dapat dipilih terpisah (UU PDP Pasal 22 ayat (4)).</p>
            </div>
            <div class="form-section-body">
                @unless ($editing)
                    <x-ui.field label="Kunci" for="key" required hint="Huruf kecil dan garis bawah, contoh: media_publication. Tidak dapat diubah setelah disimpan.">
                        <x-ui.input name="key" class="font-mono" maxlength="64" required />
                    </x-ui.field>
                @endunless
                <x-ui.field label="Nama tujuan" for="name" required>
                    <x-ui.input name="name" :value="old('name', $purpose->name)" maxlength="150" required />
                </x-ui.field>
                <x-ui.field label="Penjelasan untuk pengguna" for="description" required hint="Apa yang dilakukan, data apa yang dipakai, dan bagaimana menarik persetujuan.">
                    <x-ui.textarea name="description" rows="4" :value="old('description', $purpose->description)" maxlength="2000" required />
                </x-ui.field>
                <x-ui.field label="Data yang dipakai" for="data_used" optional>
                    <x-ui.input name="data_used" :value="old('data_used', $purpose->data_used)" maxlength="255" placeholder="Contoh: foto, nama depan, karya proyek" />
                </x-ui.field>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="font-display text-card-title font-title text-fg">Dasar dan sasaran</h2>
                <p class="mt-1 text-sm text-fg-muted">Hanya tujuan dengan dasar <em>persetujuan</em> yang dapat dipilih pengguna. Dasar lain ditampilkan sebagai informasi.</p>
            </div>
            <div class="form-section-body">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Kategori" for="category" required>
                        <x-ui.select name="category" :options="ConsentPurpose::CATEGORIES" :value="old('category', $purpose->category)" required />
                    </x-ui.field>
                    <x-ui.field label="Dasar pemrosesan" for="legal_basis" required>
                        <x-ui.select name="legal_basis" :options="ConsentPurpose::LEGAL_BASES" :value="old('legal_basis', $purpose->legal_basis)" required />
                    </x-ui.field>
                </div>

                <fieldset class="field">
                    <legend class="label mb-1.5">Berlaku untuk <span class="label-required" aria-hidden="true">*</span></legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach (ConsentPurpose::ROLES as $value => $label)
                            <x-ui.checkbox name="applies_to[]" :value="$value" :label="$label" :checked="in_array($value, $selectedRoles, true)" />
                        @endforeach
                    </div>
                    @error('applies_to')
                        <p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>
                    @enderror
                </fieldset>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Kelompok usia" for="audience" required>
                        <x-ui.select name="audience" :options="ConsentPurpose::AUDIENCES" :value="old('audience', $purpose->audience)" required />
                    </x-ui.field>
                    <x-ui.field label="Urutan tampil" for="sort_order" optional>
                        <x-ui.input name="sort_order" type="number" min="0" max="1000" inputmode="numeric" :value="old('sort_order', $purpose->sort_order ?? 0)" />
                    </x-ui.field>
                </div>

                <div class="divide-y divide-line rounded-lg border border-line px-4">
                    <x-ui.toggle name="is_required" value="1" unchecked-value="0" label="Wajib untuk layanan inti" :checked="(bool) old('is_required', $purpose->is_required)"
                        description="Tujuan wajib tidak dapat ditolak tanpa mengakhiri layanan, misalnya pemrosesan data anak untuk kegiatan belajar." />
                    <x-ui.toggle name="is_active" value="1" unchecked-value="0" label="Aktif" :checked="(bool) old('is_active', $purpose->is_active)"
                        description="Tujuan nonaktif tidak ditawarkan lagi dan dianggap tidak disetujui." />
                    @if ($editing)
                        <x-ui.toggle name="bump_version" value="1" unchecked-value="0" label="Naikkan versi (minta persetujuan ulang)"
                            description="Centang bila isi tujuan berubah secara material. Persetujuan lama tidak berlaku sampai pengguna menyetujui versi baru (PP 33/2026 Pasal 67 ayat (4))." />
                    @endif
                </div>
            </div>
        </div>

        <div class="form-actions">
            <x-ui.button variant="secondary" :href="route('admin.privacy.consent-purposes.index')">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="saving" loading-text="Menyimpan…">{{ $editing ? 'Simpan perubahan' : 'Tambah tujuan' }}</x-ui.button>
        </div>
    </form>
</x-app-layout>
