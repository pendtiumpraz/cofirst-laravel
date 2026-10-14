{{--
    <x-ui.breadcrumbs :items="[['label' => 'Pengguna', 'url' => route('admin.users.index')], ['label' => 'Budi Santoso']]" />
    or associative: :items="['Pengguna' => route('admin.users.index'), 'Budi Santoso' => null]"
    The last item (or any item without url) is the current page.
--}}
@props([
    'items' => [],
])

@php
    $crumbs = [];
    foreach ($items as $key => $item) {
        if (is_array($item)) {
            $crumbs[] = ['label' => $item['label'] ?? '', 'url' => $item['url'] ?? ($item['href'] ?? null)];
        } elseif (is_string($key)) {
            $crumbs[] = ['label' => $key, 'url' => $item];
        } else {
            $crumbs[] = ['label' => (string) $item, 'url' => null];
        }
    }
    $lastIndex = count($crumbs) - 1;
@endphp

@if ($crumbs)
    <nav aria-label="Jejak halaman" {{ $attributes->class('min-w-0') }}>
        <ol class="breadcrumbs">
            @foreach ($crumbs as $index => $crumb)
                <li class="flex min-w-0 items-center gap-1.5">
                    @if ($index > 0)
                        <span class="breadcrumbs-sep" aria-hidden="true">/</span>
                    @endif
                    @if ($index === $lastIndex || blank($crumb['url']))
                        <span @if ($index === $lastIndex) aria-current="page" @endif class="truncate">{{ $crumb['label'] }}</span>
                    @else
                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
