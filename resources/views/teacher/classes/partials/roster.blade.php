{{--
    Class roster (tab "Siswa" of teacher.class-detail).
    Data minimisation (UU PDP): name and photo only — no email, phone or address.
--}}
@php
    use App\Services\Teacher\TeachingSchedule;
@endphp

<x-ui.table caption="Daftar siswa {{ $class->name }}" mobile="stack">
    <x-slot:head>
        <x-ui.th>Siswa</x-ui.th>
        <x-ui.th>Kehadiran</x-ui.th>
        <x-ui.th>Terakhir</x-ui.th>
        <x-ui.th>Berita acara terakhir</x-ui.th>
        <x-ui.th>Materi tuntas</x-ui.th>
        <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
    </x-slot:head>

    @foreach ($enrollments as $enrollment)
        @php
            $student = $enrollment->student;
            $att = $attendance->get($student->id);
            $rate = $att && $att->total > 0 ? round($att->attended / $att->total * 100) : null;
            $last = $lastStatus->get($student->id);
            $report = $lastReport->get($student->id);
            $done = (int) ($completedByStudent[$student->id] ?? 0);
        @endphp
        <tr>
            <td class="cell-primary" data-label="Siswa">
                <div class="flex items-center gap-3">
                    <x-ui.avatar :user="$student" size="sm" decorative />
                    <span class="font-medium text-fg">{{ $student->name }}</span>
                </div>
            </td>
            <td data-label="Kehadiran">
                @if ($rate === null)
                    <span class="text-fg-subtle">Belum ada data</span>
                @else
                    <div class="flex min-w-[8rem] items-center gap-2">
                        <x-ui.progress :value="$rate" size="sm" class="max-w-[6rem]"
                            :tone="$rate >= 85 ? 'success' : ($rate >= 75 ? 'warning' : 'danger')" />
                        <span class="font-num text-sm text-fg">{{ $rate }}%</span>
                    </div>
                    <span class="text-xs text-fg-subtle">{{ $att->attended }} dari {{ $att->total }} pertemuan</span>
                @endif
            </td>
            <td data-label="Terakhir">
                @if ($last)
                    <x-ui.badge :tone="$tones[$last->status] ?? 'neutral'" dot>{{ $statuses[$last->status] ?? $last->status }}</x-ui.badge>
                    <span class="mt-1 block text-xs text-fg-subtle">{{ TeachingSchedule::format($last->attendance_date, 'j M') }}</span>
                @else
                    <span class="text-fg-subtle">—</span>
                @endif
            </td>
            <td data-label="Berita acara terakhir">
                @if ($report)
                    {{ TeachingSchedule::format(\Illuminate\Support\Carbon::parse($report->last_date), 'j M Y') }}
                    <span class="block text-xs text-fg-subtle">{{ $report->total }} laporan</span>
                @else
                    <span class="text-fg-subtle">Belum ada</span>
                @endif
            </td>
            <td data-label="Materi tuntas">
                <span class="font-num text-fg">{{ $done }}</span><span class="text-fg-subtle">{{ $totalMeetings ? ' / '.$totalMeetings : '' }}</span>
            </td>
            <td class="cell-actions">
                <div class="inline-flex items-center justify-end gap-1 whitespace-nowrap">
                    @if (Route::has('teacher.student-progress.show'))
                        <x-ui.button variant="ghost" size="xs" icon="chart-bar" :href="route('teacher.student-progress.show', $student)">Progres</x-ui.button>
                    @endif
                    @if ($canTake && $reportUrl())
                        <x-ui.button variant="ghost" size="xs" icon="document-text" :href="$reportUrl(['student_id' => $student->id])">Berita acara</x-ui.button>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach

    <x-slot:empty>
        <x-ui.empty-state icon="user-group" title="Belum ada siswa aktif di kelas ini."
            description="Siswa muncul setelah pendaftaran mereka dikonfirmasi admin." />
    </x-slot:empty>
</x-ui.table>

<p class="mt-3 flex items-start gap-2 text-xs text-fg-subtle">
    <x-ui.icon name="shield-check" size="16" />
    <span>Data kontak siswa dan orang tua tidak ditampilkan untuk melindungi data pribadi. Gunakan menu Pesan untuk berkomunikasi.</span>
</p>
