{{--
    Create / edit a draft policy version. Variables: $document (PolicyDocument, maybe unsaved),
    $variables (placeholder names), $revision (revision number of this draft).
--}}
@php
    use App\Models\Privacy\PolicyDocument;

    $editing = $document->exists;
    $title = $editing ? 'Ubah draf' : 'Versi baru';
    $crumbs = (Route::has('admin.dashboard') ? ['Admin' => route('admin.dashboard')] : [])
        + ['Kebijakan & versi' => route('admin.privacy.policies.index'), $title => null];
@endphp

<x-app-layout :title="$title.' · '.$document->type_label">
    <x-ui.page-header :title="$title" :breadcrumbs="$crumbs"
        subtitle="Draf dapat diubah sebebasnya. Setelah diterbitkan, versi menjadi bukti hukum dan tidak dapat diubah lagi.">
        <x-slot:badge><x-ui.badge>{{ $document->type_label }}</x-ui.badge></x-slot:badge>
        <x-slot:meta><span>Revisi #{{ $revision }}</span></x-slot:meta>
    </x-ui.page-header>

    <form method="POST" action="{{ $editing ? route('admin.privacy.policies.update', $document) : route('admin.privacy.policies.store') }}"
        x-data="{ saving: false }" x-on:submit="saving = true">
        @csrf
        @if ($editing)
            @method('PUT')
        @else
            <input type="hidden" name="type" value="{{ $document->type }}">
        @endif

        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="font-display text-card-title font-title text-fg">Identitas dokumen</h2>
                <p class="mt-1 text-sm text-fg-muted">Nomor versi tampil di halaman publik dan tercatat pada setiap persetujuan.</p>
            </div>
            <div class="form-section-body">
                <div class="grid gap-5 sm:grid-cols-[10rem_minmax(0,1fr)]">
                    <x-ui.field label="Nomor versi" for="version" required hint="Contoh: 1.1 atau 2.0">
                        <x-ui.input name="version" :value="old('version', $document->version)" class="font-mono" maxlength="20" required />
                    </x-ui.field>
                    <x-ui.field label="Judul" for="title" required>
                        <x-ui.input name="title" :value="old('title', $document->title)" maxlength="150" required />
                    </x-ui.field>
                </div>
                <x-ui.field label="Ringkasan" for="summary" optional hint="Satu-dua kalimat di bawah judul halaman publik.">
                    <x-ui.textarea name="summary" rows="2" :value="old('summary', $document->summary)" maxlength="1000" />
                </x-ui.field>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="font-display text-card-title font-title text-fg">Isi dokumen</h2>
                <p class="mt-1 text-sm text-fg-muted">Ditulis dalam Markdown: <code class="font-mono">## Judul bagian</code>, daftar <code class="font-mono">- butir</code>, tabel, dan <code class="font-mono">[teks](tautan)</code>. HTML tidak dijalankan.</p>
                <details class="mt-4 rounded-md border border-line bg-surface p-3 text-sm">
                    <summary class="cursor-pointer font-medium text-fg">Variabel otomatis</summary>
                    <p class="mt-2 text-[0.8125rem] text-fg-muted">Diisi dari Pengaturan saat ditampilkan, sehingga data pengendali, kontak DPO dan masa retensi selalu sesuai.</p>
                    <ul class="mt-2 flex flex-wrap gap-1.5" role="list">
                        @foreach ($variables as $name)
                            {{-- Reason: entities, because a literal double brace would end the Blade echo. --}}
                            <li><code class="badge badge-neutral font-mono">&#123;&#123;{{ $name }}&#125;&#125;</code></li>
                        @endforeach
                    </ul>
                </details>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Isi (Markdown)" for="body" required>
                    <x-ui.textarea name="body" rows="28" :value="old('body', $document->body)" class="font-mono text-[0.8125rem] leading-6" spellcheck="false" required />
                </x-ui.field>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="font-display text-card-title font-title text-fg">Catatan perubahan</h2>
                <p class="mt-1 text-sm text-fg-muted">Ditampilkan di riwayat versi dan di halaman persetujuan ulang. Tulis dengan bahasa sederhana apa yang berubah bagi pengguna.</p>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Yang berubah" for="changelog" optional>
                    <x-ui.textarea name="changelog" rows="5" :value="old('changelog', $document->changelog)" maxlength="5000"
                        placeholder="Contoh: Menambahkan penyedia layanan email baru dan memperjelas masa simpan pesan chat." />
                </x-ui.field>
            </div>
        </div>

        <div class="form-actions">
            <x-ui.button variant="secondary" :href="$editing ? route('admin.privacy.policies.show', $document) : route('admin.privacy.policies.index')">Batal</x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="saving" loading-text="Menyimpan…">Simpan draf</x-ui.button>
        </div>
    </form>
</x-app-layout>
