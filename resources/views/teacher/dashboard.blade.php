@php
    use App\Services\Teacher\TeachingSchedule;
    use Illuminate\Support\Str;

    $hour = (int) $now->format('G');
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };
    $firstName = Str::before(trim($teacher->name), ' ') ?: $teacher->name;
    $next = $todaySessions->first(fn ($s) => ! $s->hasEnded($now));
    $subtitle = match (true) {
        $todaySessions->isEmpty() => 'Tidak ada sesi mengajar hari ini. Gunakan waktu ini untuk melengkapi laporan.',
        $next !== null => 'Ada '.$todaySessions->count().' sesi hari ini. Sesi berikutnya pukul '.$next->start.'.',
        default => 'Semua sesi hari ini sudah selesai. Terima kasih sudah mengajar.',
    };
    $quickLinks = array_filter([
        ['label' => 'Absensi hari ini', 'hint' => 'Catat kehadiran siswa', 'icon' => 'clipboard-document-check', 'route' => 'teacher.attendance.today'],
        ['label' => 'Tulis berita acara', 'hint' => 'Laporan per pertemuan', 'icon' => 'document-text', 'route' => 'class-reports.create'],
        ['label' => 'Jadwal mengajar', 'hint' => 'Kalender mingguan', 'icon' => 'calendar-days', 'route' => 'teacher.schedules.index'],
        ['label' => 'Siswa saya', 'hint' => 'Kehadiran & progres', 'icon' => 'user-group', 'route' => 'teacher.students.index'],
        ['label' => 'Serah terima', 'hint' => 'Minta guru pengganti', 'icon' => 'arrows-right-left', 'route' => 'teacher.handovers.index'],
        ['label' => 'Materi ajar', 'hint' => 'Kurikulum & materi', 'icon' => 'folder-open', 'route' => 'teacher.materials.index'],
    ], fn ($link) => Route::has($link['route']));
@endphp

