{{--
    Light / dark / system switch bound to Alpine.store('colorMode'). Hidden when the admin disabled
    user choice (<html data-allow-color-mode="false">).

    <x-ui.color-mode-toggle />                    segmented control: Terang · Gelap · Sistem
    <x-ui.color-mode-toggle variant="icon" />     single icon button that cycles (top bar)
--}}
@props([
    'variant' => 'segmented',
])

@if ($variant === 'icon')
    <button type="button" x-data x-show="$store.colorMode.allowed"
        x-on:click="$store.colorMode.cycle()"
        x-bind:aria-label="'Mode warna: ' + $store.colorMode.labels[$store.colorMode.mode] + '. Ganti mode'"
        x-bind:data-tooltip="'Mode: ' + $store.colorMode.labels[$store.colorMode.mode]"
        aria-label="Ganti mode warna"
        {{ $attributes->class('btn btn-ghost btn-icon') }}>
        <span x-show="$store.colorMode.mode === 'light'"><x-ui.icon name="sun" /></span>
        <span x-show="$store.colorMode.mode === 'dark'" x-cloak><x-ui.icon name="moon" /></span>
        <span x-show="$store.colorMode.mode === 'system'" x-cloak><x-ui.icon name="computer-desktop" /></span>
    </button>
@else
    <div x-data x-show="$store.colorMode.allowed" role="radiogroup" aria-label="Mode warna"
        {{ $attributes->class('segmented') }}>
        @foreach (['light' => ['Terang', 'sun'], 'dark' => ['Gelap', 'moon'], 'system' => ['Sistem', 'computer-desktop']] as $mode => [$text, $icon])
            <button type="button" role="radio" class="segmented-item"
                aria-checked="false"
                x-bind:aria-checked="($store.colorMode.mode === @js($mode)).toString()"
                x-on:click="$store.colorMode.set(@js($mode))">
                <x-ui.icon :name="$icon" class="size-4" />
                <span>{{ $text }}</span>
            </button>
        @endforeach
    </div>
@endif
