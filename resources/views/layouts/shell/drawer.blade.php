{{--
    Mobile drawer (appearance.mobile_nav = drawer, < 1024px): 85vw (max 320px) panel with
    the full menu. Focus-trapped dialog opened from the app-bar menu button.
--}}
<div id="cf-drawer" x-data="cfDialog" x-show="open" x-cloak @cf-drawer-open.window="show()"
     @keydown.escape.prevent.stop="hide()" @keydown.tab="trap($event)"
     class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-labelledby="cf-drawer-title">
    <div class="absolute inset-0 bg-canvas/75" @click="hide()" aria-hidden="true"
         x-show="open" x-transition.opacity.duration.200ms></div>

    <div x-ref="panel" x-show="open"
         x-transition:enter="transition duration-300 ease-out motion-reduce:transition-none" x-transition:enter-start="-translate-x-full"
         x-transition:leave="transition duration-200 ease-in motion-reduce:transition-none" x-transition:leave-end="-translate-x-full"
         class="absolute inset-y-0 left-0 flex w-[85vw] max-w-[320px] flex-col border-r border-line-strong bg-surface pb-[env(safe-area-inset-bottom)] pt-[env(safe-area-inset-top)] shadow-3">
        <div class="flex h-appbar shrink-0 items-center justify-between gap-2 border-b border-line pl-4 pr-2">
            <h2 id="cf-drawer-title" class="sr-only">Menu navigasi</h2>
            <x-brand.logo size="sm" />
            <button type="button" x-ref="initial" @click="hide()" class="inline-flex size-11 items-center justify-center rounded-md text-fg-muted hover:bg-surface-2 hover:text-fg">
                <x-heroicon-o-x-mark class="size-6" aria-hidden="true" />
                <span class="sr-only">Tutup menu</span>
            </button>
        </div>

        @if (count($roles) > 1)
            <div class="shrink-0 border-b border-line p-2">
                @include('layouts.shell.role-switcher', ['roles' => $roles, 'role' => $role])
            </div>
        @endif

        <nav class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-2 py-3" aria-label="Menu utama">
            @include('layouts.shell.nav-list', ['sections' => $sections, 'rail' => false, 'filterable' => false, 'idPrefix' => 'drawer'])
        </nav>

        <div class="shrink-0 space-y-0.5 border-t border-line p-2">
            <ul role="list" class="space-y-0.5">
                @foreach ($account as $item)
                    @include('layouts.shell.nav-item', ['item' => $item, 'rail' => false, 'filterable' => false])
                @endforeach
            </ul>
            @include('layouts.shell.logout')
        </div>
    </div>
</div>
