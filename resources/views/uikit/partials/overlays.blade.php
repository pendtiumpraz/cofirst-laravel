@php
    $modalSnippet = <<<'BLADE'
<x-ui.modal name="tambah-kelas" title="Tambah kelas">
    <x-slot:trigger><x-ui.button icon="plus">Tambah kelas</x-ui.button></x-slot:trigger>
    …
    <x-slot:footer><x-ui.button variant="secondary" x-on:click="hide()">Batal</x-ui.button> …</x-slot:footer>
</x-ui.modal>
<x-ui.confirm-delete :action="route('admin.classes.destroy', $class)" title="Hapus kelas ini?" />
<form method="POST" data-confirm="Kirim pengingat ke 18 orang tua?" data-confirm-tone="primary">…</form>
BLADE;
@endphp

<section id="overlay" class="scroll-mt-24 space-y-6">
    <x-ui.section-heading number="08" eyebrow="Komponen" title="Dialog & menu"
        description="Fokus terkunci di dalam dialog, Esc menutup, fokus kembali ke pemicu. Di ponsel dialog tampil sebagai lembar bawah." />

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="x-ui.modal">
            <div class="flex flex-wrap gap-3">
                <x-ui.modal name="demo-modal" title="Tambah kelas" description="Isi data kelas baru. Jadwal dapat diatur setelahnya.">
                    <x-slot:trigger>
                        <x-ui.button icon="plus">Tambah kelas</x-ui.button>
                    </x-slot:trigger>
                    <form id="demo-modal-form" class="space-y-4" x-on:submit.prevent="hide(); $store.toast.success('Kelas disimpan (contoh).')">
                        <x-ui.field label="Nama kelas" for="demo_class_name" required>
                            <x-ui.input name="demo_class_name" placeholder="cth. Python Dasar — Sabtu Pagi" />
                        </x-ui.field>
                        <x-ui.field label="Kapasitas" for="demo_capacity" hint="Maks. 6 siswa per kelas.">
                            <x-ui.input name="demo_capacity" type="number" value="6" min="1" max="6" />
                        </x-ui.field>
                    </form>
                    <x-slot:footer>
                        <x-ui.button variant="secondary" x-on:click="hide()">Batal</x-ui.button>
                        <x-ui.button type="submit" form="demo-modal-form" icon="check">Simpan</x-ui.button>
                    </x-slot:footer>
                </x-ui.modal>

                <x-ui.button variant="secondary" x-data x-on:click="$dispatch('open-modal', 'demo-modal')">Buka lewat event</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card title="x-ui.confirm-delete · CF.confirm" subtitle="Aksi merusak selalu dikonfirmasi dan menyebut akibatnya.">
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.confirm-delete name="demo-hapus" action="#overlay" title="Hapus kelas ini?"
                    message="Jadwal, absensi, dan laporan kelas ikut terhapus. Tindakan ini tidak dapat dibatalkan." />
                <x-ui.confirm-delete action="#overlay" icon-only size="xs" title="Hapus siswa dari kelas?" label="Hapus siswa" />
                <x-ui.confirm-delete action="#overlay" variant="danger" label="Hapus akun" confirm-phrase="HAPUS"
                    title="Hapus akun secara permanen?" message="Data dianonimkan; catatan keuangan disimpan sesuai kewajiban hukum." />
                <x-ui.button variant="secondary" x-data
                    x-on:click="CF.confirm({ title: 'Kirim pengingat?', message: 'Pengingat dikirim ke 18 orang tua.', confirmText: 'Kirim', tone: 'primary' }).then(ok => ok && $store.toast.success('Pengingat terkirim (contoh).'))">
                    CF.confirm()
                </x-ui.button>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card title="x-ui.dropdown" subtitle="Panah atas/bawah berpindah item, Esc menutup dan mengembalikan fokus.">
        <div class="flex flex-wrap items-start gap-3">
            <x-ui.dropdown label="Ekspor" icon="arrow-down-tray">
                <p class="dropdown-label">Format</p>
                <x-ui.dropdown-item icon="table-cells" href="#overlay">CSV</x-ui.dropdown-item>
                <x-ui.dropdown-item icon="document-text" href="#overlay">PDF</x-ui.dropdown-item>
            </x-ui.dropdown>
            <x-ui.dropdown label="Budi Santoso" icon="user-circle" variant="ghost" align="left" width="56">
                <x-ui.dropdown-item icon="user" href="#overlay" active>Profil</x-ui.dropdown-item>
                <x-ui.dropdown-item icon="cog-6-tooth" href="#overlay">Pengaturan</x-ui.dropdown-item>
                <x-ui.dropdown-item icon="finger-print" href="#overlay">Privasi &amp; Data Saya</x-ui.dropdown-item>
                <div class="dropdown-divider"></div>
                <x-ui.dropdown-item icon="arrow-right-start-on-rectangle" :href="route('logout')" method="post">Keluar</x-ui.dropdown-item>
            </x-ui.dropdown>
        </div>
        <pre class="card card-code mt-6 overflow-x-auto p-4"><code>{{ $modalSnippet }}</code></pre>
    </x-ui.card>
</section>
