{{--
    <x-ui.alert tone="warning" title="Tagihan jatuh tempo">Bayar sebelum 20 Okt 2026.</x-ui.alert>
    <x-ui.alert tone="success" dismissible>Profil diperbarui.</x-ui.alert>
    tone: info (default) | success | warning | danger | neutral · icon: heroicon name or false
    Slot `actions` renders buttons under the text.
--}}
@props([
    'tone' => 'info',
    'title' => null,
    'icon' => null,
    'dismissible' => false,
])

@php
    $toneClass = match ($tone) {
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'danger', 'error' => 'alert-danger',
        'neutral' => 'alert-neutral',
        default => 'alert-info',
    };

    $iconName = $icon === false ? null : ($icon ?? match ($tone) {
        'success' => 'check-circle',
        'warning' => 'exclamation-triangle',
        'danger', 'error' => 'exclamation-circle',
        default => 'information-circle',
    });

    $role = in_array($tone, ['danger', 'error', 'warning'], true) ? 'alert' : 'status';
@endphp

<div role="{{ $role }}"
    @if ($dismissible) x-data="{ open: true }" x-show="open" x-transition.opacity.duration.150ms @endif
    {{ $attributes->class(['alert', $toneClass]) }}>
    @if ($iconName)
        <x-ui.icon :name="$iconName" class="alert-icon" />
    @endif
    <div class="min-w-0 flex-1">
        @if (filled($title))
            <p class="alert-title">{{ $title }}</p>
        @endif
        @if ($slot->hasActualContent())
            <div class="alert-body">{{ $slot }}</div>
        @endif
        @isset($actions)
            <div class="mt-3 flex flex-wrap gap-2">{{ $actions }}</div>
        @endisset
    </div>
    @if ($dismissible)
        <button type="button" class="btn btn-ghost btn-icon btn-xs -mr-1 -mt-1" x-on:click="open = false" aria-label="Tutup pesan">
            <x-ui.icon name="x-mark" />
        </button>
    @endif
</div>
