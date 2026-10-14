@php
    $fieldSnippet = <<<'BLADE'
<x-ui.field label="Email orang tua" for="email" hint="Untuk laporan dan tagihan." required>
    <x-ui.input name="email" type="email" autocomplete="email" required />
</x-ui.field>
<x-ui.field label="Program" for="program_id" optional>
    <x-ui.select name="program_id" :options="$programs" placeholder="Pilih program" />
</x-ui.field>
BLADE;
    $programs = ['scratch' => 'Scratch Junior (7–9 th)', 'python' => 'Python Dasar (10–14 th)', 'web' => 'Web Developer (15+ th)'];
@endphp

<section id="formulir" class="scroll-mt-24 space-y-6">
    <x-ui.section-heading number="06" eyebrow="Komponen" title="Formulir"
        description="Label di atas, '(opsional)' alih-alih bintang, bantuan dan galat terhubung lewat aria-describedby. Kontrol 16px di ponsel." />

    @if (! request()->boolean('errors'))
        <x-ui.alert tone="neutral" icon="light-bulb">
            Lihat status galat dengan tombol <strong>Tampilkan status galat</strong> di atas (menambahkan <code class="font-mono">?errors=1</code>).
        </x-ui.alert>
    @endif

    <x-ui.card title="Bidang & kontrol" subtitle="x-ui.field membungkus x-ui.input / select / textarea / file">
        <form class="grid gap-x-6 gap-y-5 md:grid-cols-2" x-on:submit.prevent onsubmit="return false">
            <x-ui.field label="Nama lengkap" for="demo_name" hint="Sesuai akta kelahiran untuk sertifikat." required>
                <x-ui.input name="demo_name" placeholder="cth. Andi Pratama" autocomplete="name" required />
            </x-ui.field>

            <x-ui.field label="Email orang tua" for="demo_email" required>
                <x-ui.input name="demo_email" type="email" icon="envelope" placeholder="nama@domain.id" autocomplete="email" />
            </x-ui.field>

            <x-ui.field label="Nomor WhatsApp" for="demo_phone" optional hint="Hanya untuk pengingat jadwal.">
                <x-ui.input name="demo_phone" type="tel" prefix="+62" inputmode="tel" placeholder="812 3456 7890" />
            </x-ui.field>

            <x-ui.field label="Nominal" for="demo_amount">
                <x-ui.input name="demo_amount" type="text" inputmode="numeric" prefix="Rp" value="1.250.000" class="num" />
            </x-ui.field>

            <x-ui.field label="Program" for="demo_program">
                <x-ui.select name="demo_program" :options="$programs" placeholder="Pilih program" />
            </x-ui.field>

            <x-ui.field label="Tanggal mulai" for="demo_date">
                <x-ui.input name="demo_date" type="date" value="2026-10-20" />
            </x-ui.field>

            <x-ui.field label="Catatan untuk pengajar" for="demo_notes" optional class="md:col-span-2">
                <x-ui.textarea name="demo_notes" rows="3" placeholder="Minat, pengalaman, atau kebutuhan khusus anak." />
            </x-ui.field>

            <x-ui.field label="Bukti pembayaran" for="demo_proof" class="md:col-span-2">
                <x-ui.file name="demo_proof" accept="image/jpeg,image/png,application/pdf" :max-kb="2048" hint="PNG/JPG/PDF · maks. 2 MB" preview />
            </x-ui.field>

            <x-ui.field label="Nonaktif" for="demo_disabled">
                <x-ui.input name="demo_disabled" value="Tidak dapat diubah" disabled />
            </x-ui.field>
        </form>
        <pre class="card card-code mt-6 overflow-x-auto p-4"><code>{{ $fieldSnippet }}</code></pre>
    </x-ui.card>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Persetujuan & sakelar" subtitle="Satu kotak per tujuan, tidak dicentang, tidak digabung dengan S&K.">
            <div class="space-y-4">
                <x-ui.checkbox name="demo_consent" label="Saya menyetujui Kebijakan Privasi dan Syarat & Ketentuan" description="Versi 2.1 · berlaku 1 Okt 2026" />
                <x-ui.checkbox name="demo_photo" description="Dapat ditarik kapan saja dari Pusat Privasi.">
                    Izinkan publikasi foto/karya anak <a href="#formulir" class="link">Pelajari</a>
                </x-ui.checkbox>
                <x-ui.checkbox name="demo_disabled_box" label="Esensial (selalu aktif)" checked disabled />
                @error('demo_consent')
                    <p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>
                @enderror
                <x-ui.divider />
                <x-ui.toggle name="demo_email_notif" label="Email ringkasan mingguan" description="Laporan kehadiran dan kemajuan setiap Senin." checked />
                <x-ui.toggle name="demo_reduce_motion" label="Kurangi animasi" />
                <x-ui.divider />
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="text-sm font-medium">Mode warna</span>
                    <x-ui.color-mode-toggle />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="x-ui.radio-cards" subtitle="Permintaan subjek data (DSR) dengan catatan SLA">
            <x-ui.radio-cards name="demo_request" legend="Jenis permintaan" :cols="2" required
                hint="Kami merespons paling lambat 3×24 jam." :options="[
                    'access' => ['label' => 'Akses', 'description' => 'Salinan data pribadi Anda.', 'icon' => 'eye'],
                    'rectification' => ['label' => 'Koreksi', 'description' => 'Perbaiki data yang salah.', 'icon' => 'pencil-square'],
                    'erasure' => ['label' => 'Hapus', 'description' => 'Hapus atau anonimkan data.', 'icon' => 'trash'],
                    'portability' => ['label' => 'Portabilitas', 'description' => 'Unduh dalam format JSON/CSV.', 'icon' => 'arrow-down-tray'],
                ]" />
        </x-ui.card>
    </div>

    <x-ui.card title="x-ui.filter-bar" subtitle="Formulir GET: pencarian ('/' untuk fokus) + filter + Atur ulang">
        <x-ui.filter-bar placeholder="Cari nama, email…">
            <x-ui.select name="role" :value="request('role')" placeholder="Semua peran" class="sm:w-44" aria-label="Peran"
                :options="['Siswa' => 'Siswa', 'Orang tua' => 'Orang tua', 'Guru' => 'Guru', 'Keuangan' => 'Keuangan', 'Admin' => 'Admin']" />
            <x-slot:actions>
                <x-ui.button variant="ghost" icon="arrow-down-tray">Ekspor CSV</x-ui.button>
            </x-slot:actions>
        </x-ui.filter-bar>
    </x-ui.card>
</section>
