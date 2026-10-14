{{--
    One teaching session row (dashboard, attendance today, schedule).
    @param \App\Services\Teacher\TeachingSession $session
    @param \Carbon\CarbonImmutable $now      local "now" (TeachingSchedule::now())
    @param bool $showDate                    print the date above the time (lists spanning several days)
--}}
@php
    $showDate = $showDate ?? false;
    $isToday = $session->isOn($now->startOfDay());
    $ongoing = $session->isOngoing($now);
    $ended = $session->hasEnded($now);
    $actionable = $isToday || $ended;
    $takeUrl = route('teacher.attendance.take', [$session->class, 'date' => $session->date->toDateString()]);
    $reportUrl = Route::has('class-reports.create')
        ? route('class-reports.create', ['schedule_id' => $session->scheduleId, 'class_id' => $session->class->id, 'date' => $session->date->toDateString()])
        : null;

    [$statusTone, $statusText] = match (true) {
        $ongoing => ['info', 'Sedang berlangsung'],
        $ended && ! $session->hasAttendance() => ['warning', 'Absensi belum diisi'],
        $ended && $session->missingReports() > 0 => ['warning', $session->missingReports().' berita acara belum ditulis'],
        $ended => ['success', 'Tercatat lengkap'],
        default => [null, null],
    };
@endphp

<li class="flex flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
    <div class="flex min-w-0 gap-4">
        <div class="w-16 shrink-0">
            @if ($showDate)
                <p class="text-xs font-medium uppercase tracking-eyebrow text-fg-subtle">{{ \App\Services\Teacher\TeachingSchedule::format($session->date, 'D, j M') }}</p>
            @endif
            <p class="font-num text-lg leading-tight text-fg">{{ $session->start }}</p>
            <p class="text-xs text-fg-subtle">s.d. {{ $session->end }}</p>
        </div>

        <div class="min-w-0">
            <p class="font-medium text-fg">
                <a href="{{ route('teacher.class-detail', $session->class) }}" class="hover:text-brand-text hover:underline">{{ $session->class->name }}</a>
            </p>
            <p class="mt-0.5 text-sm text-fg-muted">
                {{ $session->class->course?->name ?? 'Kursus' }}
                @if ($session->room)
                    <span aria-hidden="true">·</span> {{ $session->room }}
                @endif
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <x-ui.badge :icon="$session->isOnline() ? 'video-camera' : 'map-pin'">{{ $session->isOnline() ? 'Online' : 'Tatap muka' }}</x-ui.badge>
                <x-ui.badge icon="user-group">
                    {{ $session->studentName ?? $session->rosterCount().' siswa' }}
                </x-ui.badge>
                @if ($session->hasAttendance())
                    <x-ui.badge tone="neutral">Hadir {{ $session->attendedCount }}/{{ $session->attendanceCount }}</x-ui.badge>
                @endif
                @if ($statusText)
                    <x-ui.badge :tone="$statusTone" dot>{{ $statusText }}</x-ui.badge>
                @endif
            </div>
        </div>
    </div>

    @if ($actionable)
        <div class="flex flex-wrap gap-2 sm:shrink-0 sm:justify-end">
            @if ($session->hasAttendance())
                <x-ui.button variant="secondary" size="sm" icon="clipboard-document-check" :href="$takeUrl">Ubah absensi</x-ui.button>
            @elseif ($ended)
                <x-ui.button size="sm" icon="clipboard-document-check" :href="$takeUrl">Isi absensi</x-ui.button>
            @else
                <x-ui.button size="sm" icon="play" :href="$takeUrl">Mulai kelas</x-ui.button>
            @endif

            @if ($reportUrl && ($ended || $ongoing))
                <x-ui.button variant="ghost" size="sm" icon="document-text" :href="$reportUrl">Berita acara</x-ui.button>
            @endif
        </div>
    @endif
</li>
