<x-app-layout title="Kurikulum">
    <x-ui.page-header title="Kurikulum"
        subtitle="Susun kurikulum tiap kursus, pertemuan demi pertemuan, lengkap dengan materinya."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kurikulum' => null]">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="document-text" :href="route('admin.syllabuses.index')">Semua pertemuan</x-ui.button>
            <x-ui.button icon="plus" :href="route('admin.curriculums.create')">Tambah kurikulum</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Daftar kurikulum" mobile="stack">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari judul kurikulum…">
                <x-ui.select name="course_id" :options="$courses" placeholder="Semua kursus" :value="request('course_id')" class="sm:w-48" aria-label="Filter kursus" />
                <x-ui.select name="type" :options="$types" placeholder="Semua jenis" :value="request('type')" class="sm:w-40" aria-label="Filter jenis" />
                <x-ui.select name="status" :options="['active' => 'Aktif', 'inactive' => 'Nonaktif']" placeholder="Semua status" :value="request('status')" class="sm:w-40" aria-label="Filter status" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th>Kurikulum</x-ui.th>
            <x-ui.th>Jenis</x-ui.th>
            <x-ui.th numeric>Pertemuan</x-ui.th>
            <x-ui.th numeric>Materi</x-ui.th>
            <x-ui.th numeric>Kelas</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($curriculums as $curriculum)
            <tr>
                <td class="cell-primary" data-label="Kurikulum">
                    <a href="{{ route('admin.curriculums.show', $curriculum) }}" class="font-medium text-fg hover:text-brand-text">{{ $curriculum->title }}</a>
                    <p class="cell-secondary">
                        {{ $curriculum->course?->name ?? 'Kursus diarsipkan' }}@if ($curriculum->duration_weeks) · {{ $curriculum->duration_weeks }} minggu @endif
                    </p>
                </td>
                <td data-label="Jenis"><x-ui.badge outline>{{ $types[$curriculum->type] ?? $curriculum->type }}</x-ui.badge></td>
                <td class="num" data-label="Pertemuan">{{ $curriculum->syllabuses_count }}</td>
                <td class="num" data-label="Materi">{{ $curriculum->materials_count }}</td>
                <td class="num" data-label="Kelas">{{ $curriculum->classes_count }}</td>
                <td data-label="Status">
                    @include('admin.curriculums.partials.status-toggle', [
                        'action' => route('admin.curriculums.toggle-status', $curriculum),
                        'active' => $curriculum->status === 'active',
                        'label' => 'Status kurikulum '.$curriculum->title,
                    ])
                </td>
                <td class="cell-actions">
                    <div class="flex justify-end gap-1">
                        <x-ui.button variant="ghost" size="sm" icon="eye" label="Lihat {{ $curriculum->title }}" :href="route('admin.curriculums.show', $curriculum)" />
                        <x-ui.button variant="ghost" size="sm" icon="pencil-square" label="Ubah {{ $curriculum->title }}" :href="route('admin.curriculums.edit', $curriculum)" />
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="queue-list" title="Belum ada kurikulum yang cocok."
                description="Ubah filter pencarian, atau buat kurikulum pertama untuk sebuah kursus.">
                <x-slot:actions>
                    <x-ui.button icon="plus" :href="route('admin.curriculums.create')">Tambah kurikulum</x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        </x-slot:empty>

        @if ($curriculums->hasPages())
            <x-slot:footer>{{ $curriculums->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
