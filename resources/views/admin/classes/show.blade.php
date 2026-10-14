@php
    use App\Services\Admin\AdminClock;
    use Illuminate\Support\Facades\Route;

    $statusTone = ['active' => 'success', 'planned' => 'info', 'completed' => 'neutral', 'cancelled' => 'danger'];
    $taken = (int) $class->seats_taken;
    $delivery = App\Http\Requests\Admin\ClassRequest::DELIVERY;
@endphp

<x-app-layout :title="$class->name">
    <x-ui.page-header :title="$class->name" :subtitle="$class->description"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kelas' => route('admin.classes.index'), $class->name => null]">
        <x-slot:badge>
            @if ($class->trashed())
                <x-ui.badge tone="neutral" dot>Diarsipkan</x-ui.badge>
            @else
                <x-ui.badge :tone="$statusTone[$class->status] ?? 'neutral'" dot>{{ $class->status_label }}</x-ui.badge>
            @endif
        </x-slot:badge>
        <x-slot:meta>
            <span>{{ $class->course?->name ?? 'Tanpa kursus' }}</span>
            <span>{{ $class->type_label }}</span>
            <span>{{ AdminClock::format($class->start_date, 'j M Y') }} – {{ AdminClock::format($class->end_date, 'j M Y') }}</span>
        </x-slot:meta>
        <x-slot:actions>
            @if ($class->trashed())
                @can('delete', $class)
                    <form method="POST" action="{{ route('admin.classes.restore', $class) }}">
                        @csrf
                        <x-ui.button type="submit" icon="arrow-uturn-left">Pulihkan</x-ui.button>
                    </form>
                @endcan
            @else
                <x-ui.button variant="secondary" icon="users" :href="route('admin.classes.students', $class)">Kelola siswa</x-ui.button>
                @can('update', $class)
                    <x-ui.button icon="pencil-square" :href="route('admin.classes.edit', $class)">Ubah</x-ui.button>
                @endcan
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.metric-strip class="mt-6" label="Ringkasan kelas" :stats="[
        ['label' => 'Kursi terisi', 'value' => $taken.'/'.$class->max_students, 'icon' => 'user-group', 'tone' => $class->isFull() ? 'warning' : 'neutral',
            'hint' => $class->isFull() ? 'kelas penuh' : $class->availableSeats().' kursi tersisa', 'href' => route('admin.classes.students', $class)],
        ['label' => 'Jadwal per minggu', 'value' => (string) $slots->filter->isActive()->count(), 'icon' => 'calendar-days', 'hint' => 'sesi aktif'],
        ['label' => 'Kehadiran rata-rata', 'value' => $attendanceRate === null ? null : number_format($attendanceRate, 1, ',', '.'), 'unit' => $attendanceRate === null ? null : '%', 'icon' => 'check-badge'],
        ['label' => 'Pertemuan terlaksana', 'value' => (string) $meetings, 'icon' => 'document-text', 'hint' => 'berdasarkan laporan pertemuan'],
    ]" />

    <x-ui.tabs class="mt-8" group="kelas" active="ringkasan" label="Bagian detail kelas" :tabs="[
        'ringkasan' => ['label' => 'Ringkasan', 'icon' => 'document-text'],
        'siswa' => ['label' => 'Siswa', 'icon' => 'users', 'count' => $taken],
        'jadwal' => ['label' => 'Jadwal', 'icon' => 'calendar-days', 'count' => $slots->count()],
    ]">
        <x-ui.tab-panel name="ringkasan">
            <div class="grid grid-cols-1 gap-gutter lg:grid-cols-3">
                <x-ui.card title="Informasi" class="lg:col-span-2">
                    <dl class="divide-y divide-line">
                        @foreach ([
                            'Kursus' => $class->course?->name,
                            'Kurikulum' => $class->curriculum?->title,
                            'Tipe' => $class->type_label,
                            'Metode belajar' => $delivery[$class->delivery_method] ?? null,
                            'Periode' => AdminClock::format($class->start_date, 'j F Y').' – '.AdminClock::format($class->end_date, 'j F Y'),
                            'Kapasitas' => $class->max_students.' siswa',
                        ] as $label => $value)
                            <div class="grid gap-1 py-3 first:pt-0 last:pb-0 sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm text-fg-muted">{{ $label }}</dt>
                                <dd class="text-sm text-fg sm:col-span-2">
                                    @if (filled($value))
                                        @if ($label === 'Kurikulum' && Route::has('admin.curriculums.show'))
                                            <a href="{{ route('admin.curriculums.show', $class->curriculum_id) }}" class="link">{{ $value }}</a>
                                        @elseif ($label === 'Kursus' && $class->course)
                                            <a href="{{ route('admin.courses.show', $class->course) }}" class="link">{{ $value }}</a>
                                        @else
                                            {{ $value }}
                                        @endif
                                    @else
                                        <span class="text-fg-subtle">Belum ditentukan</span>
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </x-ui.card>

                <x-ui.card title="Pengajar" padding="none">
                    @if ($class->teachers->isEmpty())
                        <x-ui.empty-state icon="user" title="Belum ada pengajar." description="Tambahkan pengajar lewat tombol Ubah." />
                    @else
                        <ul class="divide-y divide-line" role="list">
                            @foreach ($class->teachers as $teacher)
                                <li class="flex items-center gap-3 px-card py-3">
                                    <x-ui.avatar :user="$teacher" size="sm" decorative />
                                    <a href="{{ route('admin.users.show', $teacher) }}" class="min-w-0 truncate font-medium text-fg hover:text-brand-text">{{ $teacher->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>
            </div>
        </x-ui.tab-panel>

        <x-ui.tab-panel name="siswa">
            <x-ui.card padding="none" title="Siswa terdaftar" :subtitle="$taken.' dari '.$class->max_students.' kursi terisi'">
                <x-slot:actions>
                    <x-ui.button variant="link" size="sm" arrow :href="route('admin.classes.students', $class)">Kelola siswa</x-ui.button>
                </x-slot:actions>
                <div class="px-card pb-4">
                    <x-ui.progress size="sm" :value="$taken" :max="max(1, $class->max_students)" :tone="$class->isFull() ? 'danger' : 'brand'" label="Kursi terisi" />
                </div>
                @if ($students->isEmpty())
                    <x-ui.empty-state icon="users" title="Belum ada siswa di kelas ini." />
                @else
                    <ul class="grid border-t border-line sm:grid-cols-2" role="list">
                        @foreach ($students as $enrollment)
                            <li class="flex items-center gap-3 border-b border-line px-card py-3">
                                <x-ui.avatar :user="$enrollment->student" size="sm" decorative />
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('admin.users.show', $enrollment->student_id) }}" class="block truncate font-medium text-fg hover:text-brand-text">{{ $enrollment->student?->name ?? 'Siswa' }}</a>
                                    <p class="text-xs text-fg-subtle">Sejak {{ AdminClock::format($enrollment->enrollment_date, 'j M Y') }}</p>
                                </div>
                                @if ($enrollment->status === 'pending')
                                    <x-ui.badge tone="warning">Menunggu</x-ui.badge>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </x-ui.tab-panel>

        <x-ui.tab-panel name="jadwal">
            <x-ui.card padding="none" title="Jadwal mingguan">
                @can('create', App\Models\Schedule::class)
                    <x-slot:actions>
                        <x-ui.button size="sm" variant="secondary" icon="plus" :href="route('admin.schedules.create', ['class' => $class->id])">Tambah jadwal</x-ui.button>
                    </x-slot:actions>
                @endcan
                @if ($slots->isEmpty())
                    <x-ui.empty-state icon="calendar-days" title="Belum ada jadwal mingguan." description="Tambahkan hari dan jam belajar setelah siswa terdaftar." />
                @else
                    <ul class="divide-y divide-line" role="list">
                        @foreach ($slots as $slot)
                            <li class="flex flex-wrap items-center gap-x-4 gap-y-1 px-card py-3">
                                <span class="w-20 font-medium text-fg">{{ $slot->dayName() }}</span>
                                <span class="num font-mono text-sm text-fg">{{ $slot->timeRange() }}</span>
                                <span class="min-w-0 flex-1 text-sm text-fg-muted">{{ $slot->teacher()?->name ?? 'Tanpa pengajar' }}{{ $slot->room() ? ' · '.$slot->room() : '' }}</span>
                                <x-ui.badge tone="neutral">{{ $slot->studentCount() }} siswa</x-ui.badge>
                                @unless ($slot->isActive())
                                    <x-ui.badge tone="warning">Nonaktif</x-ui.badge>
                                @endunless
                                <x-ui.button variant="ghost" size="sm" icon="chevron-right" :href="route('admin.schedules.show', $slot->id())" :label="'Detail jadwal '.$slot->dayName()" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </x-ui.tab-panel>
    </x-ui.tabs>

    @can('delete', $class)
        @unless ($class->trashed())
            <x-ui.card variant="callout" class="mt-10" title="Arsipkan kelas" style="--callout-c: var(--cf-danger)"
                subtitle="Hanya kelas tanpa siswa aktif/menunggu yang dapat diarsipkan. Absensi, laporan dan sertifikat tetap tersimpan.">
                <x-ui.confirm-delete :action="route('admin.classes.destroy', $class)" variant="danger" icon="archive-box" label="Arsipkan kelas"
                    title="Arsipkan kelas ini?" confirm-text="Ya, arsipkan" confirm-phrase="ARSIPKAN"
                    :message="'Kelas '.$class->name.' dan jadwal mingguannya dinonaktifkan.'" />
            </x-ui.card>
        @endunless
    @endcan
</x-app-layout>
