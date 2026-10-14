@php
    use App\Models\Attendance;
    use App\Models\Schedule;
    use App\Services\Teacher\AttendanceRecorder;
    use App\Services\Teacher\TeachingSchedule;

    $statusTone = ['active' => 'success', 'planned' => 'info', 'completed' => 'neutral', 'cancelled' => 'danger'];
    $canTake = $class->canShowInCalendar();
    $tones = AttendanceRecorder::tones();
    $statuses = Attendance::statuses();
    $roster = $class->active_students_count;
    $reportUrl = fn (array $params = []) => Route::has('class-reports.create') ? route('class-reports.create', ['class_id' => $class->id] + $params) : null;
@endphp

<x-app-layout :title="$class->name">
    <x-ui.page-header :title="$class->name"
        :subtitle="($class->course?->name ?? 'Kursus').' · '.$class->type_label"
        :breadcrumbs="['Kelas saya' => route('teacher.classes'), $class->name => null]">
        <x-slot:badge>
            <x-ui.badge :tone="$statusTone[$class->status] ?? 'neutral'" dot>{{ $class->status_label }}</x-ui.badge>
        </x-slot:badge>
        <x-slot:actions>
            @if ($canTake)
                <x-ui.button icon="clipboard-document-check" :href="route('teacher.attendance.take', $class)">Isi absensi</x-ui.button>
                @if ($reportUrl())
                    <x-ui.button variant="secondary" icon="document-text" :href="$reportUrl()">Berita acara</x-ui.button>
                @endif
            @endif
            <x-ui.dropdown label="Lainnya" icon="ellipsis-horizontal" variant="secondary">
                @if (Route::has('teacher.class-photos.upload') && $canTake)
                    <x-ui.dropdown-item icon="camera" :href="route('teacher.class-photos.upload', $class)">Unggah foto kelas</x-ui.dropdown-item>
                @endif
                @if (Route::has('teacher.class-photos.index'))
                    <x-ui.dropdown-item icon="photo" :href="route('teacher.class-photos.index', ['class_id' => $class->id])">Foto kelas</x-ui.dropdown-item>
                @endif
                @if (Route::has('teacher.reports.by-class'))
                    <x-ui.dropdown-item icon="document-chart-bar" :href="route('teacher.reports.by-class', $class)">Laporan siswa</x-ui.dropdown-item>
                @endif
                @if ($class->isCompleted() && Route::has('teacher.certificates.generate'))
                    <x-ui.dropdown-item icon="academic-cap" :href="route('teacher.certificates.generate', ['class_id' => $class->id])">Terbitkan sertifikat</x-ui.dropdown-item>
                @endif
                @if ($canTake)
                    <div class="dropdown-divider"></div>
                    <x-ui.dropdown-item icon="arrows-right-left" :href="route('teacher.handovers.create', ['class_id' => $class->id])">Ajukan serah terima</x-ui.dropdown-item>
                @endif
            </x-ui.dropdown>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.tabs group="kelas" active="ringkasan" label="Bagian kelas" :tabs="[
        'ringkasan' => ['label' => 'Ringkasan', 'icon' => 'squares-2x2'],
        'siswa' => ['label' => 'Siswa', 'count' => $enrollments->count(), 'icon' => 'user-group'],
        'jadwal' => ['label' => 'Jadwal', 'icon' => 'calendar-days'],
        'kurikulum' => ['label' => 'Kurikulum', 'icon' => 'queue-list'],
        'berita-acara' => ['label' => 'Berita acara', 'count' => $reports->total(), 'icon' => 'document-text'],
    ]">
        <x-ui.tab-panel name="ringkasan" class="space-y-6">
            <x-ui.metric-strip label="Ringkasan kelas" :stats="[
                ['label' => 'Siswa aktif', 'value' => (string) $roster, 'unit' => '/ '.$class->max_students, 'icon' => 'user-group'],
                ['label' => 'Kehadiran rata-rata', 'value' => $overallRate !== null ? number_format($overallRate, 1, ',', '.') : null, 'unit' => $overallRate !== null ? '%' : null, 'icon' => 'clipboard-document-check'],
                ['label' => 'Pertemuan tercatat', 'value' => (string) $meetingsHeld, 'unit' => $totalMeetings ? 'dari '.$totalMeetings : null, 'icon' => 'flag'],
                ['label' => 'Berita acara', 'value' => (string) $reports->total(), 'icon' => 'document-text'],
            ]" />

            <x-ui.card title="Informasi kelas">
                <dl class="grid gap-x-8 gap-y-5 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-fg-subtle">Kursus</dt>
                        <dd class="mt-0.5 text-fg">{{ $class->course?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Kurikulum</dt>
                        <dd class="mt-0.5 text-fg">{{ $class->curriculum?->title ?? 'Belum ditentukan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Jenis kelas</dt>
                        <dd class="mt-0.5 text-fg">{{ $class->type_label ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Metode</dt>
                        <dd class="mt-0.5 text-fg">{{ $class->delivery_method === 'online' ? 'Online' : 'Tatap muka' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Periode</dt>
                        <dd class="mt-0.5 text-fg">{{ TeachingSchedule::format($class->start_date, 'j M Y') }} – {{ TeachingSchedule::format($class->end_date, 'j M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Kapasitas</dt>
                        <dd class="mt-0.5 text-fg">{{ $roster }} dari {{ $class->max_students }} kursi terisi</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-fg-subtle">Pengajar</dt>
                        <dd class="mt-2 flex flex-wrap gap-3">
                            @foreach ($class->teachers as $person)
                                <span class="inline-flex items-center gap-2 rounded-badge border border-line py-1 pl-1 pr-3">
                                    <x-ui.avatar :user="$person" size="xs" decorative />
                                    <span class="text-fg">{{ $person->name }}</span>
                                    @if ($person->id === $teacher->id)<span class="text-xs text-fg-subtle">(Anda)</span>@endif
                                </span>
                            @endforeach
                        </dd>
                    </div>
                    @if (filled($class->description))
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-fg-subtle">Deskripsi</dt>
                            <dd class="mt-0.5 max-w-measure text-fg-muted">{{ $class->description }}</dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>
        </x-ui.tab-panel>

        <x-ui.tab-panel name="siswa">
            @include('teacher.classes.partials.roster')
        </x-ui.tab-panel>

        <x-ui.tab-panel name="jadwal">
            <div class="grid gap-6 lg:grid-cols-2">
                <x-ui.card title="Slot mingguan" subtitle="Jadwal rutin kelas ini" padding="none">
                    @if ($slots->isEmpty())
                        <x-ui.empty-state icon="calendar" title="Belum ada jadwal rutin."
                            description="Admin akan menambahkan jadwal setelah siswa terdaftar." />
                    @else
                        <ul class="divide-y divide-line" role="list">
                            @foreach ($slots as $slot)
                                <li class="flex items-center justify-between gap-4 px-4 py-3 sm:px-5">
                                    <div>
                                        <p class="font-medium text-fg">{{ Schedule::DAYS[$slot['day']] ?? '' }}</p>
                                        <p class="text-sm text-fg-muted">{{ $slot['room'] ?? 'Ruang belum ditentukan' }}</p>
                                    </div>
                                    <span class="font-num text-fg">{{ $slot['start'] }}–{{ $slot['end'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>

                <x-ui.card title="Pertemuan berikutnya" padding="none">
                    @if ($upcoming->isEmpty())
                        <x-ui.empty-state icon="calendar-days" title="Tidak ada pertemuan terjadwal."
                            :description="$class->isCompleted() ? 'Kelas ini sudah selesai.' : 'Belum ada pertemuan dalam 6 minggu ke depan.'" />
                    @else
                        <ul class="divide-y divide-line" role="list">
                            @foreach ($upcoming as $session)
                                <li class="flex items-center justify-between gap-4 px-4 py-3 sm:px-5">
                                    <div>
                                        <p class="font-medium text-fg">{{ TeachingSchedule::format($session->date, 'l, j M Y') }}</p>
                                        <p class="text-sm text-fg-muted">{{ $session->room ?? '—' }}</p>
                                    </div>
                                    <span class="font-num text-fg">{{ $session->start }}–{{ $session->end }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>
            </div>
        </x-ui.tab-panel>

        <x-ui.tab-panel name="kurikulum">
            <x-ui.card :title="$class->curriculum?->title ?? 'Kurikulum'" subtitle="Ketuntasan materi per pertemuan" padding="none">
                @if ($canTake && $class->curriculum && Route::has('teacher.curriculums.class-progress'))
                    <x-slot:actions>
                        <x-ui.button variant="secondary" size="sm" icon="pencil-square" :href="route('teacher.curriculums.class-progress', $class)">Perbarui progres</x-ui.button>
                    </x-slot:actions>
                @endif
                @if (! $class->curriculum || $class->curriculum->syllabuses->isEmpty())
                    <x-ui.empty-state icon="queue-list" title="Kurikulum belum diatur."
                        description="Hubungi admin akademik agar kelas ini memakai kurikulum kursusnya." />
                @else
                    <ol class="divide-y divide-line" role="list">
                        @foreach ($class->curriculum->syllabuses as $syllabus)
                            @php $done = (int) ($completedByMeeting[$syllabus->meeting_number] ?? 0); @endphp
                            <li class="grid gap-3 px-4 py-4 sm:grid-cols-[3rem_1fr_12rem] sm:items-center sm:px-5">
                                <span class="font-num text-sm text-fg-subtle">{{ str_pad((string) $syllabus->meeting_number, 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="min-w-0">
                                    <p class="font-medium text-fg">{{ $syllabus->title }}</p>
                                    @if ($syllabus->duration_minutes)
                                        <p class="text-xs text-fg-subtle">{{ $syllabus->duration_minutes }} menit</p>
                                    @endif
                                </div>
                                <x-ui.progress :value="$done" :max="max(1, $roster)" size="sm"
                                    :tone="$roster > 0 && $done >= $roster ? 'success' : 'brand'"
                                    :label="$done.' dari '.$roster.' siswa tuntas'" />
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-ui.card>
        </x-ui.tab-panel>

        <x-ui.tab-panel name="berita-acara">
            <x-ui.table caption="Berita acara kelas {{ $class->name }}" mobile="stack">
                <x-slot:head>
                    <x-ui.th>Tanggal</x-ui.th>
                    <x-ui.th>Siswa</x-ui.th>
                    <x-ui.th numeric>Pertemuan</x-ui.th>
                    <x-ui.th>Ditulis oleh</x-ui.th>
                    <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
                </x-slot:head>
                @foreach ($reports as $report)
                    <tr>
                        <td data-label="Tanggal">{{ TeachingSchedule::format($report->report_date, 'j M Y') }}</td>
                        <td class="cell-primary" data-label="Siswa">{{ $report->student?->name ?? '—' }}</td>
                        <td class="num" data-label="Pertemuan">{{ $report->meeting_number ?? '—' }}</td>
                        <td data-label="Ditulis oleh">{{ $report->teacher?->name ?? '—' }}</td>
                        <td class="cell-actions">
                            <x-ui.button variant="ghost" size="xs" icon="eye" :href="route('class-reports.show', $report)" label="Lihat berita acara {{ $report->student?->name }}" />
                        </td>
                    </tr>
                @endforeach
                <x-slot:empty>
                    <x-ui.empty-state icon="document-text" title="Belum ada berita acara."
                        description="Tulis berita acara setelah setiap pertemuan agar orang tua dapat mengikuti perkembangan anak.">
                        @if ($canTake && $reportUrl())
                            <x-slot:actions><x-ui.button size="sm" variant="secondary" :href="$reportUrl()">Tulis berita acara</x-ui.button></x-slot:actions>
                        @endif
                    </x-ui.empty-state>
                </x-slot:empty>
                @if ($reports->hasPages())
                    <x-slot:footer>{{ $reports->links() }}</x-slot:footer>
                @endif
            </x-ui.table>
        </x-ui.tab-panel>
    </x-ui.tabs>
</x-app-layout>
