<x-app-layout title="Silabus">
    <x-ui.page-header title="Silabus"
        subtitle="Semua pertemuan dari setiap kurikulum: tujuan, kegiatan, durasi dan jumlah materinya."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Silabus' => null]">
        <x-slot:actions>
            <x-ui.button icon="plus" :href="route('admin.syllabuses.create', array_filter(['curriculum_id' => request('curriculum_id')]))">Tambah pertemuan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Daftar pertemuan" mobile="stack">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari judul pertemuan…">
                <x-ui.select name="curriculum_id" :options="$curriculumOptions" placeholder="Semua kurikulum" :value="request('curriculum_id')" class="sm:w-64" aria-label="Filter kurikulum" />
                <x-ui.select name="status" :options="['active' => 'Aktif', 'inactive' => 'Nonaktif']" placeholder="Semua status" :value="request('status')" class="sm:w-40" aria-label="Filter status" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th numeric>Ke</x-ui.th>
            <x-ui.th>Pertemuan</x-ui.th>
            <x-ui.th>Kurikulum</x-ui.th>
            <x-ui.th numeric>Durasi</x-ui.th>
            <x-ui.th numeric>Materi</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($syllabuses as $syllabus)
            <tr>
                <td class="num" data-label="Pertemuan ke">{{ $syllabus->meeting_number }}</td>
                <td class="cell-primary" data-label="Pertemuan">
                    <a href="{{ route('admin.syllabuses.show', $syllabus) }}" class="font-medium text-fg hover:text-brand-text">{{ $syllabus->title }}</a>
                </td>
                <td data-label="Kurikulum">
                    @if ($syllabus->curriculum)
                        <a href="{{ route('admin.curriculums.show', $syllabus->curriculum) }}" class="link">{{ $syllabus->curriculum->title }}</a>
                        <p class="cell-secondary">{{ $syllabus->curriculum->course?->name }}</p>
                    @else
                        <span class="text-fg-subtle">Diarsipkan</span>
                    @endif
                </td>
                <td class="num" data-label="Durasi">{{ $syllabus->duration_minutes }} mnt</td>
                <td class="num" data-label="Materi">{{ $syllabus->materials_count }}</td>
                <td data-label="Status">
                    @include('admin.curriculums.partials.status-toggle', [
                        'action' => route('admin.syllabuses.toggle-status', $syllabus),
                        'active' => $syllabus->status === 'active',
                        'label' => 'Status pertemuan '.$syllabus->title,
                    ])
                </td>
                <td class="cell-actions">
                    <div class="flex justify-end gap-1">
                        <x-ui.button variant="ghost" size="sm" icon="eye" label="Lihat {{ $syllabus->title }}" :href="route('admin.syllabuses.show', $syllabus)" />
                        <x-ui.button variant="ghost" size="sm" icon="pencil-square" label="Ubah {{ $syllabus->title }}" :href="route('admin.syllabuses.edit', $syllabus)" />
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="document-text" title="Belum ada pertemuan yang cocok."
                description="Pilih kurikulum lain atau tambahkan pertemuan baru." />
        </x-slot:empty>

        @if ($syllabuses->hasPages())
            <x-slot:footer>{{ $syllabuses->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
