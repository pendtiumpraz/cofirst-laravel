@php
    use App\Services\Teacher\TeachingSchedule;
    use Illuminate\Support\Carbon;
@endphp

<x-app-layout title="Siswa Saya">
    <x-ui.page-header title="Siswa saya"
        subtitle="Siswa aktif di kelas yang Anda ajar. Kontak pribadi siswa tidak ditampilkan untuk melindungi data mereka.">
        <x-slot:badge><x-ui.badge>{{ $students->total() }}</x-ui.badge></x-slot:badge>
    </x-ui.page-header>

    <x-ui.table caption="Daftar siswa saya" mobile="stack">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari nama siswa…" class="w-full">
                <x-ui.select name="class" :value="$classFilter" placeholder="Semua kelas" aria-label="Kelas" class="sm:w-56"
                    :options="$classes->pluck('name', 'id')" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th>Siswa</x-ui.th>
            <x-ui.th>Kelas</x-ui.th>
            <x-ui.th>Kehadiran</x-ui.th>
            <x-ui.th>Berita acara terakhir</x-ui.th>
            <x-ui.th numeric>Poin</x-ui.th>
            <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($students as $student)
            @php
                $rate = $rates->get($student->id);
                $percent = $rate && $rate->total > 0 ? round($rate->attended / $rate->total * 100) : null;
                $last = $lastReports->get($student->id);
                $firstClass = $student->enrollments->first()?->className;
            @endphp
            <tr>
                <td class="cell-primary" data-label="Siswa">
                    <div class="flex min-w-[11rem] items-center gap-3">
                        <x-ui.avatar :user="$student" size="sm" decorative />
                        <div class="min-w-0">
                            <p class="font-medium text-fg">{{ $student->name }}</p>
                            @if ($student->points)
                                <p class="cell-secondary">Level {{ $student->points->level }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td data-label="Kelas">
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($student->enrollments as $enrollment)
                            @if ($enrollment->className)
                                <a href="{{ route('teacher.class-detail', $enrollment->className) }}#siswa" class="badge badge-neutral hover:border-line-strong">
                                    {{ $enrollment->className->name }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </td>
                <td data-label="Kehadiran">
                    @if ($percent === null)
                        <span class="text-fg-subtle">Belum ada data</span>
                    @else
                        <span class="inline-flex items-center gap-2 whitespace-nowrap">
                            <x-ui.badge :tone="$percent >= 85 ? 'success' : ($percent >= 75 ? 'warning' : 'danger')">{{ $percent }}%</x-ui.badge>
                            <span class="text-xs text-fg-subtle">{{ $rate->attended }}/{{ $rate->total }} pertemuan</span>
                        </span>
                    @endif
                </td>
                <td data-label="Berita acara terakhir">
                    {{ $last ? TeachingSchedule::format(Carbon::parse($last), 'j M Y') : 'Belum ada' }}
                </td>
                <td class="num" data-label="Poin">{{ number_format((int) ($student->points?->points ?? 0), 0, ',', '.') }}</td>
                <td class="cell-actions">
                    <div class="inline-flex items-center justify-end gap-1 whitespace-nowrap">
                        @if (Route::has('teacher.student-progress.show'))
                            <x-ui.button variant="ghost" size="xs" icon="chart-bar" :href="route('teacher.student-progress.show', $student)">Progres</x-ui.button>
                        @endif
                        @if ($firstClass && Route::has('class-reports.create'))
                            <x-ui.button variant="ghost" size="xs" icon="document-text"
                                :href="route('class-reports.create', ['class_id' => $firstClass->id, 'student_id' => $student->id])">Berita acara</x-ui.button>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            @if ($search !== '' || $classFilter)
                <x-ui.empty-state icon="magnifying-glass" title="Tidak ada siswa yang cocok."
                    description="Coba kata kunci lain atau pilih semua kelas.">
                    <x-slot:actions><x-ui.button variant="secondary" size="sm" :href="route('teacher.students.index')">Hapus filter</x-ui.button></x-slot:actions>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state icon="user-group" title="Belum ada siswa aktif."
                    description="Siswa tampil di sini setelah terdaftar di kelas yang Anda ajar." />
            @endif
        </x-slot:empty>

        @if ($students->hasPages())
            <x-slot:footer>{{ $students->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
