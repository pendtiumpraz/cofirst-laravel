@php($f = \App\Services\Academic\Format::class)
<x-app-layout :title="'Progres · '.$class->name">
    <x-ui.page-header :title="$class->name" eyebrow="Progres pertemuan"
        :subtitle="trim(($class->curriculum?->title ?? 'Kurikulum belum ditetapkan').' · '.($class->course?->name ?? ''), ' ·')"
        :breadcrumbs="['Kurikulum' => route('teacher.curriculums.index'), 'Progres '.$class->name => null]">
        <x-slot:actions>
            @if (in_array($class->status, ['planned', 'active'], true))
                <x-ui.button icon="clipboard-document-list" :href="route('class-reports.create', ['class_id' => $class->id])">Buat berita acara</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.metric-strip class="mb-8" :stats="[
        ['label' => 'Siswa aktif', 'value' => (string) ($rate['students'] ?? 0), 'icon' => 'user-group'],
        ['label' => 'Pertemuan', 'value' => (string) ($rate['meetings'] ?? 0), 'icon' => 'calendar-days'],
        ['label' => 'Rata-rata selesai', 'value' => $f::percent($rate['percent'] ?? 0), 'icon' => 'chart-bar'],
    ]" />

    <x-ui.alert tone="info" icon="light-bulb" class="mb-6">
        Mengisi berita acara otomatis menandai pertemuan selesai untuk siswa yang dilaporkan. Gunakan pengaturan di bawah untuk menandai satu pertemuan bagi seluruh kelas, misalnya "Sedang berjalan" agar materinya terbuka lebih awal.
    </x-ui.alert>

    @if ($meetings->isEmpty())
        <x-ui.empty-state size="page" icon="queue-list" title="Kelas ini belum memiliki kurikulum aktif."
            description="Hubungi admin untuk menetapkan kurikulum kelas." />
    @else
        <x-ui.table caption="Progres per pertemuan" mobile="stack">
            <x-slot:head>
                <x-ui.th numeric>Ke</x-ui.th>
                <x-ui.th>Pertemuan</x-ui.th>
                <x-ui.th>Siswa selesai</x-ui.th>
                <x-ui.th numeric>Dilaporkan</x-ui.th>
                <x-ui.th>Tandai untuk kelas</x-ui.th>
            </x-slot:head>

            @foreach ($meetings as $row)
                <tr>
                    <td class="num" data-label="Pertemuan ke">{{ $row['syllabus']->meeting_number }}</td>
                    <td class="cell-primary" data-label="Pertemuan">
                        <a href="{{ route('teacher.syllabuses.show', $row['syllabus']) }}" class="font-medium text-fg hover:text-brand-text">{{ $row['syllabus']->title }}</a>
                        @if ($row['in_progress'] > 0)
                            <p class="cell-secondary">{{ $row['in_progress'] }} siswa sedang berjalan</p>
                        @endif
                    </td>
                    <td data-label="Siswa selesai" class="min-w-[9rem]">
                        <x-ui.progress :value="$row['completed']" :max="max(1, $row['students'])" size="sm"
                            :tone="$row['students'] > 0 && $row['completed'] >= $row['students'] ? 'success' : 'brand'"
                            label="{{ $row['completed'] }} dari {{ $row['students'] }} siswa" />
                        <p class="mt-1 text-xs text-fg-subtle">{{ $row['completed'] }}/{{ $row['students'] }} siswa</p>
                    </td>
                    <td class="num" data-label="Dilaporkan">
                        {{ $row['reported'] }}
                        @if ($row['last_report'])
                            <span class="block text-xs text-fg-subtle">{{ $f::date($row['last_report'], 'd M') }}</span>
                        @endif
                    </td>
                    <td data-label="Tandai untuk kelas">
                        <form method="POST" action="{{ route('teacher.curriculums.class-progress.update', $class) }}" class="flex flex-wrap items-center gap-2">
                            @csrf
                            <input type="hidden" name="meeting_number" value="{{ $row['syllabus']->meeting_number }}">
                            <x-ui.select name="status" id="status-{{ $row['syllabus']->meeting_number }}" :options="$statuses" :error="false"
                                :value="$row['students'] > 0 && $row['completed'] >= $row['students'] ? 'completed' : ($row['in_progress'] > 0 ? 'in_progress' : 'not_started')"
                                aria-label="Status pertemuan {{ $row['syllabus']->meeting_number }}" class="w-44" />
                            <x-ui.button type="submit" size="sm" variant="secondary">Simpan</x-ui.button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    @endif
</x-app-layout>
