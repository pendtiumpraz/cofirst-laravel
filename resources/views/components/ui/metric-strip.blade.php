{{--
    Hairline KPI strip (html[data-stat="strip"], default) or separate cards (html[data-stat="cards"]).
    Pure CSS switch — same markup.

    <x-ui.metric-strip :stats="[
        ['label' => 'Siswa aktif', 'value' => '312', 'trend' => 'up', 'delta' => '4%', 'hint' => 'bulan ini'],
        ['label' => 'Pendapatan', 'value' => 'Rp 48,2 jt', 'href' => route('finance.dashboard')],
    ]" />

    or with a slot (pass :cols):
    <x-ui.metric-strip :cols="3"> <x-ui.stat … /> <x-ui.stat … /> <x-ui.stat … /> </x-ui.metric-strip>
    Each stats[] item accepts every <x-ui.stat> prop (label, value, unit, hint, trend, delta, trendTone, icon, tone, href, loading).
--}}
@props([
    'stats' => [],
    'cols' => null,
    'label' => 'Ringkasan',
])

@php
    $items = collect($stats)->values();
    $count = $cols ?? ($items->count() ?: 4);
    $count = max(1, min(6, (int) $count));
@endphp

<section aria-label="{{ $label }}" {{ $attributes->class('metric-strip') }} data-cols="{{ $count }}">
    @foreach ($items as $stat)
        <x-ui.stat
            :label="$stat['label'] ?? ''"
            :value="$stat['value'] ?? null"
            :unit="$stat['unit'] ?? null"
            :hint="$stat['hint'] ?? null"
            :trend="$stat['trend'] ?? null"
            :delta="$stat['delta'] ?? null"
            :trend-tone="$stat['trendTone'] ?? ($stat['trend_tone'] ?? null)"
            :icon="$stat['icon'] ?? null"
            :tone="$stat['tone'] ?? 'neutral'"
            :href="$stat['href'] ?? null"
            :loading="$stat['loading'] ?? false"
        />
    @endforeach
    {{ $slot }}
</section>
