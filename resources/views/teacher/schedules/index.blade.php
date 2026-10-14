@php
    use App\Models\Schedule;
    use App\Services\Teacher\TeachingSchedule;

    $hours = rtrim(rtrim(number_format($weeklyMinutes / 60, 1, ',', '.'), '0'), ',');
    $range = TeachingSchedule::format($weekStart, 'j M').' – '.TeachingSchedule::format($weekEnd, 'j M Y');
@endphp

<x-app-layout title="Jadwal Mengajar">
    <x-ui.page-header title="Jadwal mengajar" :eyebrow="$range"
        :subtitle="$sessionCount > 0 ? $sessionCount.' sesi · '.$hours.' jam mengajar pada minggu ini.' : 'Tidak ada sesi mengajar pada minggu ini.'">
        <x-slot:actions>
            <nav class="btn-group" aria-label="Navigasi minggu">
                <x-ui.button variant="secondary" icon="chevron-left" label="Minggu sebelumnya"
                    :href="route('teacher.schedules.index', ['week' => $weekStart->subWeek()->toDateString()])" />
                <x-ui.button variant="secondary" :href="route('teacher.schedules.index')" :aria-current="$isCurrentWeek ? 'page' : null">Minggu ini</x-ui.button>
                <x-ui.button variant="secondary" icon="chevron-right" label="Minggu berikutnya"
                    :href="route('teacher.schedules.index', ['week' => $weekStart->addWeek()->toDateString()])" />
            </nav>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="space-y-3">
        @foreach ($days as $key => $day)
            @php
                $isToday = $day['date']->isSameDay($today);
                $count = $day['sessions']->count();
            @endphp
            <section aria-labelledby="hari-{{ $key }}" @class(['card overflow-hidden', 'ring-1 ring-brand' => $isToday])>
                <header @class([
                    'flex flex-wrap items-center justify-between gap-2 px-4 py-3 sm:px-5',
                    'border-b border-line' => $count > 0,
                    'bg-brand-soft' => $isToday,
                ])>
                    <h2 id="hari-{{ $key }}" class="flex items-baseline gap-2">
                        <span @class(['font-medium', 'text-brand-soft-fg' => $isToday, 'text-fg' => ! $isToday])>{{ TeachingSchedule::format($day['date'], 'l') }}</span>
                        <span class="text-sm text-fg-muted">{{ TeachingSchedule::format($day['date'], 'j F') }}</span>
                    </h2>
                    <div class="flex items-center gap-2">
                        @if ($isToday)
                            <x-ui.badge tone="primary">Hari ini</x-ui.badge>
                        @endif
                        <span class="text-sm text-fg-subtle">{{ $count > 0 ? $count.' sesi' : 'Tidak ada sesi' }}</span>
                    </div>
                </header>

                @if ($count > 0)
                    <ul class="divide-y divide-line" role="list">
                        @foreach ($day['sessions'] as $session)
                            @include('teacher.schedules.partials.session', ['session' => $session, 'now' => $now])
                        @endforeach
                    </ul>
                @endif
            </section>
        @endforeach
    </div>

    <x-ui.section-heading class="mt-10" title="Slot mingguan" level="2"
        description="Jadwal rutin semua kelas yang sedang Anda ajar." />

    <x-ui.table caption="Slot mingguan" mobile="stack">
        <x-slot:head>
            <x-ui.th>Hari</x-ui.th>
            <x-ui.th>Waktu</x-ui.th>
            <x-ui.th>Kelas</x-ui.th>
            <x-ui.th>Tempat</x-ui.th>
            <x-ui.th>Siswa</x-ui.th>
        </x-slot:head>
        @foreach ($slots as $slot)
            <tr>
                <td data-label="Hari">{{ Schedule::DAYS[$slot['day']] ?? '—' }}</td>
                <td class="font-num" data-label="Waktu">{{ $slot['start'] }}–{{ $slot['end'] }}</td>
                <td class="cell-primary" data-label="Kelas">
                    <a href="{{ route('teacher.class-detail', $slot['class']) }}" class="hover:text-brand-text hover:underline">{{ $slot['class']->name }}</a>
                    <span class="cell-secondary block">{{ $slot['class']->course?->name }}</span>
                </td>
                <td data-label="Tempat">{{ $slot['room'] ?? '—' }}</td>
                <td data-label="Siswa">{{ $slot['student_name'] ?? $slot['class']->active_students_count.' siswa' }}</td>
            </tr>
        @endforeach
        <x-slot:empty>
            <x-ui.empty-state icon="calendar" title="Belum ada jadwal rutin."
                description="Jadwal muncul setelah admin menugaskan Anda ke kelas dan siswa terdaftar." />
        </x-slot:empty>
    </x-ui.table>
</x-app-layout>
