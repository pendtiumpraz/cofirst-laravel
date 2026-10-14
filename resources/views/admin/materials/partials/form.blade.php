{{--
    Material form (create + edit).
    Params: $material, $syllabusOptions (optgroups), $types, $accept, $maxKb, $action, $method, $submit, optional $hasFile
--}}
@php($maxMb = (int) round($maxKb / 1024))
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Tempat materi</h2>
                <p class="mt-1 text-sm text-fg-muted">Materi terbuka bagi siswa bersama pertemuannya. Rentang pertemuan bisa diperpanjang bila materi dipakai beberapa pertemuan.</p>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Pertemuan (silabus)" for="syllabus_id" required>
                    <x-ui.select name="syllabus_id" :options="$syllabusOptions" :value="$material->syllabus_id" placeholder="Pilih kurikulum & pertemuan" required />
                </x-ui.field>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-ui.field label="Mulai pertemuan" for="meeting_start" optional hint="Kosongkan: sama dengan silabus.">
                        <x-ui.input name="meeting_start" type="number" min="1" max="200" inputmode="numeric" :value="$material->meeting_start" />
                    </x-ui.field>
                    <x-ui.field label="Sampai pertemuan" for="meeting_end" optional>
                        <x-ui.input name="meeting_end" type="number" min="1" max="200" inputmode="numeric" :value="$material->meeting_end" />
                    </x-ui.field>
                    <x-ui.field label="Urutan" for="order" optional hint="Kosongkan: paling akhir.">
                        <x-ui.input name="order" type="number" min="1" max="999" inputmode="numeric" :value="$material->exists ? $material->order : null" />
                    </x-ui.field>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Isi materi</h2>
                <p class="mt-1 text-sm text-fg-muted">Isi ditulis dengan Markdown (judul <span class="font-mono">#</span>, daftar <span class="font-mono">-</span>, tautan). HTML tidak ditampilkan demi keamanan.</p>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Judul materi" for="title" required>
                    <x-ui.input name="title" :value="$material->title" placeholder="cth. Lembar kerja variabel" required maxlength="255" />
                </x-ui.field>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Jenis" for="type" required>
                        <x-ui.select name="type" :options="$types" :value="$material->type" required />
                    </x-ui.field>
                    <x-ui.field label="Status" for="status" required>
                        <x-ui.select name="status" :options="['active' => 'Aktif — terlihat', 'inactive' => 'Nonaktif — disembunyikan']" :value="$material->status ?? 'active'" required />
                    </x-ui.field>
                </div>
                <x-ui.field label="Ringkasan" for="description" optional hint="Satu-dua kalimat yang muncul di daftar materi.">
                    <x-ui.textarea name="description" rows="2" :value="$material->description" maxlength="1000" />
                </x-ui.field>
                <x-ui.field label="Isi (Markdown)" for="content" optional>
                    <x-ui.textarea name="content" rows="10" :value="$material->content" class="font-mono text-sm" placeholder="# Judul materi" />
                </x-ui.field>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Tautan & berkas</h2>
                <p class="mt-1 text-sm text-fg-muted">Berkas disimpan privat dan hanya dapat diunduh oleh peserta kelas yang berhak. Untuk video, gunakan tautan YouTube atau Drive.</p>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Tautan" for="external_url" optional hint="YouTube diputar di halaman materi setelah siswa menekan tombol putar.">
                    <x-ui.input name="external_url" type="url" icon="link" :value="$material->external_url" placeholder="https://" inputmode="url" />
                </x-ui.field>

                @if (! empty($hasFile))
                    <div class="flex flex-col gap-3 rounded-lg border border-line bg-surface-2 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <x-ui.icon name="paper-clip" class="text-fg-muted" />
                            <span class="text-sm text-fg">Berkas saat ini: <span class="font-mono">{{ strtoupper(pathinfo($material->file_path, PATHINFO_EXTENSION)) }}</span></span>
                            <a href="{{ route('admin.materials.download', $material) }}" class="link text-sm">Unduh</a>
                        </div>
                        <x-ui.checkbox name="remove_file" label="Hapus berkas" />
                    </div>
                @endif

                <x-ui.field :label="! empty($hasFile) ? 'Ganti berkas' : 'Berkas'" for="file" optional>
                    <x-ui.file name="file" :accept="$accept" :max-kb="$maxKb" hint="PDF, Office, gambar, MP3/MP4, ZIP · maks. {{ $maxMb }} MB" />
                </x-ui.field>
            </div>
        </div>
    </x-ui.card>

    <div class="form-actions mt-6">
        <x-ui.button variant="secondary" :href="$material->exists ? route('admin.materials.show', $material) : ($material->syllabus_id ? route('admin.syllabuses.show', $material->syllabus_id) : route('admin.materials.index'))">Batal</x-ui.button>
        <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">{{ $submit }}</x-ui.button>
    </div>
</form>
