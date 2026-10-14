{{--
    <x-ui.badge tone="success" dot>Lunas</x-ui.badge>
    <x-ui.badge tone="warning">Menunggu</x-ui.badge>
    <x-ui.badge mono>INV-2026-0142</x-ui.badge>          mono code badge for IDs / tech tags
    <x-ui.badge outline icon="academic-cap">Guru</x-ui.badge>   role chip
    tone: neutral | primary | accent | success | warning | danger | info
--}}
@props([
    'tone' => 'neutral',
    'dot' => false,
    'mono' => false,
    'outline' => false,
    'solid' => false,
    'icon' => null,
])

@php
    $toneClass = match ($tone) {
        'primary', 'brand' => 'badge-primary',
        'accent' => 'badge-accent',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger', 'error' => 'badge-danger',
        'info' => 'badge-info',
        default => 'badge-neutral',
    };
@endphp

<span {{ $attributes->class([
    'badge',
    $toneClass,
    'badge-dot' => $dot,
    'badge-code' => $mono,
    'badge-outline' => $outline,
    'badge-solid' => $solid,
]) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" />
    @endif
    <span class="truncate">{{ $slot }}</span>
</span>
