{{--
    Shared class form. Expects $class, $courses, $curriculums (id => title for the current course),
    $teachers, $types, $statuses, $delivery, $suggested, $typeMax.
--}}
@php
    $editing = $class->exists;
    $selectedTeachers = array_map('intval', (array) old('teacher_ids', $editing ? $class->teachers->pluck('id')->all() : []));
    $curriculumUrl = route('admin.courses.curriculum', ['course' => '__ID__']);
@endphp

<div x-data="{
        courseId: @js((string) old('course_id', $class->course_id)),
        curriculumId: @js((string) old('curriculum_id', $class->curriculum_id)),
        curriculums: @js($curriculums->map(fn ($title, $id) => ['id' => $id, 'title' => $title])->values()),
        loading: false,
        type: @js((string) old('type', $class->type)),
        capacity: @js((string) old('max_students', $class->max_students)),
        suggested: @js($suggested),
        typeMax: @js($typeMax),
        teacherQuery: '',
        async loadCurriculums() {
            this.curriculums = []; this.curriculumId = '';
            if (! this.courseId) return;
            this.loading = true;
            try {
                const res = await fetch(@js($curriculumUrl).replace('__ID__', this.courseId), { headers: { 'Accept': 'application/json' } });
                const json = res.ok ? await res.json() : { curriculums: [] };
                this.curriculums = json.curriculums || [];
            } catch (e) { this.curriculums = []; }
            finally { this.loading = false; }
        },
        applySuggestion() {
            const max = this.typeMax[this.type];
            if (! this.capacity || (max && Number(this.capacity) > max)) { this.capacity = String(this.suggested[this.type] ?? ''); }
        },
    }">

    <section class="form-section" aria-labelledby="sec-kelas">
        <div class="form-section-intro">
            <h2 id="sec-kelas" class="card-title">Informasi kelas</h2>
            <p class="mt-1 text-sm text-fg-muted">Kelas adalah rombongan belajar dari satu kursus. Kurikulum boleh dipilih nanti.</p>
        </div>
        <div class="form-section-body">
            <x-ui.field label="Nama kelas" for="name" required>
                <x-ui.input name="name" :value="$class->name" required maxlength="150" placeholder="cth. Python Remaja — Sabtu Pagi" />
            </x-ui.field>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Kursus" for="course_id" required>
                    <x-ui.select name="course_id" placeholder="Pilih kursus" required x-model="courseId" x-on:change="loadCurriculums()"
                        :value="$class->course_id"
                        :options="$courses->map(fn ($c) => ['value' => $c->id, 'label' => $c->name.($c->is_active ? '' : ' (nonaktif)')])->all()" />
                </x-ui.field>
                <x-ui.field label="Kurikulum" for="curriculum_id" optional>
                    <select id="curriculum_id" name="curriculum_id" class="input" x-model="curriculumId" x-bind:disabled="loading"
                        @error('curriculum_id') aria-invalid="true" aria-describedby="curriculum_id-error" @enderror>
                        <option value="">Tanpa kurikulum</option>
                        <template x-for="c in curriculums" :key="c.id">
                            <option x-bind:value="String(c.id)" x-text="c.title" x-bind:selected="String(c.id) === curriculumId"></option>
                        </template>
                    </select>
                    <p class="hint" x-show="courseId && ! loading && curriculums.length === 0" x-cloak>Kursus ini belum punya kurikulum. Kelas tetap bisa dibuat.</p>
                </x-ui.field>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Tipe kelas" for="type" required>
                    <x-ui.select name="type" :options="$types" :value="$class->type" placeholder="Pilih tipe" required x-model="type" x-on:change="applySuggestion()" />
                </x-ui.field>
                <x-ui.field label="Metode belajar" for="delivery_method" optional>
                    <x-ui.select name="delivery_method" :options="$delivery" :value="$class->delivery_method" placeholder="Belum ditentukan" />
                </x-ui.field>
            </div>
            <x-ui.field label="Deskripsi" for="description" optional>
                <x-ui.textarea name="description" rows="3" :value="$class->description" maxlength="2000" />
            </x-ui.field>
        </div>
    </section>

    <section class="form-section" aria-labelledby="sec-periode">
        <div class="form-section-intro">
            <h2 id="sec-periode" class="card-title">Periode & kapasitas</h2>
            <p class="mt-1 text-sm text-fg-muted">Kelas privat berisi 1 siswa; kelas grup anak maksimal 5. Pendaftaran yang menunggu juga memakai kursi.</p>
        </div>
        <div class="form-section-body">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Tanggal mulai" for="start_date" required>
                    <x-ui.input name="start_date" type="date" :value="$class->start_date?->format('Y-m-d')" required />
                </x-ui.field>
                <x-ui.field label="Tanggal selesai" for="end_date" required>
                    <x-ui.input name="end_date" type="date" :value="$class->end_date?->format('Y-m-d')" required />
                </x-ui.field>
                <x-ui.field label="Kapasitas" for="max_students" required>
                    <x-ui.input name="max_students" type="number" min="1" max="500" :value="$class->max_students" suffix="siswa" required x-model="capacity" />
                    <p class="hint" x-show="typeMax[type]" x-cloak>Maksimal <span x-text="typeMax[type]"></span> siswa untuk tipe ini.</p>
                </x-ui.field>
                <x-ui.field label="Status" for="status" required>
                    <x-ui.select name="status" :options="$statuses" :value="$class->status" required />
                </x-ui.field>
            </div>
        </div>
    </section>

    <section class="form-section" aria-labelledby="sec-pengajar">
        <div class="form-section-intro">
            <h2 id="sec-pengajar" class="card-title">Pengajar</h2>
            <p class="mt-1 text-sm text-fg-muted">Semua pengajar kelas setara. Mengganti pengajar otomatis memindahkan jadwal ke pengajar yang tersisa.</p>
        </div>
        <div class="form-section-body">
            <fieldset>
                <legend class="label">Pengajar kelas <span class="label-required" aria-hidden="true">*</span></legend>
                @if ($teachers->isEmpty())
                    <x-ui.alert tone="warning" class="mt-2">Belum ada akun guru aktif. Tambahkan guru di menu Pengguna terlebih dahulu.</x-ui.alert>
                @else
                    @if ($teachers->count() > 8)
                        <div class="input-group mt-2 sm:max-w-xs">
                            <x-ui.icon name="magnifying-glass" class="input-icon" />
                            <input type="search" class="input" placeholder="Saring nama guru…" x-model="teacherQuery" aria-label="Saring nama guru">
                        </div>
                    @endif
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach ($teachers as $teacher)
                            <div x-show="! teacherQuery || @js(mb_strtolower($teacher->name)).includes(teacherQuery.toLowerCase())">
                                <x-ui.checkbox name="teacher_ids[]" :value="$teacher->id" :checked="in_array($teacher->id, $selectedTeachers, true)" :label="$teacher->name" />
                            </div>
                        @endforeach
                    </div>
                @endif
                @foreach (['teacher_ids', 'teacher_ids.*'] as $key)
                    @error($key)<p class="error-text mt-2"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>@enderror
                @endforeach
            </fieldset>
        </div>
    </section>

    <section class="form-section" aria-labelledby="sec-foto-kelas">
        <div class="form-section-intro">
            <h2 id="sec-foto-kelas" class="card-title">Foto sampul <span class="text-sm font-normal text-fg-subtle">(opsional)</span></h2>
            <p class="mt-1 text-sm text-fg-muted">Gunakan foto suasana/ruang kelas. Foto yang menampilkan anak memerlukan persetujuan orang tua.</p>
        </div>
        <div class="form-section-body">
            @if ($editing && $class->photo_path)
                <div class="flex items-center gap-4">
                    <img src="{{ $class->photo_url }}" alt="Sampul {{ $class->name }}" class="h-20 w-36 rounded-md border border-line object-cover">
                    <x-ui.checkbox name="remove_photo" value="1" label="Hapus foto saat ini" />
                </div>
            @endif
            <x-ui.field label="Unggah foto" for="photo">
                <x-ui.file name="photo" accept="image/jpeg,image/png,image/webp" :max-kb="2048" hint="JPG/PNG/WEBP · maks. 2 MB" preview />
            </x-ui.field>
        </div>
    </section>
</div>
