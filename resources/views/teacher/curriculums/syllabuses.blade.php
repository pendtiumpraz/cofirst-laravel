<x-app-layout :title="'Pertemuan · '.$curriculum->title">
    <x-ui.page-header title="Daftar pertemuan" :subtitle="$curriculum->title"
        :breadcrumbs="['Kurikulum' => route('teacher.curriculums.index'), $curriculum->title => route('teacher.curriculums.show', $curriculum), 'Pertemuan' => null]" />

    <x-ui.table caption="Pertemuan kurikulum {{ $curriculum->title }}" mobile="stack">
        <x-slot:head>
            <x-ui.th numeric>Ke</x-ui.th>
            <x-ui.th>Judul</x-ui.th>
            <x-ui.th numeric>Durasi</x-ui.th>
            <x-ui.th numeric>Materi</x-ui.th>
            <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($syllabuses as $syllabus)
            <tr>
                <td class="num" data-label="Pertemuan ke">{{ $syllabus->meeting_number }}</td>
                <td class="cell-primary" data-label="Judul">
                    <a href="{{ route('teacher.syllabuses.show', $syllabus) }}" class="font-medium text-fg hover:text-brand-text">{{ $syllabus->title }}</a>
                    @if (filled($syllabus->description))
                        <p class="cell-secondary line-clamp-1">{{ $syllabus->description }}</p>
                    @endif
                </td>
                <td class="num" data-label="Durasi">{{ $syllabus->duration_minutes }} mnt</td>
                <td class="num" data-label="Materi">{{ $syllabus->active_materials_count }}</td>
                <td class="cell-actions">
                    <x-ui.button variant="ghost" size="sm" icon="arrow-right" label="Buka pertemuan {{ $syllabus->meeting_number }}" :href="route('teacher.syllabuses.show', $syllabus)" />
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="calendar-days" title="Belum ada pertemuan aktif." />
        </x-slot:empty>
    </x-ui.table>
</x-app-layout>
