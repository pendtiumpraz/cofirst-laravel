{{--
    Meeting fields shared by the create and edit forms (date, time, meeting, material,
    concept, documentation link).
    Params: $syllabuses (Collection<Syllabus> with activeMaterials), $values (array: report_date,
    start_time, end_time, meeting_number, material_id, learning_concept, learning_media_link)
--}}
@php
    $meetingOptions = $syllabuses->mapWithKeys(fn ($s) => [$s->meeting_number => 'Pertemuan '.$s->meeting_number.' — '.$s->title])->all();
    $meetingData = $syllabuses->map(fn ($s) => [
        'id' => $s->id,
        'number' => (int) $s->meeting_number,
        'title' => $s->title,
        'description' => (string) $s->description,
        'materials' => $s->activeMaterials->map(fn ($m) => ['id' => $m->id, 'title' => $m->title])->values(),
    ])->values();
    $hosts = implode(', ', \App\Services\Academic\AcademicSettings::mediaLinkHosts());
@endphp

<div class="form-section"
    x-data="{
        meetings: @js($meetingData),
        meeting: @js((string) old('meeting_number', $values['meeting_number'] ?? '')),
        materialId: @js((string) old('material_id', $values['material_id'] ?? '')),
        concept: @js((string) old('learning_concept', $values['learning_concept'] ?? '')),
        get current() { return this.meetings.find(m => String(m.number) === String(this.meeting)) || null; },
        fillConcept() { if (this.current) { this.concept = this.current.title + (this.current.description ? ' — ' + this.current.description : ''); } },
    }">
    <div class="form-section-intro">
        <h2 class="card-title">Pertemuan</h2>
        <p class="mt-1 text-sm text-fg-muted">Pilih pertemuan sesuai silabus. Konsep yang dipelajari dapat diisi otomatis dari silabus lalu disesuaikan.</p>
    </div>
    <div class="form-section-body">
        <div class="grid gap-5 sm:grid-cols-3">
            <x-ui.field label="Tanggal" for="report_date" required>
                <x-ui.input name="report_date" type="date" :value="$values['report_date'] ?? null" max="{{ now(\App\Services\Academic\Format::timezone())->toDateString() }}" required />
            </x-ui.field>
            <x-ui.field label="Mulai" for="start_time" required>
                <x-ui.input name="start_time" type="time" :value="$values['start_time'] ?? null" required />
            </x-ui.field>
            <x-ui.field label="Selesai" for="end_time" required>
                <x-ui.input name="end_time" type="time" :value="$values['end_time'] ?? null" required />
            </x-ui.field>
        </div>

        @if ($syllabuses->isNotEmpty())
            <x-ui.field label="Pertemuan" for="meeting_number" required>
                <x-ui.select name="meeting_number" :options="$meetingOptions" :value="$values['meeting_number'] ?? null" x-model="meeting" x-on:change="materialId = ''" required />
            </x-ui.field>
            <input type="hidden" name="syllabus_id" x-bind:value="current ? current.id : ''" value="{{ old('syllabus_id', $values['syllabus_id'] ?? '') }}">
            <x-ui.field label="Materi yang dipakai" for="material_id" optional>
                <select id="material_id" name="material_id" class="input" x-model="materialId" x-bind:disabled="! current || current.materials.length === 0"
                    @error('material_id') aria-invalid="true" aria-describedby="material_id-error" @enderror>
                    <option value="">Tanpa materi khusus</option>
                    <template x-for="material in (current ? current.materials : [])" x-bind:key="material.id">
                        <option x-bind:value="material.id" x-text="material.title" x-bind:selected="String(material.id) === String(materialId)"></option>
                    </template>
                </select>
            </x-ui.field>
        @else
            <x-ui.alert tone="warning" title="Kelas belum memiliki kurikulum">Isi nomor pertemuan secara manual. Progres siswa baru tercatat setelah admin menetapkan kurikulum kelas.</x-ui.alert>
            <x-ui.field label="Pertemuan ke" for="meeting_number" required>
                <x-ui.input name="meeting_number" type="number" min="1" max="200" inputmode="numeric" class="sm:w-32" :value="$values['meeting_number'] ?? 1" required />
            </x-ui.field>
        @endif

        <x-ui.field label="Konsep yang dipelajari" for="learning_concept" required>
            <x-ui.textarea name="learning_concept" rows="3" :value="$values['learning_concept'] ?? null" x-model="concept" required maxlength="2000" />
        </x-ui.field>
        @if ($syllabuses->isNotEmpty())
            <div class="-mt-3">
                <x-ui.button variant="link" size="sm" icon="sparkles" x-on:click="fillConcept()" x-bind:disabled="! current">Isi dari silabus</x-ui.button>
            </div>
        @endif

        <x-ui.field label="Tautan dokumentasi" for="learning_media_link" optional
            hint="Hanya dari penyimpanan lembaga ({{ $hosts }}). Foto/video anak hanya boleh dilampirkan bila orang tua mengizinkan dokumentasi.">
            <x-ui.input name="learning_media_link" type="url" icon="photo" :value="$values['learning_media_link'] ?? null" placeholder="https://drive.google.com/…" inputmode="url" />
        </x-ui.field>
    </div>
</div>
