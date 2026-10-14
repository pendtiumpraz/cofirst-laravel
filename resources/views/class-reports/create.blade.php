@php($f = \App\Services\Academic\Format::class)
<x-app-layout title="Buat berita acara">
    <x-ui.page-header title="Buat berita acara"
        subtitle="Satu formulir untuk satu pertemuan: isi materi yang dipelajari, lalu catatan untuk tiap siswa yang hadir."
        :breadcrumbs="['Berita acara' => route('class-reports.index'), 'Buat' => null]" />

    @if ($classes->isEmpty())
        <x-ui.empty-state size="page" icon="user-group" title="Anda belum mengajar kelas yang sedang berjalan."
            description="Berita acara dapat dibuat setelah admin menugaskan Anda ke sebuah kelas." />
    @else
        <x-ui.card class="mb-6" padding="sm">
            <form method="GET" action="{{ route('class-reports.create') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <x-ui.field label="Kelas" for="pilih_kelas" class="flex-1">
                    <x-ui.select name="class_id" id="pilih_kelas" :error="false" :value="$class?->id" placeholder="Pilih kelas yang akan dilaporkan"
                        :options="$classes->mapWithKeys(fn ($c) => [$c->id => $c->name.($c->course ? ' · '.$c->course->name : '')])->all()"
                        x-data x-on:change="$el.form.requestSubmit()" />
                </x-ui.field>
                <noscript><button type="submit" class="btn btn-secondary">Pilih</button></noscript>
            </form>
            @if (! $class && $pending->isNotEmpty())
                <div class="mt-4 border-t border-line pt-4">
                    <p class="eyebrow mb-2">Belum dilaporkan</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($pending->take(6) as $item)
                            <x-ui.button size="sm" variant="secondary"
                                :href="route('class-reports.create', ['class_id' => $item['class']->id, 'date' => $item['date']->toDateString(), 'schedule_id' => $item['schedule_id']])">
                                {{ $item['class']->name }} · {{ $f::date($item['date'], 'd M') }}
                            </x-ui.button>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-ui.card>

        @if ($class && $form)
            @if ($form['roster']->isEmpty())
                <x-ui.empty-state size="page" icon="user-group" title="Kelas ini belum memiliki siswa aktif."
                    description="Berita acara ditulis per siswa, jadi kelas perlu memiliki siswa terdaftar." />
            @else
                <form method="POST" action="{{ route('class-reports.store') }}" x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true" novalidate>
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $class->id }}">
                    <input type="hidden" name="schedule_id" value="{{ old('schedule_id', $form['schedule_id']) }}">

                    @error('students')
                        <x-ui.alert tone="danger" class="mb-6">{{ $message }}</x-ui.alert>
                    @enderror

                    <x-ui.card>
                        @include('class-reports.partials.session-fields', [
                            'syllabuses' => $form['syllabuses'],
                            'values' => [
                                'report_date' => $form['date'],
                                'start_time' => $form['start'],
                                'end_time' => $form['end'],
                                'meeting_number' => $form['meeting'],
                                'learning_concept' => '',
                            ],
                        ])

                        <div class="form-section">
                            <div class="form-section-intro">
                                <h2 class="card-title">Siswa yang hadir</h2>
                                <p class="mt-1 text-sm text-fg-muted">Centang siswa yang mengikuti pertemuan. Setiap siswa mendapat berita acara sendiri dan progres pertemuannya ditandai selesai.</p>
                            </div>
                            <div class="form-section-body">
                                @foreach ($form['roster'] as $i => $student)
                                    @php($status = $form['attendance'][$student->id] ?? null)
                                    @php($defaultIncluded = ! in_array($status, ['absent', 'excused'], true))
                                    <div class="rounded-xl border border-line p-4" x-data="{ on: @js(old() ? (bool) old('students.'.$i.'.include') : $defaultIncluded) }"
                                        x-bind:class="on ? 'bg-surface' : 'bg-surface-2'">
                                        <input type="hidden" name="students[{{ $i }}][student_id]" value="{{ $student->id }}">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <label class="flex min-h-[44px] cursor-pointer items-center gap-3">
                                                <input type="checkbox" class="check-input" name="students[{{ $i }}][include]" value="1" x-model="on">
                                                <x-ui.avatar :name="$student->name" size="sm" decorative />
                                                <span class="font-medium text-fg">{{ $student->name }}</span>
                                            </label>
                                            @if ($status)
                                                <x-ui.badge :tone="in_array($status, ['present', 'late'], true) ? 'success' : 'warning'">
                                                    {{ \App\Models\Attendance::statuses()[$status] ?? $status }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                        <div x-show="on" x-collapse class="mt-4">
                                            @include('class-reports.partials.student-fields', [
                                                'prefix' => 'students['.$i.']',
                                                'values' => [],
                                                'open' => false,
                                            ])
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </x-ui.card>

                    <div class="form-actions mt-6">
                        <x-ui.button variant="secondary" :href="route('class-reports.index')">Batal</x-ui.button>
                        <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan berita acara</x-ui.button>
                    </div>
                </form>
            @endif
        @elseif (! $class)
            <x-ui.empty-state size="page" icon="clipboard-document-list" title="Pilih kelas untuk mulai."
                description="Formulir pertemuan dan daftar siswa muncul setelah kelas dipilih." />
        @endif
    @endif
</x-app-layout>
