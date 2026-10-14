{{--
    Panel for <x-ui.tabs> (in-page mode). `name` must match a key of the parent's :tabs.
    <x-ui.tab-panel name="keamanan">…</x-ui.tab-panel>
--}}
@aware(['group' => 'tabs', 'active' => null])

@props([
    'name',
])

@php
    $groupId = \Illuminate\Support\Str::slug($group) ?: 'tabs';
    $known = $active !== null;
    $isActive = $known && (string) $active === (string) $name;
@endphp

<div role="tabpanel" tabindex="0"
    id="{{ $groupId }}-panel-{{ $name }}"
    aria-labelledby="{{ $groupId }}-tab-{{ $name }}"
    x-show="isActive(@js((string) $name))"
    @if (! $known) x-cloak @elseif (! $isActive) style="display: none" @endif
    {{ $attributes->class('focus-visible:outline-offset-4') }}>
    {{ $slot }}
</div>
