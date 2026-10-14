@php
    use App\Services\Admin\AdminClock;

    $levelTone = ['beginner' => 'success', 'intermediate' => 'info', 'advanced' => 'accent'];
@endphp

<x-app-layout title="Kursus">
    <x-ui.page-header title="Kursus" subtitle="Katalog program belajar: harga, durasi dan kelas yang berjalan."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kursus' => null]">
        <x-slot:actions>
            @can('create', App\Models\Course::class)
                <x-ui.button icon="plus" :href="route('admin.courses.create')">Tambah kursus</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.metric-strip class="mt-6" label="Ringkasan kursus" :stats="[
        ['label' => 'Total kursus', 'value' => (string) $stats['total'], 'icon' => 'book-open'],
        ['label' => 'Aktif di katalog', 'value' => (string) $stats['active'], 'icon' => 'check-circle'],
        ['label' => 'Kelas berjalan/direncanakan', 'value' => (string) $stats['running_classes'], 'icon' => 'rectangle-group', 'href' => route('admin.classes.index')],
        ['label' => 'Diarsipkan', 'value' => (string) $stats['archived'], 'icon' => 'archive-box', 'href' => route('admin.courses.index', ['status' => 'archived'])],
    ]" />

    <x-ui.table caption="Daftar kursus" mobile="stack" class="mt-6">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari nama atau deskripsi…" class="w-full">
                <x-ui.select name="level" :options="$levels" placeholder="Semua tingkat" :value="request('level')" class="sm:w-40" aria-label="Tingkat" />
                <x-ui.select name="status" :options="['active' => 'Aktif', 'inactive' => 'Nonaktif', 'archived' => 'Diarsipkan']" placeholder="Semua status" :value="request('status')" class="sm:w-40" aria-label="Status" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th sort="name">Kursus</x-ui.th>
            <x-ui.th>Tingkat</x-ui.th>
            <x-ui.th sort="price" numeric>Harga</x-ui.th>
            <x-ui.th numeric>Durasi</x-ui.th>
            <x-ui.th sort="classes_count" numeric default-direction="desc">Kelas</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($courses as $course)
            <tr>
                <td class="cell-primary" data-label="Kursus">
                    <div class="flex items-center gap-3">
                        @if ($course->thumbnail)
                            <img src="{{ asset('storage/'.$course->thumbnail) }}" alt="" class="size-10 shrink-0 rounded-md border border-line object-cover" loading="lazy">
                        @else
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-md bg-brand-soft text-brand-soft-fg" aria-hidden="true">
                                <x-ui.icon name="book-open" size="20" />
                            </span>
                        @endif
                        <div class="min-w-0">
                            <a href="{{ route('admin.courses.show', $course) }}" class="block truncate font-medium text-fg hover:text-brand-text">{{ $course->name }}</a>
                            <p class="cell-secondary truncate font-mono">/{{ $course->slug }}</p>
                        </div>
                    </div>
                </td>
                <td data-label="Tingkat"><x-ui.badge :tone="$levelTone[$course->level] ?? 'neutral'">{{ $levels[$course->level] ?? ucfirst((string) $course->level) }}</x-ui.badge></td>
                <td class="num" data-label="Harga">{{ AdminClock::rupiah($course->price) }}</td>
                <td class="num" data-label="Durasi">{{ $course->duration_weeks }} minggu</td>
                <td class="num" data-label="Kelas">
                    {{ $course->classes_count }}
                    @if ($course->running_classes_count)
                        <span class="text-xs text-fg-subtle">({{ $course->running_classes_count }} berjalan)</span>
                    @endif
                </td>
                <td data-label="Status">
                    @if ($course->trashed())
                        <x-ui.badge tone="neutral" dot>Diarsipkan</x-ui.badge>
                    @elseif ($course->is_active)
                        <x-ui.badge tone="success" dot>Aktif</x-ui.badge>
                    @else
                        <x-ui.badge tone="warning" dot>Nonaktif</x-ui.badge>
                    @endif
                </td>
                <td class="cell-actions">
                    <div class="inline-flex items-center gap-1">
                        @if ($course->trashed())
                            @can('delete', $course)
                                <form method="POST" action="{{ route('admin.courses.restore', $course) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" size="sm" icon="arrow-uturn-left">Pulihkan</x-ui.button>
                                </form>
                            @endcan
                        @else
                            @can('update', $course)
                                <x-ui.button variant="ghost" size="sm" icon="pencil-square" :href="route('admin.courses.edit', $course)" :label="'Ubah '.$course->name" />
                                <form method="POST" action="{{ route('admin.courses.toggle-status', $course) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="ghost" size="sm" :icon="$course->is_active ? 'eye-slash' : 'eye'"
                                        :label="($course->is_active ? 'Nonaktifkan ' : 'Aktifkan ').$course->name" />
                                </form>
                            @endcan
                            @can('delete', $course)
                                <x-ui.confirm-delete :action="route('admin.courses.destroy', $course)" icon-only size="sm" icon="archive-box"
                                    label="Arsipkan" title="Arsipkan kursus ini?" confirm-text="Ya, arsipkan"
                                    :message="'Kursus '.$course->name.' disembunyikan dari katalog. Kelas lama, kurikulum dan riwayat pembayaran tetap tersimpan. Kursus dengan kelas yang masih berjalan tidak dapat diarsipkan.'" />
                            @endcan
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            @if (request()->hasAny(['search', 'level', 'status']))
                <x-ui.empty-state icon="magnifying-glass" title="Tidak ada kursus yang cocok." description="Coba kata kunci lain atau hapus filter.">
                    <x-slot:actions><x-ui.button variant="secondary" size="sm" :href="route('admin.courses.index')">Hapus filter</x-ui.button></x-slot:actions>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state icon="book-open" title="Belum ada kursus." description="Tambahkan program pertama, mis. Scratch untuk anak atau Python dasar." />
            @endif
        </x-slot:empty>

        <x-slot:footer>{{ $courses->links() }}</x-slot:footer>
    </x-ui.table>
</x-app-layout>
