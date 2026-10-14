<x-app-layout title="Materi">
    <x-ui.page-header title="Materi"
        subtitle="Materi dari kurikulum kelas Anda, berurutan per pertemuan." />

    <x-ui.table caption="Daftar materi" mobile="stack">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari judul materi…">
                <x-ui.select name="curriculum_id" :options="$curriculumOptions" placeholder="Semua kurikulum" :value="request('curriculum_id')" class="sm:w-56" aria-label="Filter kurikulum" />
                <x-ui.select name="type" :options="$types" placeholder="Semua jenis" :value="request('type')" class="sm:w-40" aria-label="Filter jenis" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th>Materi</x-ui.th>
            <x-ui.th>Pertemuan</x-ui.th>
            <x-ui.th>Jenis</x-ui.th>
            <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($materials as $material)
            <tr>
                <td class="cell-primary" data-label="Materi">
                    <a href="{{ route('teacher.materials.show', $material) }}" class="font-medium text-fg hover:text-brand-text">{{ $material->title }}</a>
                    <p class="cell-secondary">{{ $material->syllabus?->curriculum?->title }}</p>
                </td>
                <td data-label="Pertemuan">
                    {{ $material->meeting_range }}
                    <p class="cell-secondary">{{ $material->syllabus?->title }}</p>
                </td>
                <td data-label="Jenis">@include('admin.materials.partials.type-badge', ['type' => $material->type])</td>
                <td class="cell-actions">
                    <div class="flex justify-end gap-1">
                        @if ($material->file_path)
                            <x-ui.button variant="ghost" size="sm" icon="arrow-down-tray" label="Unduh {{ $material->title }}" :href="route('teacher.materials.download', $material)" />
                        @endif
                        <x-ui.button variant="ghost" size="sm" icon="eye" label="Buka {{ $material->title }}" :href="route('teacher.materials.show', $material)" />
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="folder-open" title="Tidak ada materi yang cocok."
                description="Ubah filter, atau tunggu admin menambahkan materi untuk kurikulum kelas Anda." />
        </x-slot:empty>

        @if ($materials->hasPages())
            <x-slot:footer>{{ $materials->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
