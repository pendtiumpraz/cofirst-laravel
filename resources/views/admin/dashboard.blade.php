@php
    use App\Services\Admin\AdminClock;
    use App\Support\Admin\AccessControl;

    $firstName = \Illuminate\Support\Str::before(trim(auth()->user()->name), ' ') ?: auth()->user()->name;

    // Month-over-month change as "12%" with direction.
    $delta = function (int|float $current, int|float $previous): array {
        if ($previous == 0) {
            return ['trend' => $current > 0 ? 'up' : null, 'delta' => null];
        }
        $pct = ($current - $previous) / abs($previous) * 100;

        return [
            'trend' => $pct > 0.5 ? 'up' : ($pct < -0.5 ? 'down' : 'flat'),
            'delta' => number_format(abs($pct), abs($pct) < 10 ? 1 : 0, ',', '.').'%',
        ];
    };

    $enrollTrend = $delta($kpis['enrollments_month'], $kpis['enrollments_prev_to_date']);
    $revenueTrend = $delta($kpis['revenue_month'], $kpis['revenue_prev_to_date']);

    $stats = [
        ['label' => 'Siswa aktif', 'value' => number_format($kpis['active_students'], 0, ',', '.'), 'icon' => 'academic-cap',
            'hint' => 'dari '.number_format($kpis['student_accounts'], 0, ',', '.').' akun siswa', 'href' => route('admin.enrollments.index', ['status' => 'active'])],
        ['label' => 'Kelas berjalan', 'value' => number_format($kpis['active_classes'], 0, ',', '.'), 'icon' => 'rectangle-group',
            'hint' => $kpis['planned_classes'].' direncanakan', 'href' => route('admin.classes.index', ['status' => 'active'])],
        ['label' => 'Pendaftaran bulan ini', 'value' => number_format($kpis['enrollments_month'], 0, ',', '.'), 'icon' => 'clipboard-document-check',
            'trend' => $enrollTrend['trend'], 'delta' => $enrollTrend['delta'], 'hint' => 'periode sama bulan lalu: '.$kpis['enrollments_prev_to_date']],
        ['label' => 'Pendapatan bulan ini', 'value' => AdminClock::rupiahCompact($kpis['revenue_month']), 'icon' => 'banknotes',
            'trend' => $revenueTrend['trend'], 'delta' => $revenueTrend['delta'], 'hint' => 'periode sama bulan lalu: '.AdminClock::rupiahCompact($kpis['revenue_prev_to_date']),
            'href' => $links['finance'] ? route($links['finance']) : null],
        ['label' => 'Tagihan jatuh tempo', 'value' => number_format($kpis['overdue_count'], 0, ',', '.'), 'icon' => 'exclamation-triangle',
            'tone' => $kpis['overdue_count'] > 0 ? 'danger' : 'neutral', 'hint' => AdminClock::rupiah($kpis['overdue_amount'])],
    ];
    if ($kpis['privacy_open'] !== null) {
        $stats[] = ['label' => 'Permintaan data terbuka', 'value' => (string) $kpis['privacy_open'], 'icon' => 'finger-print',
            'tone' => $kpis['privacy_open'] > 0 ? 'warning' : 'neutral', 'hint' => 'tenggat 3×24 jam', 'href' => $links['privacy'] ? route($links['privacy']) : null];
    }

    $actionItems = collect([
        ['count' => $kpis['pending_enrollments'], 'label' => 'pendaftaran menunggu persetujuan', 'icon' => 'clock', 'tone' => 'warning',
            'url' => route('admin.enrollments.index', ['status' => 'pending'])],
        ['count' => $kpis['overdue_count'], 'label' => 'tagihan melewati jatuh tempo', 'icon' => 'receipt-percent', 'tone' => 'danger',
            'url' => $links['finance'] ? route($links['finance']) : null],
        ['count' => $nearCapacity->count(), 'label' => 'kelas hampir penuh', 'icon' => 'user-group', 'tone' => 'info', 'url' => '#kelas-hampir-penuh'],
        ['count' => $kpis['privacy_open'] ?? 0, 'label' => 'permintaan subjek data (UU PDP)', 'icon' => 'finger-print', 'tone' => 'warning',
            'url' => $links['privacy'] ? route($links['privacy']) : null],
    ])->filter(fn ($item) => $item['count'] > 0)->values();

    $revenueTotal = array_sum($chart['revenue']);
    $enrollTotal = array_sum($chart['enrollments']);
@endphp

