<x-app-layout title="Silabus">
    <x-ui.page-header title="Silabus"
        subtitle="Rencana setiap pertemuan dari kurikulum kelas Anda: tujuan, kegiatan dan materinya." />

    <x-ui.table caption="Daftar pertemuan" mobile="stack">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari judul pertemuan…">
                <x-ui.select name="curriculum_id" :options="$curriculumOptions" placeholder="Semua kurikulum" :value="request('curriculum_id')" class="sm:w-64" aria-label="Filter kurikulum" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th numeric>Ke</x-ui.th>
            <x-ui.th>Pertemuan</x-ui.th>
            <x-ui.th>Kurikulum</x-ui.th>
            <x-ui.th numeric>Durasi</x-ui.th>
            <x-ui.th numeric>Materi</x-ui.th>
        </x-slot:head>

        @foreach ($syllabuses as $syllabus)
            <tr>
                <td class="num" data-label="Pertemuan ke">{{ $syllabus->meeting_number }}</td>
                <td class="cell-primary" data-label="Pertemuan">
                    <a href="{{ route('teacher.syllabuses.show', $syllabus) }}" class="font-medium text-fg hover:text-brand-text">{{ $syllabus->title }}</a>
                </td>
                <td data-label="Kurikulum">
                    {{ $syllabus->curriculum?->title }}
                    <p class="cell-secondary">{{ $syllabus->curriculum?->course?->name }}</p>
                </td>
                <td class="num" data-label="Durasi">{{ $syllabus->duration_minutes }} mnt</td>
                <td class="num" data-label="Materi">{{ $syllabus->active_materials_count }}</td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="document-text" title="Belum ada pertemuan untuk kelas Anda."
                description="Pertemuan muncul setelah kelas Anda memiliki kurikulum." />
        </x-slot:empty>

        @if ($syllabuses->hasPages())
            <x-slot:footer>{{ $syllabuses->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
