@php
    use App\Services\Admin\AdminClock;

    $statusTone = ['active' => 'success', 'planned' => 'info', 'completed' => 'neutral', 'cancelled' => 'danger'];
@endphp

<x-app-layout title="Kelas">
    <x-ui.page-header title="Kelas" subtitle="Rombongan belajar per kursus: pengajar, kapasitas, periode dan status."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kelas' => null]">
        <x-slot:actions>
            @can('create', App\Models\ClassName::class)
                <x-ui.button icon="plus" :href="route('admin.classes.create')">Buat kelas</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.tabs class="mt-6" label="Status kelas" :active="request('status', 'all')" :tabs="[
        'all' => ['label' => 'Semua', 'href' => route('admin.classes.index', request()->except(['status', 'page']))],
        'active' => ['label' => 'Berlangsung', 'count' => (int) ($counts['active'] ?? 0), 'href' => route('admin.classes.index', ['status' => 'active'] + request()->except(['status', 'page']))],
        'planned' => ['label' => 'Direncanakan', 'count' => (int) ($counts['planned'] ?? 0), 'href' => route('admin.classes.index', ['status' => 'planned'] + request()->except(['status', 'page']))],
        'completed' => ['label' => 'Selesai', 'count' => (int) ($counts['completed'] ?? 0), 'href' => route('admin.classes.index', ['status' => 'completed'] + request()->except(['status', 'page']))],
        'cancelled' => ['label' => 'Dibatalkan', 'count' => (int) ($counts['cancelled'] ?? 0), 'href' => route('admin.classes.index', ['status' => 'cancelled'] + request()->except(['status', 'page']))],
        'archived' => ['label' => 'Arsip', 'href' => route('admin.classes.index', ['status' => 'archived'])],
    ]" />

    <x-ui.table caption="Daftar kelas" mobile="stack" class="mt-6">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari nama kelas…" class="w-full" :keep="['sort', 'direction', 'status']">
                <x-ui.select name="course" :options="$courses" placeholder="Semua kursus" :value="request('course')" class="sm:w-48" aria-label="Kursus" />
                <x-ui.select name="type" :options="$types" placeholder="Semua tipe" :value="request('type')" class="sm:w-48" aria-label="Tipe kelas" />
                <x-ui.select name="teacher" :options="$teachers" placeholder="Semua pengajar" :value="request('teacher')" class="sm:w-44" aria-label="Pengajar" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th sort="name">Kelas</x-ui.th>
            <x-ui.th>Pengajar</x-ui.th>
            <x-ui.th sort="seats_taken" default-direction="desc">Kursi</x-ui.th>
            <x-ui.th sort="start_date" numeric default-direction="desc">Periode</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($classes as $class)
            @php $taken = $class->seatsTaken(); @endphp
            <tr>
                <td class="cell-primary" data-label="Kelas">
                    <a href="{{ route('admin.classes.show', $class) }}" class="font-medium text-fg hover:text-brand-text">{{ $class->name }}</a>
                    <p class="cell-secondary">{{ $class->course?->name ?? 'Tanpa kursus' }} · {{ $class->type_label }}</p>
                </td>
                <td data-label="Pengajar">
                    @if ($class->teachers->isEmpty())
                        <span class="text-sm text-warning">Belum ada pengajar</span>
                    @else
                        <div class="flex items-center gap-2">
                            <div class="flex -space-x-2">
                                @foreach ($class->teachers->take(3) as $teacher)
                                    <x-ui.avatar :user="$teacher" size="xs" class="ring-2 ring-surface" />
                                @endforeach
                            </div>
                            <span class="truncate text-sm text-fg-muted">{{ $class->teachers->pluck('name')->implode(', ') }}</span>
                        </div>
                    @endif
                </td>
                <td data-label="Kursi" class="min-w-[9rem]">
                    <div class="flex items-center gap-2">
                        <x-ui.progress class="flex-1" size="sm" :value="$taken" :max="max(1, $class->max_students)"
                            :tone="$class->isFull() ? 'danger' : ($taken / max(1, $class->max_students) >= 0.8 ? 'warning' : 'brand')" :label="'Kursi terisi '.$class->name" />
                        <span class="num text-sm text-fg-muted">{{ $taken }}/{{ $class->max_students }}</span>
                    </div>
                </td>
                <td class="num" data-label="Periode">{{ AdminClock::format($class->start_date, 'j M') }} – {{ AdminClock::format($class->end_date, 'j M Y') }}</td>
                <td data-label="Status">
                    @if ($class->trashed())
                        <x-ui.badge tone="neutral" dot>Diarsipkan</x-ui.badge>
                    @else
                        <x-ui.badge :tone="$statusTone[$class->status] ?? 'neutral'" dot>{{ $class->status_label }}</x-ui.badge>
                    @endif
                </td>
                <td class="cell-actions">
                    <div class="inline-flex items-center gap-1">
                        @if ($class->trashed())
                            @can('delete', $class)
                                <form method="POST" action="{{ route('admin.classes.restore', $class) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" size="sm" icon="arrow-uturn-left">Pulihkan</x-ui.button>
                                </form>
                            @endcan
                        @else
                            <x-ui.button variant="ghost" size="sm" icon="users" :href="route('admin.classes.students', $class)" :label="'Siswa '.$class->name" />
                            @can('update', $class)
                                <x-ui.button variant="ghost" size="sm" icon="pencil-square" :href="route('admin.classes.edit', $class)" :label="'Ubah '.$class->name" />
                            @endcan
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            @if (request()->hasAny(['search', 'course', 'type', 'teacher', 'status']))
                <x-ui.empty-state icon="magnifying-glass" title="Tidak ada kelas yang cocok." description="Coba kata kunci lain atau hapus filter.">
                    <x-slot:actions><x-ui.button variant="secondary" size="sm" :href="route('admin.classes.index')">Hapus filter</x-ui.button></x-slot:actions>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state icon="rectangle-group" title="Belum ada kelas." description="Buat kelas dari kursus yang sudah ada, lalu tambahkan pengajar dan siswa." />
            @endif
        </x-slot:empty>

        <x-slot:footer>{{ $classes->links() }}</x-slot:footer>
    </x-ui.table>
</x-app-layout>
