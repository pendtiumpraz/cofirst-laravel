{{--
    GET filter toolbar: search + filter controls + 'Terapkan' / 'Atur ulang'. '/' focuses the search.

    <x-ui.filter-bar placeholder="Cari nama, email…">
        <x-ui.select name="role" :options="$roleOptions" placeholder="Semua peran" :value="request('role')" class="sm:w-44" />
        <x-slot:actions><x-ui.button variant="secondary" icon="arrow-down-tray" :href="…">Ekspor</x-ui.button></x-slot:actions>
    </x-ui.filter-bar>

    Controllers read request('search') (searchName) plus the filter names. Sorting params listed in
    `keep` (sort, direction, per_page) survive a new search; `page` is always reset.
    Filter controls inside the bar must get their value from request(), not old().
--}}
@props([
    'action' => null,
    'search' => true,
    'searchName' => 'search',
    'placeholder' => 'Cari…',
    'value' => null,
    'keep' => ['sort', 'direction', 'per_page'],
    'submitLabel' => 'Terapkan',
])

@php
    $target = $action ?? url()->current();
    $searchValue = $value ?? request()->query($searchName);
    $searchValue = is_string($searchValue) ? $searchValue : null;
    $searchId = 'filter-'.\Illuminate\Support\Str::slug($searchName);

    $kept = collect($keep)
        ->mapWithKeys(fn ($key) => [$key => request()->query($key)])
        ->filter(fn ($v) => is_string($v) && $v !== '');

    $active = collect(request()->query())
        ->except(array_merge(['page'], (array) $keep))
        ->filter(fn ($v) => $v !== null && $v !== '' && $v !== []);

    $resetUrl = $target.($kept->isNotEmpty() ? '?'.http_build_query($kept->all()) : '');
@endphp

<form method="GET" action="{{ $target }}" role="search"
    {{ $attributes->class('flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center') }}>
    @foreach ($kept as $key => $keptValue)
        <input type="hidden" name="{{ $key }}" value="{{ $keptValue }}">
    @endforeach

    @if ($search)
        <div class="relative w-full sm:w-auto sm:min-w-[16rem] sm:flex-1 lg:max-w-sm">
            <label for="{{ $searchId }}" class="sr-only">{{ $placeholder }}</label>
            <div class="input-group">
                <x-ui.icon name="magnifying-glass" class="input-icon" />
                <input type="search" id="{{ $searchId }}" name="{{ $searchName }}" value="{{ $searchValue }}"
                    class="input pr-10" placeholder="{{ $placeholder }}" autocomplete="off" enterkeyhint="search" data-search-shortcut>
                <kbd class="kbd pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 sm:inline-flex" aria-hidden="true">/</kbd>
            </div>
        </div>
    @endif

    {{ $slot }}

    <div class="flex items-center gap-2">
        <button type="submit" class="btn btn-secondary">
            <x-ui.icon name="funnel" />
            <span>{{ $submitLabel }}</span>
        </button>
        @if ($active->isNotEmpty())
            <a href="{{ $resetUrl }}" class="btn btn-ghost">Atur ulang</a>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2 sm:ml-auto">{{ $actions }}</div>
    @endisset
</form>
