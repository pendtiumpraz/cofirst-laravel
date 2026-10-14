{{--
    Admin › Privasi & PDP › Tujuan persetujuan (purpose & legal-basis register, UU PDP Pasal 20).
    Variables: $purposes (with records_count), $active (purpose key => active grants), $builtIn (keys that cannot be deleted).
--}}
@php
    use App\Models\Privacy\ConsentPurpose;
@endphp

<x-app-layout title="Tujuan persetujuan">
    <x-ui.page-header title="Tujuan persetujuan"
        subtitle="Daftar tujuan pemrosesan beserta dasar hukumnya. Tujuan berbasis persetujuan muncul di pendaftaran, banner cookie, persetujuan orang tua, dan Pusat Privasi.">
        <x-slot:actions>
            @if (Route::has('admin.privacy.consent-records.index'))
                <x-ui.button variant="secondary" icon="clipboard-document-check" :href="route('admin.privacy.consent-records.index')">Catatan persetujuan</x-ui.button>
            @endif
            <x-ui.button icon="plus" :href="route('admin.privacy.consent-purposes.create')">Tambah tujuan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Daftar tujuan pemrosesan" mobile="stack">
        <x-slot:head>
            <x-ui.th>Tujuan</x-ui.th>
            <x-ui.th>Kategori</x-ui.th>
            <x-ui.th>Dasar pemrosesan</x-ui.th>
            <x-ui.th>Berlaku untuk</x-ui.th>
            <x-ui.th numeric>Persetujuan aktif</x-ui.th>
            <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($purposes as $purpose)
            <tr @class(['opacity-60' => ! $purpose->is_active])>
                <td class="cell-primary" data-label="Tujuan">
                    <span class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.privacy.consent-purposes.edit', $purpose) }}" class="font-medium text-fg hover:text-brand-text">{{ $purpose->name }}</a>
                        @if ($purpose->is_required)
                            <x-ui.badge tone="primary">Wajib</x-ui.badge>
                        @endif
                        @unless ($purpose->is_active)
                            <x-ui.badge>Nonaktif</x-ui.badge>
                        @endunless
                    </span>
                    <span class="cell-secondary mt-0.5 block font-mono">{{ $purpose->key }} · v{{ $purpose->version }}</span>
                </td>
                <td data-label="Kategori"><x-ui.badge :tone="$purpose->category_tone">{{ $purpose->category_label }}</x-ui.badge></td>
                <td data-label="Dasar pemrosesan" class="text-sm">
                    <span class="text-fg">{{ $purpose->legal_basis_short }}</span>
                    <span class="cell-secondary block">{{ $purpose->legal_basis_article }}</span>
                </td>
                <td data-label="Berlaku untuk" class="text-sm text-fg-muted">
                    {{ $purpose->audience_summary }}
                    @if ($purpose->audience !== 'all')
                        <span class="block text-xs text-fg-subtle">{{ ConsentPurpose::AUDIENCES[$purpose->audience] }}</span>
                    @endif
                </td>
                <td class="num" data-label="Persetujuan aktif">
                    {{ $purpose->isConsentBased() ? number_format((int) ($active[$purpose->key] ?? 0), 0, ',', '.') : '—' }}
                </td>
                <td class="cell-actions">
                    <x-ui.button variant="ghost" size="sm" icon="pencil-square" :href="route('admin.privacy.consent-purposes.edit', $purpose)" label="Ubah {{ $purpose->name }}" />
                    @if ($purpose->records_count === 0 && ! in_array($purpose->key, $builtIn, true))
                        <x-ui.confirm-delete :action="route('admin.privacy.consent-purposes.destroy', $purpose)" icon-only size="sm"
                            title="Hapus tujuan ini?" message="Tujuan “{{ $purpose->name }}” belum memiliki catatan persetujuan dan akan dihapus." />
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="hand-raised" title="Belum ada tujuan pemrosesan."
                description="Jalankan PrivacySeeder atau tambahkan tujuan pertama, misalnya publikasi karya siswa.">
                <x-slot:actions><x-ui.button icon="plus" :href="route('admin.privacy.consent-purposes.create')">Tambah tujuan</x-ui.button></x-slot:actions>
            </x-ui.empty-state>
        </x-slot:empty>
    </x-ui.table>

    <p class="mt-4 max-w-measure text-[0.8125rem] leading-relaxed text-fg-subtle">
        Kunci tujuan dipakai modul lain untuk memeriksa persetujuan, sehingga tidak dapat diubah. Tujuan bawaan dan tujuan yang sudah memiliki catatan hanya dapat dinonaktifkan, agar pemeriksaan di modul lain dan riwayat bukti tetap utuh (UU PDP Pasal 24).
    </p>
</x-app-layout>
