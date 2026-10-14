{{--
    Agenda of the next N days built from weekly schedule rows (used by the teacher,
    student and parent dashboards).

    <x-schedule-calendar :schedules="$schedules" />
    <x-schedule-calendar :schedules="$schedules" title="Jadwal 7 hari ke depan" :days="7" show-students />

    - Rows of the same class/day/time/room (one per student) are shown once.
    - Class start/end dates, inactive rows and completed/cancelled classes are honoured.
    - Phones: a stacked day list; wider screens: the same list with a date column (no horizontal scroll).
    - title: optional heading (callers that wrap it in a card usually omit it).
    - show-students: show the number of students per session (staff views).
--}}
@props([
    'schedules' => [],
    'days' => 7,
    'title' => null,
    'showStudents' => false,
    'hideEmptyDays' => false,
])

@php
    $rows = $schedules instanceof \Illuminate\Database\Eloquent\Collection
        ? $schedules->loadMissing(['className.course:id,name', 'teacherAssignment.teacher:id,name'])
        : collect($schedules);
    $agenda = \App\Services\Scheduling\ScheduleSlots::agenda($rows, \App\Services\Admin\AdminClock::now(), max(1, min(14, (int) $days)));
    $total = collect($agenda)->sum(fn ($day) => $day['slots']->count());
    $headingId = 'agenda-'.\Illuminate\Support\Str::random(6);
@endphp

<section {{ $attributes->class('w-full') }} @if ($title) aria-labelledby="{{ $headingId }}" @else aria-label="Jadwal {{ (int) $days }} hari ke depan" @endif>
    @if ($title)
        <h2 id="{{ $headingId }}" class="card-title mb-4">{{ $title }}</h2>
    @endif

    @if ($total === 0)
        <x-ui.empty-state icon="calendar-days" title="Tidak ada kelas dalam {{ (int) $days }} hari ke depan."
            description="Jadwal muncul di sini setelah kelas memiliki jadwal mingguan." />
    @else
        <ol class="divide-y divide-line" role="list">
            @foreach ($agenda as $day)
                @continue($hideEmptyDays && $day['slots']->isEmpty())
                <li class="grid gap-2 py-3 first:pt-0 last:pb-0 sm:grid-cols-[8.5rem_1fr] sm:gap-4">
                    <div class="flex items-center gap-2 sm:block">
                        <p @class(['text-sm font-semibold', 'text-brand-text' => $day['is_today'], 'text-fg' => ! $day['is_today']])>
                            {{ $day['date']->translatedFormat('l') }}
                        </p>
                        <p class="text-xs text-fg-subtle">{{ $day['date']->translatedFormat('j M') }}</p>
                        @if ($day['is_today'])
                            <x-ui.badge tone="primary" class="sm:mt-1">Hari ini</x-ui.badge>
                        @endif
                    </div>
                    @if ($day['slots']->isEmpty())
                        <p class="self-center text-sm text-fg-subtle">Tidak ada kelas.</p>
                    @else
                        <ul class="grid gap-2 md:grid-cols-2" role="list">
                            @foreach ($day['slots'] as $slot)
                                @php $teacher = $slot->teacher(); @endphp
                                <li class="rounded-md border border-line bg-surface p-3">
                                    <p class="num font-mono text-xs font-semibold text-fg-muted">{{ $slot->timeRange() }}</p>
                                    <p class="mt-1 text-sm font-medium leading-snug text-fg">{{ $slot->class()?->name ?? 'Kelas' }}</p>
                                    <p class="mt-0.5 text-xs text-fg-muted">
                                        {{ $teacher?->name ?? 'Pengajar belum ditentukan' }}@if ($slot->room()) · {{ $slot->room() }}@endif
                                    </p>
                                    @if ($showStudents)
                                        <x-ui.badge tone="neutral" class="mt-2">{{ $slot->studentCount() }} siswa</x-ui.badge>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
</section>
