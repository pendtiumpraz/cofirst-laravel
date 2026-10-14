@php
    $buttonSnippet = <<<'BLADE'
<x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan perubahan</x-ui.button>
<x-ui.button variant="secondary" :href="route('admin.users.index')">Batal</x-ui.button>
<x-ui.button variant="ghost" size="sm" icon="pencil-square" label="Ubah" />
BLADE;
    $badgeSnippet = <<<'BLADE'
<x-ui.badge tone="success" dot>Lunas</x-ui.badge>
<x-ui.badge mono>INV-2026-0142</x-ui.badge>
<x-ui.badge outline icon="academic-cap">Guru</x-ui.badge>
BLADE;
@endphp

<section id="aksi" class="scroll-mt-24 space-y-6">
    <x-ui.section-heading number="03" eyebrow="Komponen" title="Tombol & lencana"
        description="Satu tombol utama per area. Varian aksen hanya untuk konversi di landing — tidak di dasbor." />

    <x-ui.card title="x-ui.button" subtitle="variant: primary · secondary · ghost · soft · danger · danger-ghost · link · accent — size: xs · sm · md · lg">
        <div class="space-y-5">
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button icon="plus">Tambah siswa</x-ui.button>
                <x-ui.button variant="secondary" icon="arrow-down-tray">Ekspor</x-ui.button>
                <x-ui.button variant="ghost">Batal</x-ui.button>
                <x-ui.button variant="soft" arrow>Lihat silabus</x-ui.button>
                <x-ui.button variant="danger" icon="trash">Hapus</x-ui.button>
                <x-ui.button variant="danger-ghost" icon="archive-box">Arsipkan</x-ui.button>
                <x-ui.button variant="link" arrow>Lihat semua</x-ui.button>
                <x-ui.button variant="accent">Jadwalkan konsultasi gratis</x-ui.button>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button size="xs">Ekstra kecil</x-ui.button>
                <x-ui.button size="sm">Kecil</x-ui.button>
                <x-ui.button>Sedang</x-ui.button>
                <x-ui.button size="lg">Besar</x-ui.button>
                <x-ui.divider vertical />
                <x-ui.button variant="ghost" icon="pencil-square" label="Ubah" size="sm" />
                <x-ui.button variant="secondary" icon="ellipsis-horizontal" label="Menu lainnya" />
                <x-ui.button variant="danger-ghost" icon="trash" label="Hapus" size="xs" />
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button loading loading-text="Menyimpan…">Simpan</x-ui.button>
                <x-ui.button disabled>Nonaktif</x-ui.button>
                <x-ui.button :href="'#aksi'" disabled variant="secondary">Tautan nonaktif</x-ui.button>
                <div x-data="{ isSubmitting: false }">
                    <x-ui.button icon="paper-airplane" loading-when="isSubmitting" loading-text="Mengirim…"
                        x-on:click="isSubmitting = true; setTimeout(() => isSubmitting = false, 1500)">Coba status memuat</x-ui.button>
                </div>
                <div class="btn-group">
                    <x-ui.button variant="secondary" size="sm">Hari</x-ui.button>
                    <x-ui.button variant="secondary" size="sm">Minggu</x-ui.button>
                    <x-ui.button variant="secondary" size="sm">Bulan</x-ui.button>
                </div>
            </div>
            <div class="sm:max-w-xs">
                <x-ui.button block icon="credit-card">Bayar tagihan</x-ui.button>
            </div>
            <pre class="card card-code overflow-x-auto p-4"><code>{{ $buttonSnippet }}</code></pre>
        </div>
    </x-ui.card>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="x-ui.badge" subtitle="tone: neutral · primary · accent · success · warning · danger · info">
            <div class="space-y-4">
                <div class="flex flex-wrap gap-2">
                    <x-ui.badge>Draf</x-ui.badge>
                    <x-ui.badge tone="primary" dot>Berjalan</x-ui.badge>
                    <x-ui.badge tone="accent">Baru</x-ui.badge>
                    <x-ui.badge tone="success" dot>Lunas</x-ui.badge>
                    <x-ui.badge tone="warning" dot>Menunggu</x-ui.badge>
                    <x-ui.badge tone="danger" dot>Terlambat</x-ui.badge>
                    <x-ui.badge tone="info">Informasi</x-ui.badge>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge mono>INV-2026-0142</x-ui.badge>
                    <x-ui.badge mono tone="primary">Python</x-ui.badge>
                    <x-ui.badge outline icon="shield-check">Superadmin</x-ui.badge>
                    <x-ui.badge outline icon="academic-cap">Guru</x-ui.badge>
                    <x-ui.badge outline icon="user">Orang tua</x-ui.badge>
                    <x-ui.badge solid tone="accent">3</x-ui.badge>
                    <span class="count-pill">12</span>
                </div>
                <pre class="card card-code overflow-x-auto p-4"><code>{{ $badgeSnippet }}</code></pre>
            </div>
        </x-ui.card>

        <x-ui.card title="x-ui.kbd · x-ui.divider" subtitle="Pintasan keyboard dan pemisah">
            <div class="space-y-5">
                <p class="text-fg-muted">Tekan <x-ui.kbd>/</x-ui.kbd> untuk mencari, <x-ui.kbd>Ctrl K</x-ui.kbd> untuk palet perintah, <x-ui.kbd>Esc</x-ui.kbd> untuk menutup.</p>
                <x-ui.divider />
                <x-ui.divider label="atau" />
                <div class="flex items-center gap-3 text-sm text-fg-muted">
                    <span>Kehadiran</span><x-ui.divider vertical /><span>Laporan</span><x-ui.divider vertical /><span>Tagihan</span>
                </div>
            </div>
        </x-ui.card>
    </div>
</section>
