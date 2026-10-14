{{--
    <x-ui.button>Simpan</x-ui.button>
    <x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan</x-ui.button>
    <x-ui.button variant="secondary" :href="route('admin.users.index')" icon="arrow-left">Kembali</x-ui.button>
    <x-ui.button variant="ghost" icon="pencil-square" label="Ubah" size="sm" />   icon-only (label = aria-label + tooltip)
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'icon' => null,
    'iconRight' => null,
    'arrow' => false,
    'label' => null,
    'loading' => false,
    'loadingText' => null,
    'loadingWhen' => null,
    'block' => false,
    'disabled' => false,
])

@php
    $variantClass = match ($variant) {
        'secondary' => 'btn-secondary',
        'ghost' => 'btn-ghost',
        'soft' => 'btn-soft',
        'danger' => 'btn-danger',
        'danger-ghost' => 'btn-danger-ghost',
        'accent' => 'btn-accent',
        'link' => 'btn-link',
        default => 'btn-primary',
    };

    $sizeClass = match ($size) {
        'xs' => 'btn-xs',
        'sm' => 'btn-sm',
        'lg' => 'btn-lg',
        default => '',
    };

    $iconOnly = $icon && ! $slot->hasActualContent();
    $isDisabled = (bool) ($disabled || $loading);

    $classes = trim(implode(' ', array_filter([
        'btn', $variantClass, $sizeClass,
        $iconOnly ? 'btn-icon' : null,
        $block ? 'btn-block' : null,
    ])));

    $extra = [];
    if ($label) {
        $extra['aria-label'] = $label;
        if ($iconOnly && ! $attributes->has('data-tooltip')) {
            $extra['data-tooltip'] = $label;
        }
    }
    if ($loading) {
        $extra['aria-busy'] = 'true';
    }
@endphp

@if ($href)
    <a @unless ($isDisabled) href="{{ $href }}" @else role="link" aria-disabled="true" @endunless
        {{ $attributes->merge($extra)->class($classes) }}>
@else
    <button type="{{ $type }}" {{ $attributes->merge($extra)->class($classes) }} @disabled($isDisabled)
        @if ($loadingWhen) x-bind:disabled="{{ $loadingWhen }}" x-bind:aria-busy="({{ $loadingWhen }}) ? 'true' : 'false'" @endif>
@endif
    @if ($loading)
        <span class="spinner" aria-hidden="true"></span>
    @elseif ($loadingWhen)
        <span class="spinner" x-show="{{ $loadingWhen }}" x-cloak aria-hidden="true"></span>
        @if ($icon)
            <span class="contents" x-show="!({{ $loadingWhen }})"><x-ui.icon :name="$icon" /></span>
        @endif
    @elseif ($icon)
        <x-ui.icon :name="$icon" />
    @endif

    @if ($loading && $loadingText)
        <span>{{ $loadingText }}</span>
    @elseif ($loadingWhen && $loadingText)
        <span x-show="!({{ $loadingWhen }})">{{ $slot }}</span>
        <span x-show="{{ $loadingWhen }}" x-cloak>{{ $loadingText }}</span>
    @elseif (! $iconOnly)
        {{ $slot }}
    @endif

    @if ($iconRight)
        <x-ui.icon :name="$iconRight" />
    @elseif ($arrow)
        <x-ui.icon name="arrow-right" class="btn-arrow" />
    @endif
@if ($href)
    </a>
@else
    </button>
@endif
