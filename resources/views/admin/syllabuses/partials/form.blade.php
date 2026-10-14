{{--
    Syllabus (meeting) form. Params: $syllabus, $curriculumOptions, $action, $method, $submit
--}}
<form method="POST" action="{{ $action }}" x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Pertemuan</h2>
                <p class="mt-1 text-sm text-fg-muted">Nomor pertemuan menentukan urutan dan kapan materinya terbuka bagi siswa.</p>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Kurikulum" for="curriculum_id" required>
                    <x-ui.select name="curriculum_id" :options="$curriculumOptions" :value="$syllabus->curriculum_id" placeholder="Pilih kurikulum" required />
                </x-ui.field>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Pertemuan ke" for="meeting_number" required hint="Harus unik dalam kurikulum.">
                        <x-ui.input name="meeting_number" type="number" min="1" max="200" inputmode="numeric" :value="$syllabus->meeting_number" required />
                    </x-ui.field>
                    <x-ui.field label="Durasi" for="duration_minutes" required>
                        <x-ui.input name="duration_minutes" type="number" min="15" max="480" step="5" inputmode="numeric" suffix="menit" :value="$syllabus->duration_minutes" required />
                    </x-ui.field>
                </div>
                <x-ui.field label="Judul pertemuan" for="title" required>
                    <x-ui.input name="title" :value="$syllabus->title" placeholder="cth. Mengenal variabel" required maxlength="255" />
                </x-ui.field>
                <x-ui.radio-cards name="status" legend="Status" cols="2" :value="$syllabus->status ?? 'active'" :options="[
                    'active' => ['label' => 'Aktif', 'description' => 'Terlihat oleh pengajar dan siswa.'],
                    'inactive' => ['label' => 'Nonaktif', 'description' => 'Disembunyikan; materinya ikut tertutup.'],
                ]" />
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Rencana belajar</h2>
                <p class="mt-1 text-sm text-fg-muted">Tulis satu butir per baris. Pengajar memakainya saat mengisi berita acara.</p>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Deskripsi" for="description" optional>
                    <x-ui.textarea name="description" rows="3" :value="$syllabus->description" placeholder="Gambaran singkat isi pertemuan." />
                </x-ui.field>
                <x-ui.field label="Tujuan pembelajaran" for="learning_objectives" optional hint="Satu tujuan per baris.">
                    <x-ui.textarea name="learning_objectives" rows="4" :value="$syllabus->learning_objectives" placeholder="cth. Siswa dapat membuat variabel" />
                </x-ui.field>
                <x-ui.field label="Kegiatan" for="activities" optional hint="Satu kegiatan per baris.">
                    <x-ui.textarea name="activities" rows="4" :value="$syllabus->activities" placeholder="cth. Pemanasan: kuis singkat" />
                </x-ui.field>
            </div>
        </div>
    </x-ui.card>

    <div class="form-actions mt-6">
        <x-ui.button variant="secondary" :href="$syllabus->exists ? route('admin.syllabuses.show', $syllabus) : ($syllabus->curriculum_id ? route('admin.curriculums.show', $syllabus->curriculum_id) : route('admin.syllabuses.index'))">Batal</x-ui.button>
        <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">{{ $submit }}</x-ui.button>
    </div>
</form>
