@php
    $stripSnippet = <<<'BLADE'
<x-ui.metric-strip :stats="[
    ['label' => 'Siswa aktif', 'value' => '312', 'trend' => 'up', 'delta' => '4%', 'hint' => 'bulan ini', 'icon' => 'users'],
    ['label' => 'Pendapatan', 'value' => 'Rp 48,2 jt', 'href' => route('finance.dashboard')],
]" />
BLADE;
    $stats = [
        ['label' => 'Siswa aktif', 'value' => '312', 'trend' => 'up', 'delta' => '4%', 'hint' => 'dibanding bulan lalu', 'icon' => 'users'],
        ['label' => 'Pendapatan bulan ini', 'value' => 'Rp 48,2', 'unit' => 'jt', 'trend' => 'up', 'delta' => '12%', 'hint' => 'vs bulan lalu', 'icon' => 'banknotes'],
        ['label' => 'Terlambat bayar', 'value' => '4', 'trend' => 'down', 'delta' => '2', 'trendTone' => 'success', 'hint' => 'lebih sedikit', 'icon' => 'clock', 'tone' => 'warning'],
        ['label' => 'Kehadiran rata-rata', 'value' => null, 'hint' => 'Belum ada data minggu ini', 'icon' => 'check-badge'],
    ];
@endphp

<section id="tata-letak" class="scroll-mt-24 space-y-6">
    <x-ui.section-heading number="05" eyebrow="Komponen" title="Tata letak & kartu"
        description="Urutan baca setiap beranda: header halaman, Hari ini, Perlu tindakan, ringkasan, lalu grafik." />

    <div class="rounded-lg border border-dashed border-line-strong px-4 sm:px-6">
        <x-ui.page-header title="Pengguna" subtitle="Kelola akun siswa, orang tua, guru dan staf."
            :breadcrumbs="['Admin' => '#', 'Sistem' => '#', 'Pengguna' => null]" class="!mb-0 border-b-0">
            <x-slot:badge><x-ui.badge>134</x-ui.badge></x-slot:badge>
            <x-slot:meta><span>Terakhir diperbarui 13 Okt 2026, 14.02 WIB</span></x-slot:meta>
            <x-slot:actions>
                <x-ui.button variant="secondary" icon="arrow-down-tray">Ekspor</x-ui.button>
                <x-ui.button icon="plus">Tambah pengguna</x-ui.button>
            </x-slot:actions>
            <x-ui.tabs label="Bagian pengguna" active="semua" :tabs="[
                'semua' => ['label' => 'Semua', 'href' => '#', 'count' => 134],
                'siswa' => ['label' => 'Siswa', 'href' => '#', 'count' => 96],
                'guru' => ['label' => 'Guru', 'href' => '#', 'count' => 12],
            ]" />
        </x-ui.page-header>
    </div>

    <div>
        <x-ui.section-heading level="3" title="x-ui.metric-strip" description="html[data-stat=strip] = satu strip bergaris tipis; html[data-stat=cards] = kartu terpisah. Markup sama." />
        <x-ui.metric-strip :stats="$stats" />
        <div class="mt-4">
            <x-ui.metric-strip :cols="3" label="Ringkasan tiga kolom">
                <x-ui.stat label="Kelas hari ini" value="3" icon="calendar-days" href="#tata-letak" />
                <x-ui.stat label="Laporan belum dibuat" value="2" icon="document-text" tone="warning" />
                <x-ui.stat label="Kehadiran minggu ini" value="96" unit="%" icon="check-circle" tone="success">
                    <x-ui.progress :value="96" size="sm" tone="success" class="mt-1" label="Kehadiran" />
                </x-ui.stat>
            </x-ui.metric-strip>
        </div>
        <pre class="card card-code mt-4 overflow-x-auto p-4"><code>{{ $stripSnippet }}</code></pre>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <x-ui.card title="Kartu dasar" subtitle="bg-surface, garis tipis, tanpa bayangan" eyebrow="Default">
            <p class="text-fg-muted">Kertas di atas kertas: kanvas washi, kartu putih.</p>
            <x-slot:footer><x-ui.button size="sm" variant="secondary">Batal</x-ui.button><x-ui.button size="sm">Simpan</x-ui.button></x-slot:footer>
        </x-ui.card>
        <x-ui.card title="Kelas berikutnya" subtitle="Python Dasar · Sabtu 09.00" variant="highlight" eyebrow="Hari ini">
            <x-slot:actions><x-ui.button size="sm" variant="link" arrow>Detail</x-ui.button></x-slot:actions>
            <p class="text-fg-muted">Garis indikator 2px di atas kartu.</p>
        </x-ui.card>
        <x-ui.card title="Kartu interaktif" subtitle="Seluruh kartu dapat diklik (stretched link)" href="#tata-letak">
            <p class="text-fg-muted">Hover: garis lebih tegas, bayangan-1, naik 1px.</p>
        </x-ui.card>
        <x-ui.card title="Tagihan jatuh tempo" variant="hero" eyebrow="Orang tua">
            <p class="stat-value">Rp 750.000</p>
            <p class="mt-1 text-[0.8125rem] text-fg-subtle">Jatuh tempo 20 Okt 2026</p>
            <x-ui.button class="mt-4" size="sm" icon="credit-card">Bayar tagihan</x-ui.button>
        </x-ui.card>
        <x-ui.card variant="callout" padding="sm">
            <p class="font-semibold">Catatan guru</p>
            <p class="mt-1 text-fg-muted">Rina sudah memahami perulangan; latih fungsi minggu depan.</p>
        </x-ui.card>
        <x-ui.card variant="code" padding="sm">