<x-app-layout title="Dasbor Pengajar">
    <x-ui.page-header :eyebrow="TeachingSchedule::format($today, 'l, j F Y')" :subtitle="$subtitle">
        <x-slot:title>{{ $greeting }}, <em>{{ $firstName }}</em>.</x-slot:title>
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="calendar-days" :href="route('teacher.schedules.index')">Jadwal</x-ui.button>
            <x-ui.button icon="clipboard-document-check" :href="route('teacher.attendance.today')">Absensi hari ini</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($incomingHandovers->isNotEmpty())
        @php $first = $incomingHandovers->first(); @endphp
        <x-ui.alert tone="info" icon="arrows-right-left" class="mb-6"
            :title="$incomingHandovers->count().' permintaan serah terima menunggu jawaban Anda'">
            {{ $first->fromTeacher?->name ?? 'Seorang pengajar' }} meminta Anda mengajar kelas
            <strong>{{ $first->className?->name }}</strong>
            mulai {{ TeachingSchedule::format($first->effective_from, 'j M Y') }}.
            <x-slot:actions>
                <x-ui.button size="sm" variant="secondary" :href="route('teacher.handovers.index', ['tab' => 'masuk'])" arrow>Tinjau permintaan</x-ui.button>
            </x-slot:actions>
        </x-ui.alert>
    @endif

    <x-ui.metric-strip :stats="$stats" label="Ringkasan mengajar" class="mb-8" />

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Kelas hari ini" :subtitle="TeachingSchedule::format($today, 'l, j M')" padding="none" class="lg:col-span-2">
            <x-slot:actions>
                <x-ui.button variant="link" size="sm" arrow :href="route('teacher.schedules.index')">Jadwal lengkap</x-ui.button>
            </x-slot:actions>

            @if ($todaySessions->isEmpty())
                <x-ui.empty-state icon="calendar" title="Tidak ada sesi hari ini."
                    description="Sesi berikutnya tampil di linimasa minggu ini di bawah." />
            @else
                <ul class="divide-y divide-line" role="list">
                    @foreach ($todaySessions as $session)
                        @include('teacher.schedules.partials.session', ['session' => $session, 'now' => $now])
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card title="Perlu perhatian" subtitle="Kehadiran dan progres siswa" padding="none">
            @if ($attention->isEmpty())
                <x-ui.empty-state icon="face-smile" title="Semua siswa dalam kondisi baik."
                    description="Siswa yang sering absen atau tertinggal materi akan muncul di sini." />
            @else
                <ul class="divide-y divide-line" role="list">
                    @foreach ($attention as $row)
                        <li class="relative flex items-center gap-3 px-4 py-3 hover:bg-surface-2 sm:px-5">
                            <x-ui.avatar :user="$row['student']" size="sm" decorative />
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('teacher.class-detail', $row['class']) }}#siswa" class="block truncate font-medium text-fg after:absolute after:inset-0">
                                    {{ $row['student']->name }}
                                </a>
                                <p class="truncate text-xs text-fg-subtle">{{ $row['class']?->name }}</p>
                            </div>
                            <x-ui.badge :tone="$row['tone']" class="shrink-0">{{ $row['reason'] }}</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>

    <x-ui.card title="Minggu ini" class="mt-6"
        :subtitle="TeachingSchedule::format($weekStart, 'j M').' – '.TeachingSchedule::format($weekStart->addDays(6), 'j M Y')">
        <x-slot:actions>
            <x-ui.button variant="link" size="sm" arrow :href="route('teacher.schedules.index')">Buka kalender</x-ui.button>
        </x-slot:actions>

        <ol class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7" role="list">
            @foreach (range(0, 6) as $offset)
                @php
                    $day = $weekStart->addDays($offset);
                    $daySessions = $week->get($day->toDateString(), collect());
                    $isToday = $day->isSameDay($today);
                @endphp
                {{-- Reason: on phones only days with sessions (and today) are listed, to keep the page short. --}}
                <li @class([
                    'rounded-lg border p-3',
                    'hidden sm:block' => $daySessions->isEmpty() && ! $isToday,
                    'border-brand bg-brand-soft' => $isToday,
                    'border-line bg-surface-2' => ! $isToday,
                ]) @if ($isToday) aria-current="date" @endif>
                    <p class="flex items-baseline justify-between gap-2">
                        <span @class(['text-sm font-medium', 'text-brand-soft-fg' => $isToday, 'text-fg' => ! $isToday])>{{ TeachingSchedule::format($day, 'l') }}</span>
                        <span class="text-xs text-fg-subtle">{{ TeachingSchedule::format($day, 'j M') }}</span>
                    </p>
                    @if ($daySessions->isEmpty())
                        <p class="mt-2 text-xs text-fg-subtle">Tidak ada sesi</p>
                    @else
                        <ul class="mt-2 space-y-1.5" role="list">
                            @foreach ($daySessions as $session)
                                <li>
                                    <a href="{{ route('teacher.class-detail', $session->class) }}"
                                        class="block rounded-md bg-surface px-2.5 py-2 text-xs ring-1 ring-line transition-colors duration-fast hover:ring-line-strong motion-reduce:transition-none">
                                        <span class="font-num text-fg">{{ $session->start }}–{{ $session->end }}</span>
                                        <span class="mt-0.5 block truncate text-fg-muted">{{ $session->class->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ol>
    </x-ui.card>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Laporan pertemuan" subtitle="Absensi dan berita acara 7 hari terakhir" class="lg:col-span-2">
            <x-ui.tabs group="laporan" active="tertunda" :hash="false" label="Laporan pertemuan"
                :tabs="['tertunda' => ['label' => 'Tertunda', 'count' => $pendingCount], 'terbaru' => 'Terbaru ditulis']">
                <x-ui.tab-panel name="tertunda">
                    @if ($pending->isEmpty())
                        <x-ui.empty-state icon="check-circle" title="Tidak ada laporan tertunda."
                            description="Absensi dan berita acara sesi 7 hari terakhir sudah lengkap." />
                    @else
                        <ul class="divide-y divide-line overflow-hidden rounded-lg border border-line" role="list">
                            @foreach ($pending as $session)
                                @include('teacher.schedules.partials.session', ['session' => $session, 'now' => $now, 'showDate' => true])
                            @endforeach
                        </ul>
                        @if ($pendingCount > $pending->count())
                            <p class="mt-3 text-sm text-fg-muted">dan {{ $pendingCount - $pending->count() }} sesi lainnya di halaman <a class="link" href="{{ route('teacher.attendance.today') }}">Absensi</a>.</p>
                        @endif
                    @endif
                </x-ui.tab-panel>
                <x-ui.tab-panel name="terbaru">
                    @if ($recentReports->isEmpty())
                        <x-ui.empty-state icon="document-text" title="Belum ada berita acara."
                            description="Berita acara yang Anda tulis setelah pertemuan tampil di sini." />
                    @else
                        <ul class="divide-y divide-line overflow-hidden rounded-lg border border-line" role="list">
                            @foreach ($recentReports as $report)
                                <li class="relative flex items-center justify-between gap-4 px-4 py-3 hover:bg-surface-2 sm:px-5">
                                    <div class="min-w-0">
                                        <a href="{{ route('class-reports.show', $report) }}" class="block truncate font-medium text-fg after:absolute after:inset-0">
                                            {{ $report->student?->name ?? 'Siswa' }}
                                        </a>
                                        <p class="truncate text-xs text-fg-subtle">
                                            {{ $report->className?->name }}
                                            @if ($report->meeting_number) · Pertemuan {{ $report->meeting_number }} @endif
                                        </p>
                                    </div>
                                    <span class="shrink-0 text-xs text-fg-muted">{{ TeachingSchedule::format($report->report_date, 'j M') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.tab-panel>
            </x-ui.tabs>
        </x-ui.card>

        <x-ui.card title="Akses cepat" padding="none">
            <ul class="divide-y divide-line" role="list">
                @foreach ($quickLinks as $link)
                    <li>
                        <a href="{{ route($link['route']) }}" class="flex min-h-[3.5rem] items-center gap-3 px-4 py-3 transition-colors duration-fast hover:bg-surface-2 motion-reduce:transition-none sm:px-5">
                            <span class="grid size-9 shrink-0 place-items-center rounded-md bg-brand-soft text-brand-soft-fg">
                                <x-ui.icon :name="$link['icon']" size="18" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium text-fg">{{ $link['label'] }}</span>
                                <span class="block text-xs text-fg-subtle">{{ $link['hint'] }}</span>
                            </span>
                            <x-ui.icon name="chevron-right" size="16" class="text-fg-subtle" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    </div>
</x-app-layout>
