{{--
    Shared slot form. Expects $classes (options), $preset (values), $duration (minutes), $rooms,
    and optionally $slot (when editing).
--}}
@php
    use App\Models\Schedule;

    $editing = isset($slot);
    $values = [
        'class_id' => (string) old('class_id', $preset['class_id']),
        'day_of_week' => (string) old('day_of_week', $preset['day_of_week']),
        'start_time' => (string) old('start_time', $preset['start_time']),
        'end_time' => (string) old('end_time', $preset['end_time']),
        'teacher_id' => (string) old('teacher_id', $preset['teacher_id']),
        'enrollment_ids' => array_map('intval', (array) old('enrollment_ids', $preset['enrollment_ids'])),
    ];
    $teachersUrl = route('admin.schedule-data.teachers', ['class' => '__ID__']);
    $studentsUrl = route('admin.schedule-data.students', ['class' => '__ID__']);
@endphp

<div x-data="{
        classId: @js($values['class_id']),
        teacherId: @js($values['teacher_id']),
        selected: @js($values['enrollment_ids']),
        selectAll: @js(! $editing && ! old('enrollment_ids')),
        start: @js($values['start_time']),
        end: @js($values['end_time']),
        duration: @js($duration),
        teachers: [], students: [], loading: false, failed: false,
        async load(initial = false) {
            this.failed = false;
            if (! this.classId) { this.teachers = []; this.students = []; return; }
            this.loading = true;
            try {
                const opts = { headers: { 'Accept': 'application/json' } };
                const [t, s] = await Promise.all([
                    fetch(@js($teachersUrl).replace('__ID__', this.classId), opts),
                    fetch(@js($studentsUrl).replace('__ID__', this.classId), opts),
                ]);
                if (! t.ok || ! s.ok) throw new Error('load');
                this.teachers = (await t.json()).data || [];
                this.students = (await s.json()).data || [];
                if (! initial || ! this.teachers.some(x => String(x.id) === String(this.teacherId))) {
                    this.teacherId = this.teachers.length === 1 ? String(this.teachers[0].id) : (initial ? this.teacherId : '');
                }
                if (! initial || this.selectAll) {
                    this.selected = this.students.map(x => x.enrollment_id);
                }
            } catch (e) { this.failed = true; }
            finally { this.loading = false; }
        },
        suggestEnd() {
            if (! this.start || this.end) return;
            const [h, m] = this.start.split(':').map(Number);
            const total = h * 60 + m + Number(this.duration || 90);
            this.end = String(Math.floor(total / 60) % 24).padStart(2, '0') + ':' + String(total % 60).padStart(2, '0');
        },
        toggleAll(on) { this.selected = on ? this.students.map(x => x.enrollment_id) : []; },
    }" x-init="load(true)">

    <section class="form-section" aria-labelledby="sec-kelas-jadwal">
        <div class="form-section-intro">
            <h2 id="sec-kelas-jadwal" class="card-title">Kelas & waktu</h2>
            <p class="mt-1 text-sm text-fg-muted">Jadwal berulang setiap minggu selama periode kelas. Jam memakai zona waktu sekolah ({{ App\Services\Admin\AdminClock::timezone() === 'Asia/Jakarta' ? 'WIB' : App\Services\Admin\AdminClock::timezone() }}).</p>
        </div>
        <div class="form-section-body">
            <x-ui.field label="Kelas" for="class_id" required hint="Hanya kelas aktif yang punya pengajar dan siswa.">
                <x-ui.select name="class_id" :options="$classes" :value="$values['class_id']" placeholder="Pilih kelas" required
                    x-model="classId" x-on:change="load()" />
            </x-ui.field>
            <div class="grid gap-5 sm:grid-cols-3">
                <x-ui.field label="Hari" for="day_of_week" required>
                    <x-ui.select name="day_of_week" :options="Schedule::DAYS" :value="$values['day_of_week']" placeholder="Pilih hari" required />
                </x-ui.field>
                <x-ui.field label="Jam mulai" for="start_time" required>
                    <x-ui.input name="start_time" type="time" step="300" :value="$values['start_time']" required x-model="start" x-on:change="suggestEnd()" />
                </x-ui.field>
                <x-ui.field label="Jam selesai" for="end_time" required :hint="'Default '.$duration.' menit'">
                    <x-ui.input name="end_time" type="time" step="300" :value="$values['end_time']" required x-model="end" />
                </x-ui.field>
            </div>
            <x-ui.field label="Ruang atau lokasi" for="room" optional hint="Mis. Lab 1, Online, atau Rumah siswa. Ruang fisik dicek agar tidak dipakai dua kelas sekaligus.">
                <x-ui.input name="room" :value="$preset['room']" list="daftar-ruang" maxlength="100" icon="map-pin" />
                <datalist id="daftar-ruang">
                    @foreach ($rooms as $room)
                        <option value="{{ $room }}"></option>
                    @endforeach
                </datalist>
            </x-ui.field>
        </div>
    </section>

    <section class="form-section" aria-labelledby="sec-peserta">
        <div class="form-section-intro">
            <h2 id="sec-peserta" class="card-title">Pengajar & siswa</h2>
            <p class="mt-1 text-sm text-fg-muted">Pengajar diambil dari pengajar kelas. Siswa yang dicentang mendapat jadwal ini; siswa baru yang ditambahkan ke kelas otomatis ikut.</p>
        </div>
        <div class="form-section-body">
            <p class="text-sm text-fg-subtle" x-show="! classId">Pilih kelas terlebih dahulu.</p>
            <p class="text-sm text-fg-subtle" x-show="loading" x-cloak>Memuat pengajar dan siswa…</p>
            <x-ui.alert tone="danger" x-show="failed" x-cloak>Data kelas gagal dimuat. Pilih ulang kelasnya atau muat ulang halaman.</x-ui.alert>

            <div x-show="classId && ! loading" x-cloak class="space-y-5">
                <x-ui.field label="Pengajar" for="teacher_id" required>
                    <select id="teacher_id" name="teacher_id" class="input" x-model="teacherId" required
                        @error('teacher_id') aria-invalid="true" aria-describedby="teacher_id-error" @enderror>
                        <option value="">Pilih pengajar</option>
                        <template x-for="t in teachers" :key="t.id">
                            <option x-bind:value="String(t.id)" x-text="t.name" x-bind:selected="String(t.id) === teacherId"></option>
                        </template>
                    </select>
                </x-ui.field>

                <fieldset>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <legend class="label">Siswa <span class="label-required" aria-hidden="true">*</span></legend>
                        <div class="flex gap-1" x-show="students.length > 1">
                            <button type="button" class="btn btn-ghost btn-xs" x-on:click="toggleAll(true)">Pilih semua</button>
                            <button type="button" class="btn btn-ghost btn-xs" x-on:click="toggleAll(false)">Kosongkan</button>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-fg-subtle" x-show="! students.length">Kelas ini belum punya siswa aktif. Tambahkan siswa dari halaman kelas terlebih dahulu.</p>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        <template x-for="s in students" :key="s.enrollment_id">
                            <label class="check">
                                <input type="checkbox" class="check-input" name="enrollment_ids[]" x-bind:value="s.enrollment_id" x-model.number="selected">
                                <span class="check-label">
                                    <span x-text="s.name"></span>
                                    <span class="check-description" x-show="s.pending">Menunggu persetujuan</span>
                                </span>
                            </label>
                        </template>
                    </div>
                    @foreach (['enrollment_ids', 'enrollment_ids.*'] as $key)
                        @error($key)<p id="enrollment_ids-error" class="error-text mt-2"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>@enderror
                    @endforeach
                </fieldset>
            </div>

            <x-ui.toggle name="is_active" unchecked-value="0" :checked="(bool) old('is_active', $preset['is_active'])"
                label="Jadwal aktif" description="Jadwal nonaktif tidak tampil di kalender guru, siswa dan orang tua." />
        </div>
    </section>
</div>
