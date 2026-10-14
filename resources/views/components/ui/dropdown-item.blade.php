{{--
    Item for <x-ui.dropdown>.
    <x-ui.dropdown-item :href="route('profile.edit')" icon="user">Profil</x-ui.dropdown-item>
    <x-ui.dropdown-item :href="route('logout')" method="post" icon="arrow-right-start-on-rectangle">Keluar</x-ui.dropdown-item>
    <x-ui.dropdown-item danger icon="trash" x-on:click="…">Hapus</x-ui.dropdown-item>       (button)
    method: post|put|patch|delete renders a tiny form with @csrf. active marks the current item.
--}}
@props([
    'href' => null,
    'icon' => null,
    'danger' => false,
    'active' => false,
    'method' => null,
])

@php
    $classes = ['dropdown-item', 'dropdown-item-danger' => $danger, 'is-active' => $active];
    $verb = $method ? strtoupper($method) : null;
@endphp

@if ($verb && $verb !== 'GET' && $href)
    <form method="POST" action="{{ $href }}" class="contents">
        @csrf
        @if ($verb !== 'POST')
            @method($verb)
        @endif
        <button type="submit" {{ $attributes->class($classes) }}>
            @if ($icon)<x-ui.icon :name="$icon" />@endif
            <span class="truncate">{{ $slot }}</span>
        </button>
    </form>
@elseif ($href)
    <a href="{{ $href }}" @if ($active) aria-current="true" @endif {{ $attributes->class($classes) }}>
        @if ($icon)<x-ui.icon :name="$icon" />@endif
        <span class="truncate">{{ $slot }}</span>
    </a>
@else
    <button type="button" {{ $attributes->class($classes) }}>
        @if ($icon)<x-ui.icon :name="$icon" />@endif
        <span class="truncate">{{ $slot }}</span>
    </button>
@endif
