{{--
    'Ledger' table in a card: horizontal scroll with edge fade, optional stacked cards on phones.

    <x-ui.table caption="Daftar pengguna" mobile="stack">
        <x-slot:toolbar><x-ui.filter-bar placeholder="Cari nama, email…" /></x-slot:toolbar>
        <x-slot:head>
            <x-ui.th sort="name">Nama</x-ui.th>
            <x-ui.th>Peran</x-ui.th>
            <x-ui.th sort="created_at" numeric>Bergabung</x-ui.th>
            <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>
        @foreach ($users as $user)
            <tr>
                <td class="cell-primary" data-label="Nama">…</td>
                <td data-label="Peran"><x-ui.badge>Guru</x-ui.badge></td>
                <td class="num" data-label="Bergabung">{{ $user->created_at->translatedFormat('d M Y') }}</td>
                <td class="cell-actions">…</td>
            </tr>
        @endforeach
        <x-slot:empty><x-ui.empty-state icon="users" title="Belum ada pengguna." /></x-slot:empty>
        <x-slot:footer>{{ $users->withQueryString()->links() }}</x-slot:footer>
    </x-ui.table>

    mobile: scroll (default) | stack (rows become cards under 768px; give each <td> a data-label)
    sticky: sticky header (adds a max-height scroll area) · sticky-first: sticky first column
    flush: no card frame (when already inside a card) · enhance: legacy client search/sort/No column
    The `empty` slot is shown when the default slot (rows) is empty.
--}}
@props([
    'caption' => null,
    'mobile' => 'scroll',
    'sticky' => false,
    'stickyFirst' => false,
    'flush' => false,
    'enhance' => false,
    'enhanceSearch' => true,
    'enhanceNumbers' => true,
])

@php
    $hasRows = $slot->hasActualContent();
    $mobileMode = $mobile === 'stack' ? 'stack' : 'scroll';
@endphp

<div {{ $attributes->class(['table-wrapper', 'table-wrap' => ! $flush]) }}>
    @isset($toolbar)
        <div {{ $toolbar->attributes->class('table-toolbar') }}>{{ $toolbar }}</div>
    @endisset

    <div @class(['table-scroll', 'max-h-[70vh] overflow-y-auto' => $sticky])
        tabindex="0" role="region" aria-label="{{ $caption ?? 'Tabel' }}">
        <table @class(['table-cf', 'table-sticky' => $sticky, 'table-sticky-first' => $stickyFirst])
            data-mobile="{{ $mobileMode }}"
            @if ($enhance) data-enhance="true" data-searchable="{{ $enhanceSearch ? 'true' : 'false' }}" data-show-no="{{ $enhanceNumbers ? 'true' : 'false' }}" @endif>
            @if (filled($caption))
                <caption class="sr-only">{{ $caption }}</caption>
            @endif
            @isset($head)
                <thead>
                    <tr>{{ $head }}</tr>
                </thead>
            @endisset
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if (! $hasRows && isset($empty))
        <div class="border-t border-line">{{ $empty }}</div>
    @endif

    @isset($footer)
        <div {{ $footer->attributes->class('table-footer') }}>{{ $footer }}</div>
    @endisset
</div>
