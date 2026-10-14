@php
    $legacyRows = collect([
        ['name' => 'Andi Pratama', 'role' => 'Siswa', 'points' => 1240],
        ['name' => 'Siti Nurhaliza', 'role' => 'Orang tua', 'points' => 0],
        ['name' => 'Kenji Watanabe', 'role' => 'Guru', 'points' => 0],
    ]);
@endphp

<section id="warisan" class="scroll-mt-24 space-y-6">
    <x-ui.section-heading number="09" eyebrow="Kompatibilitas" title="Komponen lama (Breeze)"
        description="Props dan slot tidak berubah; tampilannya kini mengikuti token. Untuk kode baru gunakan x-ui.*." />

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Tombol & input">
            <div class="space-y-4">
                <div class="flex flex-wrap gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <x-secondary-button>Batal</x-secondary-button>
                    <x-danger-button>Hapus akun</x-danger-button>
                </div>
                <div>
                    <x-input-label for="legacy_email" :value="'Email'" />
                    <x-text-input id="legacy_email" class="mt-1 block w-full" type="email" placeholder="nama@domain.id" />
                    <x-input-error class="mt-2" :messages="['Email wajib diisi.']" />
                </div>
                <x-auth-session-status :status="'Tautan reset kata sandi telah dikirim.'" />
            </div>
        </x-ui.card>

        <x-ui.card title="Navigasi & menu">
            <div class="space-y-4">
                <div class="flex gap-6 border-b border-line">
                    <x-nav-link href="#warisan" :active="true">Dasbor</x-nav-link>
                    <x-nav-link href="#warisan" :active="false">Jadwal</x-nav-link>
                </div>
                <div class="overflow-hidden rounded-md border border-line">
                    <x-responsive-nav-link href="#warisan" :active="true">Dasbor</x-responsive-nav-link>
                    <x-responsive-nav-link href="#warisan" :active="false">Jadwal</x-responsive-nav-link>
                </div>
                <x-dropdown align="left">
                    <x-slot name="trigger">
                        <button type="button" class="btn btn-secondary btn-sm">x-dropdown (lama)</button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link href="#warisan">Profil</x-dropdown-link>
                        <x-dropdown-link href="#warisan">Keluar</x-dropdown-link>
                    </x-slot>
                </x-dropdown>
                <div x-data>
                    <x-secondary-button x-on:click="$dispatch('open-modal', 'legacy-modal')">x-modal (lama)</x-secondary-button>
                    <x-modal name="legacy-modal" focusable>
                        <div class="p-6">
                            <h2 class="text-lg">Modal Breeze</h2>
                            <p class="mt-1 text-fg-muted">Logika lama tetap; gaya mengikuti token.</p>
                            <div class="mt-6 flex justify-end">
                                <x-secondary-button x-on:click="$dispatch('close')">Tutup</x-secondary-button>
                            </div>
                        </div>
                    </x-modal>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="x-user-avatar · x-photo-upload">
            <div class="space-y-5">
                <div class="flex flex-wrap items-center gap-4">
                    @if (auth()->user())
                        <x-user-avatar :user="auth()->user()" size="md" />
                    @endif
                    <x-ui.avatar name="Rina Kartika" size="md" />
                </div>
                <x-photo-upload name="legacy_photo" :max-size="2048" accept="image/jpeg,image/png,image/webp" />
            </div>
        </x-ui.card>

        <x-ui.card title="x-data-table (koleksi)" padding="sm">
            <x-data-table :columns="[
                ['key' => 'name', 'label' => 'Nama'],
                ['key' => 'role', 'label' => 'Peran'],
                ['key' => 'points', 'label' => 'Poin', 'render' => fn ($row) => number_format($row['points'], 0, ',', '.')],
            ]" :data="$legacyRows" :searchable="false" :paginate="false" />
        </x-ui.card>
    </div>
</section>
