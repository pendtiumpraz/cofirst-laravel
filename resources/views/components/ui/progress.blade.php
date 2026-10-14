{{--
    <x-ui.progress :value="72" label="Progres kursus" show-value />
    <x-ui.progress :value="$paid" :max="$total" tone="success" size="sm" label="Terbayar" />
    tone: brand (default) | success | warning | danger | info | accent · size: sm | md | lg
--}}
@props([
    'value' => 0,
    'max' => 100,
    'tone' => 'brand',
    'label' => null,
    'showValue' => false,
    'size' => 'md',
])

@php
    $maxValue = (float) $max > 0 ? (float) $max : 100;
    $percent = max(0, min(100, ((float) $value / $maxValue) * 100));
    $percentText = number_format($percent, $percent < 10 && $percent > 0 && fmod($percent, 1) !== 0.0 ? 1 : 0, ',', '.').'%';

    $toneClass = match ($tone) {
        'success' => 'progress-success',
        'warning' => 'progress-warning',
        'danger' => 'progress-danger',
        'info' => 'progress-info',
        'accent' => 'progress-accent',
        default => null,
    };

    $sizeClass = match ($size) {
        'sm' => 'progress-sm',
        'lg' => 'progress-lg',
        default => null,
    };
@endphp

<div {{ $attributes->class('w-full') }}>
    @if (filled($label) || $showValue)
        <div class="mb-1.5 flex items-baseline justify-between gap-3 text-[0.8125rem]">
            @if (filled($label))
                <span class="font-medium text-fg-muted">{{ $label }}</span>
            @endif
            @if ($showValue)
                <span class="num ml-auto text-fg-subtle">{{ $percentText }}</span>
            @endif
        </div>
    @endif
    <div role="progressbar" aria-valuemin="0" aria-valuemax="{{ $maxValue + 0 }}" aria-valuenow="{{ (float) $value + 0 }}"
        aria-valuetext="{{ $percentText }}" aria-label="{{ $label ?? 'Progres' }}"
        @class(['progress', $toneClass, $sizeClass])>
        <div class="progress-bar" style="width: {{ round($percent, 2) }}%"></div>
    </div>
</div>
