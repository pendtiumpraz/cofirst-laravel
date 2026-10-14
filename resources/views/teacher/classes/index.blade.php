@php
    use App\Services\Teacher\TeachingSchedule;

    $statusTone = ['active' => 'success', 'planned' => 'info', 'completed' => 'neutral', 'cancelled' => 'danger'];
    $emptyCopy = [
        'berlangsung' => ['Belum ada kelas yang berlangsung.', 'Kelas yang sedang berjalan dan Anda ajar akan tampil di sini.'],
        'direncanakan' => ['Belum ada kelas yang direncanakan.', 'Admin akan menambahkan Anda ke kelas baru sebelum dimulai.'],
        'selesai' => ['Belum ada kelas yang selesai.', 'Riwayat kelas yang sudah selesai akan tersimpan di sini.'],
    ];
@endphp

<x-app-layout title="Kelas Saya">
    <x-ui.page-header title="Kelas saya" subtitle="Kelas yang Anda ajar, lengkap dengan progres pertemuan dan jadwal berikutnya.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="calendar-days" :href="route('teacher.schedules.index')">Jadwal mengajar</x-ui.button>
        </x-slot:actions>
        <x-ui.tabs :tabs="$tabs" :active="$tab" label="Status kelas" />
    </x-ui.page-header>

    @if ($classes->isEmpty())
        <x-ui.empty-state size="page" icon="rectangle-group" :title="$emptyCopy[$tab][0]" :description="$emptyCopy[$tab][1]" />
    @else
        <ul class="grid gap-gutter md:grid-cols-2 xl:grid-cols-3" role="list">
            @foreach ($classes as $class)
                @php
                    $held = $meetingsHeld[$class->id] ?? 0;
                    $total = $class->curriculum?->syllabuses_count ?: null;
                    $next = $nextSessions->get($class->id);
                    $isRunning = $class->status === 'active' && $class->is_active;
                @endphp
                <x-ui.card as="li" class="flex flex-col" padding="none">
                    <div class="flex flex-1 flex-col gap-4 p-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="eyebrow mb-1 truncate">{{ $class->course?->name ?? 'Kursus' }}</p>
                                <h2 class="card-title">
                                    <a href="{{ route('teacher.class-detail', $class) }}" class="hover:text-brand-text hover:underline">{{ $class->name }}</a>
                                </h2>
                            </div>
                            <x-ui.badge :tone="$statusTone[$class->status] ?? 'neutral'" dot class="shrink-0">{{ $class->status_label }}</x-ui.badge>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <x-ui.badge>{{ $class->type_label }}</x-ui.badge>
                            <x-ui.badge :icon="$class->delivery_method === 'online' ? 'video-camera' : 'map-pin'">
                                {{ $class->delivery_method === 'online' ? 'Online' : 'Tatap muka' }}
                            </x-ui.badge>
                        </div>

                        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                            <div>
                                <dt class="text-xs text-fg-subtle">Siswa aktif</dt>
                                <dd class="font-num text-fg">{{ $class->active_students_count }}<span class="text-fg-subtle">/{{ $class->max_students }}</span></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-fg-subtle">Periode</dt>
                                <dd class="text-fg">
                                    {{ $class->start_date ? TeachingSchedule::format($class->start_date, 'j M') : '—' }}
                                    – {{ $class->end_date ? TeachingSchedule::format($class->end_date, 'j M Y') : '—' }}
                                </dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-xs text-fg-subtle">Pertemuan berikutnya</dt>
                                <dd class="text-fg">
                                    @if ($next)
                                        {{ TeachingSchedule::format($next->date, 'l, j M') }} · {{ $next->start }}–{{ $next->end }}
                                        @if ($next->room)<span class="text-fg-muted"> · {{ $next->room }}</span>@endif
                                    @elseif ($isRunning)
                                        <span class="text-fg-muted">Belum ada jadwal dalam 2 minggu ke depan</span>
                                    @else
                                        <span class="text-fg-muted">—</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <x-ui.progress :value="$total ? min($held, $total) : 0" :max="$total ?: 1" size="sm"
                            :tone="$class->status === 'completed' ? 'success' : 'brand'"
                            :label="$total ? 'Pertemuan '.min($held, $total).' dari '.$total : $held.' pertemuan tercatat'" />

                        <div class="mt-auto flex items-center justify-between gap-3 pt-1">
                            <div class="flex -space-x-2" aria-label="Pengajar: {{ $class->teachers->pluck('name')->implode(', ') }}">
                                @foreach ($class->teachers->take(4) as $teacherRow)
                                    <x-ui.avatar :user="$teacherRow" size="xs" class="ring-2 ring-surface" />
                                @endforeach
                            </div>
                            <p class="truncate text-xs text-fg-subtle">
                                {{ $class->teachers->count() > 1 ? 'Bersama '.$class->teachers->where('id', '!=', $teacher->id)->pluck('name')->implode(', ') : 'Pengajar tunggal' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-line bg-surface-2 px-card py-3">
                        <x-ui.button size="sm" variant="secondary" :href="route('teacher.class-detail', $class)" icon="eye">Detail</x-ui.button>
                        @if ($isRunning)
                            <x-ui.button size="sm" variant="ghost" icon="clipboard-document-check" :href="route('teacher.attendance.take', $class)">Absensi</x-ui.button>
                            @if (Route::has('class-reports.create'))
                                <x-ui.button size="sm" variant="ghost" icon="document-text" :href="route('class-reports.create', ['class_id' => $class->id])">Berita acara</x-ui.button>
                            @endif
                        @endif
                    </div>
                </x-ui.card>
            @endforeach
        </ul>
    @endif
</x-app-layout>