<x-app-layout title="Beranda">
    <x-ui.page-header :eyebrow="$now->translatedFormat('l, j F Y')" subtitle="Ringkasan sekolah hari ini: siswa, kelas, pendaftaran dan keuangan.">
        <x-slot:title>{{ $greeting }}, <em>{{ $firstName }}</em>.</x-slot:title>
        <x-slot:actions>
            @can('create', App\Models\User::class)
                <x-ui.button variant="secondary" icon="user-plus" :href="route('admin.users.create')">Tambah pengguna</x-ui.button>
            @endcan
            @can('create', App\Models\Enrollment::class)
                <x-ui.button icon="plus" :href="route('admin.enrollments.create')">Daftarkan siswa</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.metric-strip :stats="$stats" :cols="count($stats) > 5 ? 3 : 5" label="Ringkasan sekolah" class="mt-6" />

    <div class="mt-8 grid grid-cols-1 gap-gutter lg:grid-cols-3">
        {{-- Hari ini --}}
        <x-ui.card title="Kelas hari ini" :eyebrow="$now->translatedFormat('l')" padding="none" class="lg:col-span-2">
            <x-slot:actions>
                <x-ui.button variant="link" size="sm" arrow :href="route('admin.schedules.index')">Kalender</x-ui.button>
            </x-slot:actions>
            @if ($today->isEmpty())
                <x-ui.empty-state icon="calendar-days" title="Tidak ada kelas terjadwal hari ini."
                    description="Jadwal mingguan setiap kelas diatur di menu Jadwal." />
            @else
                <ul class="divide-y divide-line" role="list">
                    @foreach ($today as $slot)
                        @php $teacher = $slot->teacher(); @endphp
                        <li class="flex items-start gap-4 px-card py-4">
                            <div class="w-16 shrink-0 pt-0.5 text-right">
                                <p class="num font-mono text-sm font-semibold text-fg">{{ str_replace(':', '.', $slot->start()) }}</p>
                                <p class="num font-mono text-xs text-fg-subtle">{{ str_replace(':', '.', $slot->end()) }}</p>
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.schedules.show', $slot->id()) }}" class="font-medium text-fg hover:text-brand-text">
                                    {{ $slot->class()?->name ?? 'Kelas' }}
                                </a>
                                <p class="mt-0.5 text-sm text-fg-muted">
                                    {{ $slot->class()?->course?->name ?? 'Tanpa kursus' }}
                                    @if ($teacher) · {{ $teacher->name }} @endif
                                    @if ($slot->room()) · {{ $slot->room() }} @endif
                                </p>
                            </div>
                            <x-ui.badge tone="neutral" class="shrink-0">{{ $slot->studentCount() }} siswa</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        {{-- Perlu tindakan + tindakan cepat --}}
        <div class="flex flex-col gap-gutter">
            <x-ui.card title="Perlu tindakan" padding="none">
                @if ($actionItems->isEmpty())
                    <x-ui.empty-state icon="check-circle" title="Semua beres." description="Tidak ada pendaftaran, tagihan atau permintaan yang menunggu." />
                @else
                    <ul class="divide-y divide-line" role="list">
                        @foreach ($actionItems as $item)
                            <li>
                                @if ($item['url'])
                                    <a href="{{ $item['url'] }}" class="flex min-h-[44px] items-center gap-3 px-card py-3 hover:bg-surface-2">
                                @else
                                    <div class="flex min-h-[44px] items-center gap-3 px-card py-3">
                                @endif
                                    <span @class(['flex size-9 shrink-0 items-center justify-center rounded-md',
                                        'bg-warning-soft text-warning' => $item['tone'] === 'warning',
                                        'bg-danger-soft text-danger' => $item['tone'] === 'danger',
                                        'bg-info-soft text-info' => $item['tone'] === 'info'])>
                                        <x-ui.icon :name="$item['icon']" size="20" />
                                    </span>
                                    <span class="min-w-0 flex-1 text-sm text-fg-muted">
                                        <span class="num font-semibold text-fg">{{ $item['count'] }}</span> {{ $item['label'] }}
                                    </span>
                                    @if ($item['url'])
                                        <x-ui.icon name="chevron-right" size="16" class="text-fg-subtle" />
                                    @endif
                                @if ($item['url'])
                                    </a>
                                @else
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            @if ($quickActions->isNotEmpty())
                <x-ui.card title="Tindakan cepat">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($quickActions as $action)
                            <a href="{{ $action['url'] }}"
                                class="flex min-h-[44px] items-center gap-2 rounded-md border border-line px-3 py-2.5 text-sm font-medium text-fg transition-colors duration-fast hover:border-line-strong hover:bg-surface-2 motion-reduce:transition-none">
                                <x-ui.icon :name="$action['icon']" size="18" class="text-brand-text" />
                                <span class="min-w-0">{{ $action['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif

            @if ($isSuperadmin && $links['privacy'])
                <x-ui.card variant="callout" title="Konsol PDP" subtitle="Permintaan subjek data, insiden 3×24 jam, ROPA dan persetujuan." style="--callout-c: var(--cf-info)">
                    <x-ui.button variant="link" size="sm" arrow :href="route($links['privacy'])">Buka konsol privasi</x-ui.button>
                </x-ui.card>
            @endif
        </div>
    </div>

    {{-- Tren 6 bulan --}}
    <div class="mt-8 grid grid-cols-1 gap-gutter lg:grid-cols-2">
        <x-ui.card title="Pendaftaran baru" subtitle="6 bulan terakhir">
            @if ($enrollTotal === 0)
                <x-ui.empty-state size="inline" icon="chart-bar" title="Belum ada pendaftaran dalam 6 bulan terakhir." />
            @else
                <x-ui.chart type="bar" highlight-last :height="240"
                    :label="'Pendaftaran bulan ini '.$kpis['enrollments_month'].', bulan lalu '.$kpis['enrollments_prev'].'; total 6 bulan '.$enrollTotal.'.'"
                    :labels="$chart['labels']"
                    :datasets="[['label' => 'Pendaftaran', 'data' => $chart['enrollments']]]" />
            @endif
        </x-ui.card>

        <x-ui.card title="Pendapatan bersih" subtitle="Pembayaran lunas dikurangi pengembalian, 6 bulan terakhir">
            @if ($revenueTotal === 0)
                <x-ui.empty-state size="inline" icon="banknotes" title="Belum ada pembayaran lunas dalam 6 bulan terakhir." />
            @else
                <x-ui.chart type="bar" format="idr-compact" highlight-last :height="240"
                    :label="'Pendapatan bulan ini '.AdminClock::rupiah($kpis['revenue_month']).', bulan lalu '.AdminClock::rupiah($kpis['revenue_prev']).'.'"
                    :labels="$chart['labels']"
                    :datasets="[['label' => 'Pendapatan', 'data' => $chart['revenue']]]" />
            @endif
        </x-ui.card>
    </div>

    {{-- Aktivitas --}}
    <div class="mt-8 grid grid-cols-1 gap-gutter lg:grid-cols-2">
        <x-ui.card title="Pengguna terbaru" padding="none">
            <x-slot:actions>
                <x-ui.button variant="link" size="sm" arrow :href="route('admin.users.index')">Semua pengguna</x-ui.button>
            </x-slot:actions>
            @if ($recentUsers->isEmpty())
                <x-ui.empty-state icon="users" title="Belum ada pengguna." />
            @else
                <ul class="divide-y divide-line" role="list">
                    @foreach ($recentUsers as $recent)
                        <li class="flex items-center gap-3 px-card py-3">
                            <x-ui.avatar :user="$recent" size="sm" decorative />
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.users.show', $recent) }}" class="block truncate font-medium text-fg hover:text-brand-text">{{ $recent->name }}</a>
                                <p class="truncate text-xs text-fg-subtle">Bergabung {{ AdminClock::format($recent->created_at, 'j M Y') }}</p>
                            </div>
                            <div class="flex shrink-0 flex-wrap justify-end gap-1">
                                @foreach ($recent->roles as $role)
                                    <x-ui.badge outline :tone="AccessControl::roleTone($role->name)">{{ AccessControl::roleLabel($role->name) }}</x-ui.badge>
                                @endforeach
                                @unless ($recent->is_active)
                                    <x-ui.badge tone="neutral">Nonaktif</x-ui.badge>
                                @endunless
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <div class="flex flex-col gap-gutter">
            <x-ui.card title="Menunggu persetujuan" padding="none">
                <x-slot:actions>
                    <x-ui.button variant="link" size="sm" arrow :href="route('admin.enrollments.index', ['status' => 'pending'])">Antrean</x-ui.button>
                </x-slot:actions>
                @if ($pendingEnrollments->isEmpty())
                    <x-ui.empty-state icon="clipboard-document-check" title="Tidak ada pendaftaran yang menunggu." />
                @else
                    <ul class="divide-y divide-line" role="list">
                        @foreach ($pendingEnrollments as $pending)
                            <li class="flex items-center gap-3 px-card py-3">
                                <x-ui.avatar :user="$pending->student" size="sm" decorative />
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('admin.enrollments.show', $pending) }}" class="block truncate font-medium text-fg hover:text-brand-text">{{ $pending->student?->name ?? 'Siswa' }}</a>
                                    <p class="truncate text-xs text-fg-subtle">{{ $pending->className?->name ?? 'Belum ada kelas' }} · {{ AdminClock::format($pending->enrollment_date, 'j M') }}</p>
                                </div>
                                @can('update', $pending)
                                    <form method="POST" action="{{ route('admin.enrollments.toggle-status', $pending) }}">
                                        @csrf
                                        <x-ui.button type="submit" size="sm" variant="soft" icon="check">Setujui</x-ui.button>
                                    </form>
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            <x-ui.card id="kelas-hampir-penuh" title="Kelas hampir penuh" subtitle="Terisi 80% atau lebih" padding="none">
                @if ($nearCapacity->isEmpty())
                    <x-ui.empty-state icon="user-group" title="Semua kelas masih punya kursi kosong." />
                @else
                    <ul class="divide-y divide-line" role="list">
                        @foreach ($nearCapacity as $full)
                            <li class="px-card py-3">
                                <div class="flex items-baseline justify-between gap-3">
                                    <a href="{{ route('admin.classes.show', $full) }}" class="min-w-0 truncate font-medium text-fg hover:text-brand-text">{{ $full->name }}</a>
                                    <span class="num shrink-0 text-sm text-fg-muted">{{ $full->seatsTaken() }}/{{ $full->max_students }}</span>
                                </div>
                                <x-ui.progress class="mt-2" size="sm" :value="$full->seatsTaken()" :max="max(1, $full->max_students)"
                                    :tone="$full->isFull() ? 'danger' : 'warning'" :label="'Kursi terisi '.$full->name" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
