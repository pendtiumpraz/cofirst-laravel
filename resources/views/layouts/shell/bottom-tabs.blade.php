{{--
    Mobile bottom tab bar (appearance.mobile_nav = tabs, < 1024px): four role tabs from
    Navigation::mobileTabs() + "Lainnya", which opens the full menu as a bottom sheet.
--}}
@php
    $moreHasActive = $current && ! collect($tabs)->contains('route', $current['route']);
@endphp
<nav x-data class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-surface pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Navigasi bawah">
    <ul role="list" @class([
        'mx-auto grid h-tabbar max-w-xl',
        'grid-cols-5' => count($tabs) >= 4,
        'grid-cols-4' => count($tabs) === 3,
        'grid-cols-3' => count($tabs) === 2,
        'grid-cols-2' => count($tabs) <= 1,
    ])>
        @foreach ($tabs as $tab)
            <li>
                <a href="{{ $tab['url'] }}" @if ($tab['is_active']) aria-current="page" @endif
                   @class([
                       'relative flex h-full min-h-12 flex-col items-center justify-center gap-1 px-1 text-[11px] font-semibold transition-colors',
                       'text-tab-active' => $tab['is_active'],
                       'text-fg-subtle hover:text-fg-muted' => ! $tab['is_active'],
                   ])>
                    @if ($tab['is_active'])
                        <span class="absolute left-1/2 top-0 h-0.5 w-4 -translate-x-1/2 rounded-full bg-indicator" aria-hidden="true"></span>
                    @endif
                    <span class="relative">
                        @svg(($tab['is_active'] ? 'heroicon-s-' : 'heroicon-o-').$tab['icon'], 'size-6', ['aria-hidden' => 'true'])
                        @if ($tab['badge'])
                            <span class="absolute -right-0.5 -top-0.5 size-2 rounded-full bg-accent-solid ring-2 ring-surface" aria-hidden="true"></span>
                        @endif
                    </span>
                    <span class="max-w-full truncate">{{ $tab['label'] }}</span>
                    @if ($tab['badge'])<span class="sr-only">, {{ $tab['badge'] }} belum dibaca</span>@endif
                </a>
            </li>
        @endforeach
        <li>
            <button type="button" @click="$dispatch('cf-sheet-open')" aria-haspopup="dialog" aria-controls="cf-more-sheet"
                    @class([
                        'relative flex h-full min-h-12 w-full flex-col items-center justify-center gap-1 px-1 text-[11px] font-semibold transition-colors',
                        'text-tab-active' => $moreHasActive,
                        'text-fg-subtle hover:text-fg-muted' => ! $moreHasActive,
                    ])>
                @svg($moreHasActive ? 'heroicon-s-squares-2x2' : 'heroicon-o-squares-2x2', 'size-6', ['aria-hidden' => 'true'])
                <span>Lainnya</span>
            </button>
        </li>
    </ul>
</nav>
