@php($f = \App\Services\Academic\Format::class)
<x-app-layout :title="'Laporan · '.$class->name">
    <x-ui.page-header :title="$class->name" eyebrow="Laporan per kelas" :subtitle="$class->course?->name"
        :breadcrumbs="['Laporan siswa' => route('teacher.reports.index'), $class->name => null]">
        <x-slot:actions>
            <x-ui.button icon="plus" :href="route('teacher.reports.create', ['class_id' => $class->id])">Tulis laporan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Laporan terbaru tiap siswa" mobile="stack">
        <x-slot:head>
            <x-ui.th>Siswa</x-ui.th>
            <x-ui.th>Laporan terakhir</x-ui.th>
            <x-ui.th>Progres</x-ui.th>
            <x-ui.th numeric>Jumlah</x-ui.th>
            <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($students as $student)
            @php($reports = $latest->get($student->id, collect()))
            @php($last = $reports->first())
            <tr>
                <td class="cell-primary" data-label="Siswa">
                    <a href="{{ route('teacher.reports.by-student', $student) }}" class="font-medium text-fg hover:text-brand-text">{{ $student->name }}</a>
                </td>
                <td data-label="Laporan terakhir">
                    @if ($last)
                        <a href="{{ route('teacher.reports.show', $last->id) }}" class="link">{{ $last->title }}</a>
                        <p class="cell-secondary">{{ $f::date($last->report_date) }}</p>
                    @else
                        <span class="text-fg-subtle">Belum ada</span>
                    @endif
                </td>
                <td data-label="Progres" class="min-w-[8rem]">
                    @if ($last)
                        <x-ui.progress :value="(float) $last->progress_percentage" size="sm" show-value label="Progres {{ $student->name }}"
                            :tone="\App\Services\Academic\ProgressReportService::progressTone($last->progress_percentage)" />
                    @else
                        —
                    @endif
                </td>
                <td class="num" data-label="Jumlah">{{ $reports->count() }}</td>
                <td class="cell-actions">
                    <x-ui.button variant="ghost" size="sm" icon="plus" label="Tulis laporan untuk {{ $student->name }}" :href="route('teacher.reports.create', ['class_id' => $class->id, 'student_id' => $student->id])" />
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="user-group" title="Kelas ini belum memiliki siswa aktif." />
        </x-slot:empty>
    </x-ui.table>
</x-app-layout>
