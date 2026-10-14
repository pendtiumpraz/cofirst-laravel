@php
    $toastSnippet = <<<'BLADE'
return back()->with('success', 'Kelas berhasil disimpan.');       // controller
CF.toast.success('Tersimpan.')                                     // JS
Alpine.store('toast').error('Gagal menyimpan.', 'Terjadi kesalahan')
BLADE;
@endphp

<section id="umpan-balik" class="scroll-mt-24 space-y-6">
    <x-ui.section-heading number="04" eyebrow="Komponen" title="Umpan balik"
        description="Warna tidak pernah jadi satu-satunya sinyal: selalu ada ikon atau teks. Notifikasi memakai aria-live." />

    <div class="grid gap-4 lg:grid-cols-2">
        <x-ui.alert tone="info" title="Jadwal diperbarui">Kelas Python Dasar pindah ke Sabtu, 09.00 WIB.</x-ui.alert>
        <x-ui.alert tone="success" title="Pembayaran diterima" dismissible>Terima kasih. Kuitansi dikirim ke email Anda.</x-ui.alert>
        <x-ui.alert tone="warning" title="Tagihan jatuh tempo">
            Rp 750.000 jatuh tempo 20 Okt 2026.
            <x-slot:actions><x-ui.button size="sm">Bayar tagihan</x-ui.button></x-slot:actions>
        </x-ui.alert>
        <x-ui.alert tone="danger" title="Gagal memuat data">Periksa koneksi Anda lalu coba lagi.</x-ui.alert>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="x-ui.toast" subtitle="Satu wilayah per layout. Sukses hilang setelah 5 detik; galat tetap.">
            <div class="space-y-4" x-data>
                <div class="flex flex-wrap gap-2">
                    <x-ui.button variant="secondary" size="sm" x-on:click="$store.toast.success('Profil berhasil diperbarui.')">Sukses</x-ui.button>
                    <x-ui.button variant="secondary" size="sm" x-on:click="$store.toast.info('Laporan baru tersedia.', 'Informasi')">Info</x-ui.button>
                    <x-ui.button variant="secondary" size="sm" x-on:click="$store.toast.warning('Sesi berakhir dalam 5 menit.')">Peringatan</x-ui.button>
                    <x-ui.button variant="secondary" size="sm" x-on:click="$store.toast.error('Tidak dapat menyimpan. Coba lagi.')">Galat</x-ui.button>
                </div>
                <pre class="card card-code overflow-x-auto p-4"><code>{{ $toastSnippet }}</code></pre>
            </div>
        </x-ui.card>

        <x-ui.card title="x-ui.progress · x-ui.progress-ring">
            <div class="space-y-5">
                <x-ui.progress :value="72" label="Progres kursus" show-value />
                <x-ui.progress :value="45" tone="warning" label="Terbayar" show-value size="sm" />
                <x-ui.progress :value="100" tone="success" label="Kehadiran bulan ini" show-value size="lg" />
                <div class="flex flex-wrap items-center gap-6">
                    <x-ui.progress-ring :value="72" label="Kehadiran" />
                    <x-ui.progress-ring :value="38" :size="88" :stroke="8" label="Progres level" />
                    <x-ui.progress-ring :value="100" :size="48" :stroke="5" label="Selesai" />
                </div>
            </div>
        </x-ui.card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="x-ui.empty-state" padding="none">
            <x-ui.empty-state icon="calendar-days" title="Belum ada kelas minggu ini."
                description="Jadwal berikutnya muncul setelah pendaftaran dikonfirmasi.">
                <x-slot:actions><x-ui.button variant="secondary" size="sm">Lihat program</x-ui.button></x-slot:actions>
            </x-ui.empty-state>
        </x-ui.card>
        <x-ui.empty-state size="page" icon="shield-check" title="Tidak ada permintaan data yang menunggu."
            description="Permintaan baru dari Pusat Privasi akan tampil di sini." hint="// tenggat respons 3×24 jam" />
    </div>

    <x-ui.card title="x-ui.skeleton" subtitle="Status memuat yang menyerupai tinggi akhir; kilau berhenti saat gerak dikurangi.">
        <div class="grid gap-6 lg:grid-cols-3">
            <x-ui.skeleton />
            <x-ui.skeleton variant="card" />
            <div class="space-y-3">
                <div class="flex items-center gap-3"><x-ui.skeleton variant="avatar" class="w-auto" /><x-ui.skeleton variant="title" /></div>
                <x-ui.skeleton variant="rows" :rows="2" class="rounded-lg border border-line" />
            </div>
        </div>
    </x-ui.card>
</section>
