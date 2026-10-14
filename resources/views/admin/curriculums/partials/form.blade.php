{{--
    Curriculum form (create + edit). Params: $curriculum, $courses (id => name), $types, $action, $method ('POST'|'PUT'), $submit
--}}
<form method="POST" action="{{ $action }}" x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Identitas kurikulum</h2>
                <p class="mt-1 text-sm text-fg-muted">Satu kursus boleh memiliki beberapa varian kurikulum, misalnya reguler dan fast track.</p>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Judul kurikulum" for="title" required>
                    <x-ui.input name="title" :value="$curriculum->title" placeholder="cth. Python Dasar — Reguler" required maxlength="255" />
                </x-ui.field>
                <x-ui.field label="Kursus" for="course_id" required>
                    <x-ui.select name="course_id" :options="$courses" :value="$curriculum->course_id" placeholder="Pilih kursus" required />
                </x-ui.field>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Jenis" for="type" required>
                        <x-ui.select name="type" :options="$types" :value="$curriculum->type" required />
                    </x-ui.field>
                    <x-ui.field label="Durasi" for="duration_weeks" optional hint="Perkiraan lama program.">
                        <x-ui.input name="duration_weeks" type="number" min="1" max="104" inputmode="numeric" suffix="minggu" :value="$curriculum->duration_weeks" />
                    </x-ui.field>
                </div>
                <x-ui.radio-cards name="status" legend="Status" cols="2" :value="$curriculum->status ?? 'active'" :options="[
                    'active' => ['label' => 'Aktif', 'description' => 'Dapat dipakai kelas dan terlihat oleh pengajar.'],
                    'inactive' => ['label' => 'Nonaktif', 'description' => 'Disembunyikan dari pilihan kelas baru.'],
                ]" />
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Isi & tujuan</h2>
                <p class="mt-1 text-sm text-fg-muted">Ringkasan ini dibaca pengajar, siswa dan orang tua.</p>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Deskripsi" for="description" optional>
                    <x-ui.textarea name="description" rows="4" :value="$curriculum->description" placeholder="Apa yang dipelajari dan untuk siapa kurikulum ini." />
                </x-ui.field>
                <x-ui.field label="Tujuan pembelajaran" for="objectives" optional hint="Satu tujuan per baris.">
                    <x-ui.textarea name="objectives" rows="5" :value="$curriculum->objectives" placeholder="cth. Memahami variabel dan tipe data" />
                </x-ui.field>
            </div>
        </div>
    </x-ui.card>

    <div class="form-actions mt-6">
        <x-ui.button variant="secondary" :href="$curriculum->exists ? route('admin.curriculums.show', $curriculum) : route('admin.curriculums.index')">Batal</x-ui.button>
        <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">{{ $submit }}</x-ui.button>
    </div>
</form>
