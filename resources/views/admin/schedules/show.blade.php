@php
    $class = $slot->class();
    $teacher = $slot->teacher();
    $students = $slot->rows->filter(fn ($row) => $row->enrollment)->sortBy(fn ($row) => mb_strtolower((string) $row->enrollment->student?->name))->values();
    $enrollmentStatus = App\Models\Enrollment::statuses();
@endphp

<x-app-layout title="Detail jadwal">
    <x-ui.page-header :title="$class?->name ?? 'Jadwal'" :subtitle="$slot->dayName().', '.$slot->timeRange().($slot->room() ? ' · '.$slot->room() : '')"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Jadwal' => route('admin.schedules.index'), 'Detail' => null]">
        <x-slot:badge>
            <x-ui.badge :tone="$slot->isActive() ? 'success' : 'warning'" dot>{{ $slot->isActive() ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
        </x-slot:badge>
        <x-slot:actions>
            @can('update', $schedule)
                <form method="POST" action="{{ route('admin.schedules.toggle-status', $schedule) }}">
                    @csrf
                    <x-ui.button type="submit" variant="secondary" :icon="$slot->isActive() ? 'pause-circle' : 'play-circle'">
                        {{ $slot->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}
                    </x-ui.button>
                </form>
                <x-ui.button icon="pencil-square" :href="route('admin.schedules.edit', $schedule)">Ubah</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    @if ($clashReasons)
        <x-ui.alert tone="danger" icon="exclamation-triangle" class="mt-6" :title="'Bentrok '.implode(' & ', $clashReasons)">
            Pengajar atau ruang jadwal ini juga dipakai kelas lain pada jam yang tumpang tindih. Ubah jam, ruang atau pengajarnya.
        </x-ui.alert>
    @endif

    <div class="mt-8 grid grid-cols-1 gap-gutter lg:grid-cols-3">
        <x-ui.card title="Siswa di sesi ini" :subtitle="$students->count().' siswa'" padding="none" class="lg:col-span-2">
            @if ($students->isEmpty())
                <x-ui.empty-state icon="users" title="Belum ada siswa di sesi ini." />
            @else
                <ul class="divide-y divide-line" role="list">
                    @foreach ($students as $row)
                        @php $student = $row->enrollment->student; @endphp
                        <li class="flex items-center gap-3 px-card py-3">
                            <x-ui.avatar :user="$student" size="sm" decorative />
                            <a href="{{ route('admin.enrollments.show', $row->enrollment_id) }}" class="min-w-0 flex-1 truncate font-medium text-fg hover:text-brand-text">{{ $student?->name ?? 'Siswa' }}</a>
                            @if ($row->enrollment->status !== 'active')
                                <x-ui.badge tone="warning">{{ $enrollmentStatus[$row->enrollment->status] ?? $row->enrollment->status }}</x-ui.badge>
                            @endif
                            @unless ($row->is_active)
                                <x-ui.badge tone="neutral">Nonaktif</x-ui.badge>
                            @endunless
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <div class="flex flex-col gap-gutter">
            <x-ui.card title="Rincian">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-fg-muted">Kelas</dt>
                        <dd class="mt-1">
                            @if ($class)
                                <a href="{{ route('admin.classes.show', $class) }}" class="link">{{ $class->name }}</a>
                                <p class="text-xs text-fg-subtle">{{ $class->course?->name }}</p>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-fg-muted">Pengajar</dt>
                        <dd class="mt-1 flex items-center gap-2 text-fg">
                            @if ($teacher)
                                <x-ui.avatar :user="$teacher" size="xs" decorative /> {{ $teacher->name }}
                            @else
                                <span class="text-warning">Belum ditentukan</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-fg-muted">Waktu</dt>
                        <dd class="mt-1 text-fg">Setiap {{ $slot->dayName() }}, <span class="num font-mono">{{ $slot->timeRange() }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-fg-muted">Ruang/lokasi</dt>
                        <dd class="mt-1 text-fg">{{ $slot->room() ?? 'Belum ditentukan' }}</dd>
                    </div>
                    @if ($class)
                        <div>
                            <dt class="text-fg-muted">Berlaku</dt>
                            <dd class="mt-1 text-fg">{{ App\Services\Admin\AdminClock::format($class->start_date, 'j M Y') }} – {{ App\Services\Admin\AdminClock::format($class->end_date, 'j M Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>

            @can('delete', $schedule)
                <x-ui.card variant="callout" title="Hapus jadwal" style="--callout-c: var(--cf-danger)"
                    subtitle="Menghapus sesi mingguan ini untuk semua siswanya. Laporan pertemuan dan absensi yang sudah tercatat tetap tersimpan.">
                    <x-ui.confirm-delete :action="route('admin.schedules.destroy', $schedule)" variant="danger" label="Hapus jadwal"
                        title="Hapus jadwal ini?" :message="'Sesi '.$slot->dayName().' '.$slot->timeRange().' untuk '.$students->count().' siswa dihapus. Pilih Nonaktifkan bila hanya libur sementara.'" />
                </x-ui.card>
            @endcan
        </div>
    </div>
</x-app-layout>
