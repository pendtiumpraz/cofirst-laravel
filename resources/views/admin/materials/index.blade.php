<x-app-layout title="Materi">
    <x-ui.page-header title="Materi"
        subtitle="Dokumen, video, latihan, kuis dan proyek untuk setiap pertemuan."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Materi' => null]">
        <x-slot:actions>
            <x-ui.button icon="plus" :href="route('admin.materials.create', array_filter(['syllabus_id' => request('syllabus_id')]))">Tambah materi</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Daftar materi" mobile="stack">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari judul materi…">
                <x-ui.select name="curriculum_id" :options="$curriculumOptions" placeholder="Semua kurikulum" :value="request('curriculum_id')" class="sm:w-56" aria-label="Filter kurikulum" />
                <x-ui.select name="type" :options="$types" placeholder="Semua jenis" :value="request('type')" class="sm:w-36" aria-label="Filter jenis" />
                <x-ui.select name="status" :options="['active' => 'Aktif', 'inactive' => 'Nonaktif']" placeholder="Semua status" :value="request('status')" class="sm:w-36" aria-label="Filter status" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th>Materi</x-ui.th>
            <x-ui.th>Pertemuan</x-ui.th>
            <x-ui.th>Jenis</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($materials as $material)
            <tr>
                <td class="cell-primary" data-label="Materi">
                    <a href="{{ route('admin.materials.show', $material) }}" class="font-medium text-fg hover:text-brand-text">{{ $material->title }}</a>
                    @if (filled($material->description))
                        <p class="cell-secondary line-clamp-1">{{ $material->description }}</p>
                    @endif
                </td>
                <td data-label="Pertemuan">
                    @if ($material->syllabus)
                        <a href="{{ route('admin.syllabuses.show', $material->syllabus) }}" class="link">{{ $material->meeting_range }}</a>
                        <p class="cell-secondary">{{ $material->syllabus->curriculum?->title }}</p>
                    @else
                        <span class="text-fg-subtle">Pertemuan diarsipkan</span>
                    @endif
                </td>
                <td data-label="Jenis">@include('admin.materials.partials.type-badge', ['type' => $material->type])</td>
                <td data-label="Status">
                    @include('admin.curriculums.partials.status-toggle', [
                        'action' => route('admin.materials.toggle-status', $material),
                        'active' => $material->status === 'active',
                        'label' => 'Status materi '.$material->title,
                    ])
                </td>
                <td class="cell-actions">
                    <div class="flex justify-end gap-1">
                        @if ($material->file_path)
                            <x-ui.button variant="ghost" size="sm" icon="arrow-down-tray" label="Unduh berkas {{ $material->title }}" :href="route('admin.materials.download', $material)" />
                        @endif
                        <x-ui.button variant="ghost" size="sm" icon="eye" label="Lihat {{ $material->title }}" :href="route('admin.materials.show', $material)" />
                        <x-ui.button variant="ghost" size="sm" icon="pencil-square" label="Ubah {{ $material->title }}" :href="route('admin.materials.edit', $material)" />
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="folder-open" title="Belum ada materi yang cocok."
                description="Ubah filter, atau buka sebuah pertemuan untuk menambahkan materinya." />
        </x-slot:empty>

        @if ($materials->hasPages())
            <x-slot:footer>{{ $materials->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
