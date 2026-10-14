{{--
    Underline tabs. Two modes:

    1) In-page panels (Alpine, URL-hash aware: /halaman#keamanan opens that tab; arrow keys move):
    <x-ui.tabs group="profil" active="akun" :tabs="['akun' => 'Akun', 'keamanan' => ['label' => 'Keamanan', 'count' => 2, 'icon' => 'shield-check']]">
        <x-ui.tab-panel name="akun">…</x-ui.tab-panel>
        <x-ui.tab-panel name="keamanan">…</x-ui.tab-panel>
    </x-ui.tabs>

    2) Navigation tabs (server-side; any tab with 'href' switches to links + aria-current):
    <x-ui.tabs :tabs="['ringkasan' => ['label' => 'Ringkasan', 'href' => route('…')], …]" active="ringkasan" />

    group: unique per page when several tab sets exist (ids + panel binding) · hash: false to ignore the URL hash
    Pass `active` explicitly to avoid a flash before Alpine starts.
--}}
@props([
    'tabs' => [],
    'active' => null,
    'group' => 'tabs',
    'label' => 'Tab',
    'hash' => true,
])

@php
    $items = [];
    foreach ($tabs as $key => $tab) {
        $tab = is_array($tab) ? $tab : ['label' => $tab];
        $items[(string) $key] = [
            'label' => $tab['label'] ?? (string) $key,
            'count' => $tab['count'] ?? null,
            'icon' => $tab['icon'] ?? null,
            'href' => $tab['href'] ?? null,
        ];
    }
    $keys = array_keys($items);
    $current = $active !== null ? (string) $active : ($keys[0] ?? null);
    $isNav = collect($items)->contains(fn ($tab) => filled($tab['href']));
    $groupId = \Illuminate\Support\Str::slug($group) ?: 'tabs';
@endphp

@if ($isNav)
    <nav aria-label="{{ $label }}" {{ $attributes }}>
        <div class="tabs-list">
            @foreach ($items as $key => $tab)
                <a href="{{ $tab['href'] ?? '#' }}" @class(['tab']) @if ($key === $current) aria-current="page" @endif>
                    @if ($tab['icon'])<x-ui.icon :name="$tab['icon']" />@endif
                    <span>{{ $tab['label'] }}</span>
                    @if ($tab['count'] !== null)<span class="count-pill">{{ $tab['count'] }}</span>@endif
                </a>
            @endforeach
        </div>
    </nav>
@else
    <div x-data="cfTabs(@js($keys), @js($current), @js((bool) $hash))" {{ $attributes }}>
        <div class="tabs-list" role="tablist" aria-label="{{ $label }}" x-on:keydown="onKeydown($event)">
            @foreach ($items as $key => $tab)
                <button type="button" role="tab" class="tab"
                    id="{{ $groupId }}-tab-{{ $key }}"
                    data-tab-key="{{ $key }}"
                    aria-controls="{{ $groupId }}-panel-{{ $key }}"
                    aria-selected="{{ $key === $current ? 'true' : 'false' }}"
                    tabindex="{{ $key === $current ? '0' : '-1' }}"
                    x-bind:aria-selected="isActive(@js($key)).toString()"
                    x-bind:tabindex="isActive(@js($key)) ? 0 : -1"
                    x-on:click="select(@js($key))">
                    @if ($tab['icon'])<x-ui.icon :name="$tab['icon']" />@endif
                    <span>{{ $tab['label'] }}</span>
                    @if ($tab['count'] !== null)<span class="count-pill">{{ $tab['count'] }}</span>@endif
                </button>
            @endforeach
        </div>
        <div class="pt-6">
            {{ $slot }}
        </div>
    </div>
@endif
