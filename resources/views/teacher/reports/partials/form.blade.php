{{--
    Progress report form. Params: $report, $types, $action, $method, $submit, optional $options (create only:
    [{id, name, course, students: [{id, name}]}]). On edit, class and student are fixed.
--}}
@php($creating = ! $report->exists)
<form method="POST" action="{{ $action }}" novalidate
    x-data="{
        isSubmitting: false,
        classId: @js((string) old('class_id', $report->class_id)),
        studentId: @js((string) old('student_id', $report->student_id)),
        progress: @js((int) old('progress_percentage', (int) $report->progress_percentage)),
        options: @js($options ?? []),
        get students() {
            const found = this.options.find(o => String(o.id) === String(this.classId));
            return found ? found.students : [];
        },
    }"
    x-on:submit="isSubmitting = true">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Siswa</h2>
                <p class="mt-1 text-sm text-fg-muted">Laporan hanya dapat dibuat untuk siswa yang terdaftar (aktif atau sudah lulus) di kelas Anda. Siswa dan orang tuanya dapat membaca laporan ini.</p>
            </div>
            <div class="form-section-body">
                @if ($creating)
                    @if (empty($options) || collect($options)->every(fn ($o) => empty($o['students'])))
                        <x-ui.alert tone="warning" title="Belum ada siswa aktif">Kelas Anda belum memiliki siswa aktif, sehingga laporan belum dapat dibuat.</x-ui.alert>
                    @endif
                    <x-ui.field label="Kelas" for="class_id" required>
                        <select id="class_id" name="class_id" class="input" x-model="classId" x-on:change="studentId = ''" required
                            @error('class_id') aria-invalid="true" aria-describedby="class_id-error" @enderror>
                            <option value="">Pilih kelas</option>
                            @foreach ($options as $option)
                                <option value="{{ $option['id'] }}" @selected((string) old('class_id', $report->class_id) === (string) $option['id'])>
                                    {{ $option['name'] }}{{ $option['course'] ? ' · '.$option['course'] : '' }}
                                </option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Siswa" for="student_id" required hint="Daftar menyesuaikan kelas yang dipilih.">
                        <select id="student_id" name="student_id" class="input" x-model="studentId" required x-bind:disabled="students.length === 0"
                            @error('student_id') aria-invalid="true" aria-describedby="student_id-error" @enderror>
                            <option value="">Pilih siswa</option>
                            <template x-for="student in students" x-bind:key="student.id">
                                <option x-bind:value="student.id" x-text="student.name" x-bind:selected="String(student.id) === String(studentId)"></option>
                            </template>
                        </select>
                    </x-ui.field>
                @else
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div><dt class="label">Siswa</dt><dd class="font-medium text-fg">{{ $report->student?->name }}</dd></div>
                        <div><dt class="label">Kelas</dt><dd class="text-fg">{{ $report->className?->name }}</dd></div>
                    </dl>
                @endif
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Jenis laporan" for="type" required>
                        <x-ui.select name="type" :options="$types" :value="$report->type" required />
                    </x-ui.field>
                    <x-ui.field label="Tanggal laporan" for="report_date" required>
                        <x-ui.input name="report_date" type="date" :value="optional($report->report_date)->format('Y-m-d')" max="{{ now(\App\Services\Academic\Format::timezone())->toDateString() }}" required />
                    </x-ui.field>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Isi laporan</h2>
                <p class="mt-1 text-sm text-fg-muted">Tulis dengan bahasa yang mudah dipahami orang tua: apa yang sudah dikuasai, apa yang perlu dilatih.</p>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Judul" for="title" required>
                    <x-ui.input name="title" :value="$report->title" placeholder="cth. Laporan bulanan Oktober" required maxlength="255" />
                </x-ui.field>
                <x-ui.field label="Progres belajar" for="progress_percentage" required hint="Perkiraan penguasaan materi sejauh ini.">
                    <div class="flex items-center gap-4">
                        <input type="range" min="0" max="100" step="5" class="h-11 flex-1 accent-brand" x-model.number="progress" aria-label="Geser progres">
                        <x-ui.input name="progress_percentage" type="number" min="0" max="100" inputmode="numeric" suffix="%" class="w-24" x-model.number="progress" :value="$report->progress_percentage !== null ? (int) $report->progress_percentage : 0" required />
                    </div>
                </x-ui.field>
                <x-ui.field label="Nilai" for="score" optional hint="Isi bila ada penilaian 0–100.">
                    <x-ui.input name="score" type="number" min="0" max="100" step="0.5" inputmode="decimal" class="sm:w-40" :value="$report->score !== null ? (float) $report->score : null" />
                </x-ui.field>
                <x-ui.field label="Isi laporan" for="content" required>
                    <x-ui.textarea name="content" rows="7" :value="$report->content" required />
                </x-ui.field>
                <x-ui.field label="Rekomendasi" for="recommendations" optional>
                    <x-ui.textarea name="recommendations" rows="4" :value="$report->recommendations" placeholder="Latihan di rumah, materi yang perlu diulang, langkah berikutnya." />
                </x-ui.field>
            </div>
        </div>
    </x-ui.card>

    <div class="form-actions mt-6">
        <x-ui.button variant="secondary" :href="$report->exists ? route('teacher.reports.show', $report) : route('teacher.reports.index')">Batal</x-ui.button>
        <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">{{ $submit }}</x-ui.button>
    </div>
</form>
