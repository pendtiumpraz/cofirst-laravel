{{--
    <x-ui.stat label="Pendapatan bulan ini" value="Rp 48,2 jt" trend="up" delta="12%" hint="dibanding bulan lalu" icon="banknotes" />
    <x-ui.stat label="Kehadiran" value="94,1" unit="%" :href="route('…')" />
    value null/'' renders '—' (Belum ada data) — never a fake zero.
    trend: up | down | flat · trend-tone overrides colour (e.g. trend="down" trend-tone="success" for 'Terlambat turun')
    tone (icon colour): neutral | primary | accent | success | warning | danger | info
    Default slot renders under the numbers (sparkline, progress, link).
--}}
@props([
    'label',
    'value' => null,
    'unit' => null,
    'hint' => null,
    'trend' => null,
    'delta' => null,
    'trendTone' => null,
    'icon' => null,
    'tone' => 'neutral',
    'href' => null,
    'loading' => false,
])

@php
    $iconTone = match ($tone) {
        'primary', 'brand' => 'stat-icon-primary',
        'accent' => 'stat-icon-accent',
        'success' => 'stat-icon-success',
        'warning' => 'stat-icon-warning',
        'danger' => 'stat-icon-danger',
        'info' => 'stat-icon-info',
        default => null,
    };

    $resolvedTrendTone = $trendTone ?? match ($trend) {
        'up' => 'success',
        'down' => 'danger',
        default => 'neutral',
    };

    $deltaClass = match ($resolvedTrendTone) {
        'success' => 'stat-delta-success',
        'danger' => 'stat-delta-danger',
        default => 'stat-delta-neutral',
    };

    $trendIcon = match ($trend) {
        'up' => 'm-arrow-up',
        'down' => 'm-arrow-down',
        'flat' => 'm-minus',
        default => null,
    };

    $trendText = match ($trend) {
        'up' => 'Naik',
        'down' => 'Turun',
        'flat' => 'Tetap',
        default => null,
    };

    $isEmpty = $value === null || $value === '';
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class('stat') }}>
    <p class="stat-label">
        @if ($icon)
            <x-ui.icon :name="$icon" :class="$iconTone" />
        @endif
        <span class="min-w-0">{{ $label }}</span>
    </p>

    @if ($loading)
        <span class="skeleton mt-1 h-8 w-28" aria-hidden="true"></span>
        <span class="sr-only">Memuat…</span>
    @else
        <p class="stat-value">
            @if ($isEmpty)
                <span title="Belum ada data">—</span><span class="sr-only">Belum ada data</span>
            @else
                {{-- Reason: never truncate a figure ("Rp 48,2…" hides the unit); wrap as a last resort. --}}
                <span class="min-w-0 break-words">{{ $value }}</span>
                @if (filled($unit))
                    <span class="stat-unit">{{ $unit }}</span>
                @endif
            @endif
        </p>
    @endif

    @if ($trend || filled($delta) || filled($hint))
        <p class="stat-foot">
            @if ($trend || filled($delta))
                <span class="stat-delta {{ $deltaClass }}">
                    @if ($trendIcon)
                        <x-ui.icon :name="$trendIcon" />
                    @endif
                    @if ($trendText)
                        <span class="sr-only">{{ $trendText }}</span>
                    @endif
                    {{ $delta }}
                </span>
            @endif
            @if (filled($hint))
                <span>{{ $hint }}</span>
            @endif
        </p>
    @endif

    {{ $slot }}
</{{ $tag }}>
