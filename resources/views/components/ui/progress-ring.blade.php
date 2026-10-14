{{--
    Circular progress with the brand's leading marker (seal dot in A, caret-red dot in B).
    <x-ui.progress-ring :value="72" label="Kehadiran" />
    <x-ui.progress-ring :value="40" :size="96" :stroke="8">40<span class="text-xs">%</span></x-ui.progress-ring>
    The slot replaces the centre text (default: the percentage).
--}}
@props([
    'value' => 0,
    'size' => 64,
    'stroke' => 6,
    'label' => 'Progres',
    'marker' => true,
])

@php
    $pixels = max(24, (int) $size);
    $width = max(2, (int) $stroke);
    $percent = max(0, min(100, (float) $value));
    $radius = ($pixels - $width) / 2 - 1;
    $center = $pixels / 2;
    $circumference = 2 * M_PI * $radius;
    $offset = $circumference * (1 - $percent / 100);
    $angle = 2 * M_PI * ($percent / 100);
    $markerX = $center + $radius * cos($angle);
    $markerY = $center + $radius * sin($angle);
    $showMarker = $marker && $percent > 0 && $percent < 100;
    $text = number_format($percent, 0, ',', '.').'%';
@endphp

<div role="img" aria-label="{{ $label }}: {{ $text }}" {{ $attributes->class('progress-ring') }}
    style="width: {{ $pixels }}px; height: {{ $pixels }}px">
    <svg viewBox="0 0 {{ $pixels }} {{ $pixels }}" aria-hidden="true" fill="none">
        <circle class="progress-ring-track" cx="{{ $center }}" cy="{{ $center }}" r="{{ round($radius, 3) }}" stroke-width="{{ $width }}" />
        <circle class="progress-ring-value" cx="{{ $center }}" cy="{{ $center }}" r="{{ round($radius, 3) }}" stroke-width="{{ $width }}"
            stroke-linecap="round" stroke-dasharray="{{ round($circumference, 3) }}" stroke-dashoffset="{{ round($offset, 3) }}" />
        @if ($showMarker)
            <circle class="progress-ring-marker" cx="{{ round($markerX, 3) }}" cy="{{ round($markerY, 3) }}" r="{{ $width / 2 + 0.5 }}" />
        @endif
    </svg>
    <span class="progress-ring-label" style="font-size: {{ max(11, round($pixels / 4.6)) }}px" aria-hidden="true">
        @if ($slot->hasActualContent())
            {{ $slot }}
        @else
            {{ $text }}
        @endif
    </span>
</div>
