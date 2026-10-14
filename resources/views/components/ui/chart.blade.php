{{--
    Theme-aware Chart.js chart (lazy, bundled from npm — no CDN). Follows theme A/B and dark mode.

    <x-ui.chart type="bar" label="Pendapatan naik 12% dibanding bulan lalu" format="idr-compact"
        :labels="['Jul', 'Agu', 'Sep', 'Okt']"
        :datasets="[['label' => 'Pendapatan', 'data' => [32000000, 41000000, 43000000, 48200000]]]"
        highlight-last />

    type: line (default) | bar | doughnut | pie · format: number | idr | idr-compact | percent
    datasets[]: label, data, optional tone (primary|success|warning|danger|info|accent) or color, fill (line)
    label: one-sentence takeaway used as the canvas aria-label (required for accessibility).
    table (default true) adds a 'Lihat tabel' toggle with the same data as an accessible table.
    stacked, horizontal, legend (bool|null auto), highlight-last (bar: current period in the accent series),
    options: raw Chart.js options deep-merged last. height: px (default 280).
--}}
@props([
    'type' => 'line',
    'labels' => [],
    'datasets' => [],
    'options' => [],
    'format' => 'number',
    'label' => 'Grafik',
    'height' => 280,
    'table' => true,
    'stacked' => false,
    'horizontal' => false,
    'legend' => null,
    'highlightLast' => false,
    'caption' => null,
])

@php
    $labelList = collect($labels)->values()->all();
    $datasetList = collect($datasets)->map(fn ($dataset) => array_merge($dataset, [
        'data' => array_values(collect($dataset['data'] ?? [])->all()),
    ]))->values()->all();

    $config = array_filter([
        'type' => $type,
        'data' => ['labels' => $labelList, 'datasets' => $datasetList],
        'options' => $options ?: null,
        'format' => $format,
        'stacked' => $stacked ?: null,
        'horizontal' => $horizontal ?: null,
        'legend' => $legend,
        'highlightLast' => $highlightLast ?: null,
    ], fn ($value) => $value !== null);

    $formatValue = function ($value) use ($format) {
        if (! is_numeric($value)) {
            return (string) $value;
        }

        return match ($format) {
            'idr', 'idr-compact' => 'Rp '.number_format((float) $value, 0, ',', '.'),
            'percent' => number_format((float) $value, 1, ',', '.').'%',
            default => number_format((float) $value, fmod((float) $value, 1) === 0.0 ? 0 : 1, ',', '.'),
        };
    };

    $circular = in_array($type, ['doughnut', 'pie'], true);
@endphp

<figure {{ $attributes->class('m-0 w-full') }} x-data="{ asTable: false }">
    <div class="relative w-full" style="height: {{ (int) $height }}px" x-show="! asTable">
        <canvas data-cf-chart role="img" aria-label="{{ $label }}"></canvas>
        <script type="application/json" data-cf-chart-config>@json($config)</script>
    </div>

    @if ($table)
        <div class="table-scroll rounded-md border border-line" x-show="asTable" x-cloak>
            <table class="table-cf">
                <caption class="sr-only">{{ $label }}</caption>
                <thead>
                    <tr>
                        <th scope="col">{{ $circular ? 'Kategori' : 'Periode' }}</th>
                        @foreach ($datasetList as $dataset)
                            <th scope="col" class="num">{{ $dataset['label'] ?? 'Nilai' }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($labelList as $index => $rowLabel)
                        <tr>
                            <th scope="row" class="font-medium">{{ $rowLabel }}</th>
                            @foreach ($datasetList as $dataset)
                                <td class="num">{{ $formatValue($dataset['data'][$index] ?? '—') }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($table || filled($caption))
        <figcaption class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-fg-subtle">
            <span>{{ $caption }}</span>
            @if ($table)
                <button type="button" class="btn btn-link btn-xs" x-on:click="asTable = ! asTable"
                    x-bind:aria-pressed="asTable.toString()" aria-pressed="false">
                    <x-ui.icon name="table-cells" x-show="! asTable" />
                    <x-ui.icon name="chart-bar" x-show="asTable" x-cloak />
                    <span x-text="asTable ? 'Lihat grafik' : 'Lihat tabel'">Lihat tabel</span>
                </button>
            @endif
        </figcaption>
    @endif
</figure>
