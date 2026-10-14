@php
    use App\Models\Schedule;

    $clashCount = count($clashes);
    $viewUrl = fn ($mode) => route('admin.schedules.index', ['view' => $mode] + request()->except(['view', 'page']));
@endphp

<x-app-layout title="Jadwal">
    <x-ui.page-header title="Jadwal" subtitle="Jadwal mingguan setiap kelas. Bentrok guru atau ruang ditandai otomatis."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Jadwal' => null]">
        <x-slot:actions>
            @can('create', App\Models\Schedule::class)
                <x-ui.button icon="plus" :href="route('admin.schedules.create')">Tambah jadwal</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <x-ui.tabs label="Tampilan jadwal" :active="$view" :tabs="[
            'calendar' => ['label' => 'Kalender mingguan', 'icon' => 'calendar-days', 'href' => $viewUrl('calendar')],
            'list' => ['label' => 'Daftar', 'icon' => 'list-bullet', 'count' => $slotCount, 'href' => $viewUrl('list')],
        ]" />
    </div>

    <x-ui.card class="mt-4" padding="sm">
        <x-ui.filter-bar placeholder="Cari kelas atau ruang…" :keep="['view', 'sort', 'direction']">
            <x-ui.select name="class" :options="$classes" placeholder="Semua kelas" :value="request('class')" class="sm:w-48" aria-label="Kelas" />
            <x-ui.select name="teacher" :options="$teachers" placeholder="Semua pengajar" :value="request('teacher')" class="sm:w-44" aria-label="Pengajar" />
            <x-ui.select name="day" :options="Schedule::DAYS" placeholder="Semua hari" :value="request('day')" class="sm:w-36" aria-label="Hari" />
            <x-ui.select name="class_status" :value="$classStatus" class="sm:w-52" aria-label="Status kelas" :options="[
                'ongoing' => 'Kelas berjalan & direncanakan',
                'all' => 'Semua kelas',
                'completed' => 'Kelas selesai',
                'cancelled' => 'Kelas dibatalkan',
            ]" />
            <x-ui.select name="state" :value="request('state')" placeholder="Aktif & nonaktif" class="sm:w-40" aria-label="Status jadwal"
                :options="['active' => 'Jadwal aktif', 'inactive' => 'Jadwal nonaktif']" />
        </x-ui.filter-bar>
    </x-ui.card>

    @if ($clashCount > 0)
        <x-ui.alert tone="danger" icon="exclamation-triangle" class="mt-6" :title="$clashCount.' jadwal bentrok'">
            Ada pengajar atau ruang yang dipakai pada jam yang sama. Buka jadwal bertanda merah lalu ubah jam, ruang atau pengajarnya.
        </x-ui.alert>
    @endif

    @if ($view === 'calendar')
        @if ($slotCount === 0)
            <x-ui.empty-state class="mt-6" size="page" icon="calendar-days" title="Belum ada jadwal yang cocok."
                description="Tambahkan jadwal mingguan untuk kelas yang sudah memiliki siswa dan pengajar.">
                @can('create', App\Models\Schedule::class)
                    <x-slot:actions><x-ui.button icon="plus" :href="route('admin.schedules.create')">Tambah jadwal</x-ui.button></x-slot:actions>
                @endcan
            </x-ui.empty-state>
        @else
            {{-- Phones: one day per section; desktop: 7 columns. --}}
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-7 xl:gap-3">
                @foreach ($week as $day => $column)
                    <section aria-labelledby="hari-{{ $day }}"
                        @class(['rounded-lg border p-3', 'border-brand bg-brand-soft/40' => $day === $todayIso, 'border-line bg-surface-2' => $day !== $todayIso])>
                        <h2 id="hari-{{ $day }}" class="flex items-center justify-between gap-2 text-sm font-semibold text-fg">
                            <span>{{ $column['name'] }}</span>
                            @if ($day === $todayIso)
                                <x-ui.badge tone="primary">Hari ini</x-ui.badge>
                            @else
                                <span class="num text-xs font-normal text-fg-subtle">{{ $column['slots']->count() ?: '' }}</span>
                            @endif
                        </h2>
                        @if ($column['slots']->isEmpty())
                            <p class="mt-3 text-xs text-fg-subtle">Tidak ada kelas.</p>
                        @else
                            <ul class="mt-3 space-y-2" role="list">
                                @foreach ($column['slots'] as $slot)
                                    <li>@include('admin.schedules.partials.slot-card', ['slot' => $slot])</li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endforeach
            </div>
        @endif
    @else
        <x-ui.table caption="Daftar jadwal mingguan" mobile="stack" class="mt-6">
            <x-slot:head>
                <x-ui.th>Hari & jam</x-ui.th>
                <x-ui.th>Kelas</x-ui.th>
                <x-ui.th>Pengajar</x-ui.th>
                <x-ui.th>Ruang</x-ui.th>
                <x-ui.th numeric>Siswa</x-ui.th>
                <x-ui.th>Status</x-ui.th>
                <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
            </x-slot:head>

            @foreach ($list as $slot)
                @php $reasons = $clashes[$slot->key] ?? []; @endphp
                <tr>
                    <td class="cell-primary" data-label="Hari & jam">
                        <span class="font-medium text-fg">{{ $slot->dayName() }}</span>
                        <p class="cell-secondary num font-mono">{{ $slot->timeRange() }}</p>
                    </td>
                    <td data-label="Kelas">
                        <a href="{{ route('admin.schedules.show', $slot->id()) }}" class="font-medium text-fg hover:text-brand-text">{{ $slot->class()?->name ?? 'Kelas' }}</a>
                        <p class="cell-secondary">{{ $slot->class()?->course?->name ?? '—' }}</p>
                    </td>
                    <td data-label="Pengajar">{{ $slot->teacher()?->name ?? '—' }}</td>
                    <td data-label="Ruang">{{ $slot->room() ?? '—' }}</td>
                    <td class="num" data-label="Siswa">{{ $slot->studentCount() }}</td>
                    <td data-label="Status">
                        <div class="flex flex-wrap gap-1">
                            <x-ui.badge :tone="$slot->isActive() ? 'success' : 'warning'" dot>{{ $slot->isActive() ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                            @if ($reasons)
                                <x-ui.badge tone="danger" icon="exclamation-triangle">Bentrok {{ implode(' & ', $reasons) }}</x-ui.badge>
                            @endif
                        </div>
                    </td>
                    <td class="cell-actions">
                        <div class="inline-flex items-center gap-1">
                            <x-ui.button variant="ghost" size="sm" icon="eye" :href="route('admin.schedules.show', $slot->id())" :label="'Detail jadwal '.($slot->class()?->name ?? '')" />
                            @can('update', $slot->schedule())
                                <x-ui.button variant="ghost" size="sm" icon="pencil-square" :href="route('admin.schedules.edit', $slot->id())" :label="'Ubah jadwal '.($slot->class()?->name ?? '')" />
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot:empty>
                <x-ui.empty-state icon="calendar-days" title="Belum ada jadwal yang cocok." description="Ubah filter atau tambahkan jadwal baru." />
            </x-slot:empty>

            <x-slot:footer>{{ $list->links() }}</x-slot:footer>
        </x-ui.table>
    @endif
</x-app-layout>
