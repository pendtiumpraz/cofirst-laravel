{{--
    "Lainnya" bottom sheet (tabs mode, < 1024px): search, the full grouped menu,
    the personal items and Keluar. Focus-trapped dialog; Esc, the scrim or the close
    button dismiss it and focus returns to the "Lainnya" tab.
--}}
<div id="cf-more-sheet" x-data="cfDialog" x-show="open" x-cloak @cf-sheet-open.window="show()"
     @keydown.escape.prevent.stop="hide()" @keydown.tab="trap($event)"
     class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-labelledby="cf-more-title">
    <div class="absolute inset-0 bg-canvas/75" @click="hide()" aria-hidden="true"
         x-show="open" x-transition.opacity.duration.200ms></div>

    <div x-ref="panel" x-show="open"
         x-transition:enter="transition duration-300 ease-out motion-reduce:transition-none" x-transition:enter-start="translate-y-full"
         x-transition:leave="transition duration-200 ease-in motion-reduce:transition-none" x-transition:leave-end="translate-y-full"
         class="absolute inset-x-0 bottom-0 flex max-h-[88dvh] flex-col rounded-t-xl border-t border-line-strong bg-surface pb-[env(safe-area-inset-bottom)] shadow-3">
        <div class="mx-auto mt-2 h-1 w-10 shrink-0 rounded-full bg-line-strong" aria-hidden="true"></div>
        <div class="flex shrink-0 items-center justify-between gap-2 px-4 pt-2">
            <h2 id="cf-more-title" class="font-display text-lg font-title tracking-display text-fg">Menu lainnya</h2>
            {{-- Reason: initial focus on the close button, not the search field, so opening the sheet does not pop up the phone keyboard over the menu. --}}
            <button type="button" x-ref="initial" @click="hide()" class="-mr-2 inline-flex size-11 items-center justify-center rounded-md text-fg-muted hover:bg-surface-2 hover:text-fg">
                <x-heroicon-o-x-mark class="size-6" aria-hidden="true" />
                <span class="sr-only">Tutup menu</span>
            </button>
        </div>
        <div class="shrink-0 px-4 pb-2 pt-1">
            <label for="cf-more-search" class="sr-only">Cari menu</label>
            <div class="flex h-11 items-center gap-2 rounded-md border border-line-input bg-surface px-3 transition-shadow focus-within:border-brand focus-within:ring-[3px] focus-within:ring-ring/20">
                <x-heroicon-o-magnifying-glass class="size-5 shrink-0 text-fg-subtle" aria-hidden="true" />
                <input id="cf-more-search" x-model="q" type="search" autocomplete="off" placeholder="Cari menu…"
                       class="h-full w-full border-0 bg-transparent p-0 text-base text-fg placeholder:text-fg-subtle focus:outline-none focus:ring-0">
            </div>
        </div>
        <nav class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-2 pb-3" aria-label="Semua menu">
            @include('layouts.shell.nav-list', ['sections' => $sections, 'rail' => false, 'filterable' => true, 'idPrefix' => 'sheet'])
            @if ($account !== [])
                <div class="mt-5 border-t border-line pt-3" x-show="!q">
                    <ul role="list" class="space-y-0.5">
                        @foreach ($account as $item)
                            @include('layouts.shell.nav-item', ['item' => $item, 'rail' => false, 'filterable' => false])
                        @endforeach
                    </ul>
                </div>
            @endif
        </nav>
        <div class="shrink-0 border-t border-line p-2">
            @include('layouts.shell.logout')
        </div>
    </div>
</div>