<pre class="overflow-x-auto"><code>for siswa in kelas:
    print(f"Halo, {siswa}!")</code></pre>
        </x-ui.card>
        <x-ui.card title="Daftar (padding none)" padding="none" class="md:col-span-2 xl:col-span-1">
            <x-slot:actions><x-ui.button size="xs" variant="ghost" icon="ellipsis-horizontal" label="Menu" /></x-slot:actions>
            <ul class="divide-y divide-line">
                @foreach (['Andi Pratama' => 'Hadir', 'Siti Nurhaliza' => 'Izin', 'Kenji Watanabe' => 'Hadir'] as $name => $state)
                    <li class="flex items-center gap-3 px-card py-3">
                        <x-ui.avatar :name="$name" size="sm" decorative />
                        <span class="min-w-0 flex-1 truncate font-medium">{{ $name }}</span>
                        <x-ui.badge :tone="$state === 'Hadir' ? 'success' : 'warning'" dot>{{ $state }}</x-ui.badge>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="x-ui.avatar" subtitle="Monogram dengan 6 warna aman-brand, di-hash dari id pengguna.">
            <div class="flex flex-wrap items-end gap-3">
                <x-ui.avatar name="Andi Pratama" size="xs" />
                <x-ui.avatar name="Siti Nurhaliza" size="sm" />
                <x-ui.avatar name="Kenji Watanabe" size="md" status />
                <x-ui.avatar name="Rina Kartika" size="lg" />
                <x-ui.avatar name="渡辺 健二" size="xl" />
                <x-ui.avatar name="Budi" size="2xl" />
            </div>
        </x-ui.card>

        <x-ui.card title="x-ui.tabs" subtitle="Panel di halaman · sadar hash URL · panah kiri/kanan">
            <x-ui.tabs group="demo-tabs" active="profil" label="Pengaturan akun" :tabs="[
                'profil' => ['label' => 'Profil', 'icon' => 'user'],
                'keamanan' => ['label' => 'Keamanan', 'icon' => 'shield-check', 'count' => 2],
                'notifikasi' => 'Notifikasi',
            ]">
                <x-ui.tab-panel name="profil"><p class="text-fg-muted">Nama, foto, dan kontak.</p></x-ui.tab-panel>
                <x-ui.tab-panel name="keamanan"><p class="text-fg-muted">Kata sandi, 2FA, dan sesi aktif.</p></x-ui.tab-panel>
                <x-ui.tab-panel name="notifikasi"><p class="text-fg-muted">Kanal × peristiwa.</p></x-ui.tab-panel>
            </x-ui.tabs>
        </x-ui.card>
    </div>
</section>
