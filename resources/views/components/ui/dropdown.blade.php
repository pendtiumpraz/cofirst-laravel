{{--
    Menu button. Esc closes and returns focus, arrow keys move between items, click outside closes.

    <x-ui.dropdown label="Ekspor" icon="arrow-down-tray">
        <x-ui.dropdown-item :href="route('…', ['format' => 'csv'])" icon="table-cells">CSV</x-ui.dropdown-item>
        <div class="dropdown-divider"></div>
        <x-ui.dropdown-item danger icon="trash" x-on:click="$dispatch('open-modal', 'hapus-kelas')">Hapus</x-ui.dropdown-item>
    </x-ui.dropdown>

    Custom trigger (use `open` for aria-expanded):
    <x-ui.dropdown align="right">
        <x-slot:trigger><button type="button" class="btn btn-ghost btn-icon" aria-label="Menu lainnya" aria-haspopup="true" x-bind:aria-expanded="open.toString()"><x-ui.icon name="ellipsis-horizontal" /></button></x-slot:trigger>
        …
    </x-ui.dropdown>
    align: right (default) | left · width: 48 | 56 (default) | 64 | 72 | auto · variant/size: trigger button style
--}}
@props([
    'align' => 'right',
    'width' => '56',
    'label' => 'Menu',
    'icon' => null,
    'variant' => 'secondary',
    'size' => 'md',
    'up' => false,
])

@php
    $alignClass = $align === 'left' ? 'left-0 origin-top-left' : 'right-0 origin-top-right';
    $widthClass = match ((string) $width) {
        '48' => 'w-48',
        '64' => 'w-64',
        '72' => 'w-72',
        'auto' => 'w-max max-w-[min(20rem,calc(100vw_-_2rem))]',
        default => 'w-56',
    };
    $variantClass = match ($variant) {
        'primary' => 'btn-primary',
        'ghost' => 'btn-ghost',
        default => 'btn-secondary',
    };
    $sizeClass = match ($size) {
        'sm' => 'btn-sm',
        'xs' => 'btn-xs',
        default => '',
    };
@endphp

<div x-data="cfDropdown"
    x-on:keydown.escape.prevent.stop="close(true)"
    x-on:click.outside="close()"
    x-on:focusout="if (! $el.contains($event.relatedTarget)) close()"
    {{ $attributes->class('relative inline-block text-left') }}>
    <div x-ref="trigger" x-on:click="toggle()" x-on:keydown.arrow-down.prevent="show()">
        @isset($trigger)
            {{ $trigger }}
        @else
            <button type="button" class="btn {{ $variantClass }} {{ $sizeClass }}" aria-haspopup="true" aria-expanded="false" x-bind:aria-expanded="open.toString()">
                @if ($icon)
                    <x-ui.icon :name="$icon" />
                @endif
                <span>{{ $label }}</span>
                <x-ui.icon name="chevron-down" class="!size-4 opacity-70" />
            </button>
        @endisset
    </div>

    <div x-ref="panel" x-show="open" x-cloak
        x-transition:enter="transition duration-fast ease-cf" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition duration-fast" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        x-on:keydown.arrow-down.prevent="move(1)"
        x-on:keydown.arrow-up.prevent="move(-1)"
        x-on:click="if ($event.target.closest('a[href], button')) close()"
        @class(['dropdown-panel', $alignClass, $widthClass, 'bottom-full mb-2 mt-0' => $up])>
        {{ $slot }}
    </div>
</div>
