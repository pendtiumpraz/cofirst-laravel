{{--
    Table header cell. With `sort` it renders a server-side sort link that keeps the current query
    string (search, filters) and resets `page`: ?sort=name&direction=asc|desc.
    Marked data-sortable="false" so legacy table-enhancements.js does not sort it again.

    <x-ui.th sort="name">Nama</x-ui.th>
    <x-ui.th sort="amount" numeric default-direction="desc">Jumlah</x-ui.th>
    <x-ui.th align="right">Aksi</x-ui.th>
    Controller: $query->orderBy(in_array($r->sort, $allowed) ? $r->sort : 'created_at', $r->direction === 'desc' ? 'desc' : 'asc')
--}}
@props([
    'sort' => null,
    'numeric' => false,
    'align' => null,
    'sortParam' => 'sort',
    'directionParam' => 'direction',
    'defaultDirection' => 'asc',
])

@php
    $currentSort = request()->query($sortParam);
    $currentDirection = request()->query($directionParam) === 'desc' ? 'desc' : 'asc';
    $isActive = $sort !== null && is_string($currentSort) && $currentSort === (string) $sort;
    $nextDirection = $isActive ? ($currentDirection === 'asc' ? 'desc' : 'asc') : ($defaultDirection === 'desc' ? 'desc' : 'asc');

    $url = $sort !== null
        ? request()->fullUrlWithQuery([$sortParam => $sort, $directionParam => $nextDirection, 'page' => null])
        : null;

    $ariaSort = $sort !== null ? ($isActive ? ($currentDirection === 'asc' ? 'ascending' : 'descending') : 'none') : null;

    $alignClass = match ($numeric ? 'right' : $align) {
        'right' => 'num',
        'center' => 'text-center',
        default => null,
    };

    $sortIcon = $isActive ? ($currentDirection === 'asc' ? 'chevron-up' : 'chevron-down') : 'chevron-up-down';
@endphp

<th scope="col" {{ $attributes->class([$alignClass]) }}
    @if ($ariaSort) aria-sort="{{ $ariaSort }}" data-sortable="false" @endif>
    @if ($url)
        <a href="{{ $url }}" class="th-sort" @if ($isActive) aria-current="true" @endif>
            <span>{{ $slot }}</span>
            <x-ui.icon :name="$sortIcon" />
            <span class="sr-only">
                {{ $isActive ? ($currentDirection === 'asc' ? '(urut naik, klik untuk urut turun)' : '(urut turun, klik untuk urut naik)') : '(klik untuk mengurutkan)' }}
            </span>
        </a>
    @else
        {{ $slot }}
    @endif
</th>
