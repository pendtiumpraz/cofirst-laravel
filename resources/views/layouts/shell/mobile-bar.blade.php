{{--
    Mobile app bar (< 1024px, identical for both desktop variants): menu button in drawer
    mode, 28px mark, page title, search, messages and the account menu.
--}}
@php
    $chat = collect(\App\Support\Navigation\Navigation::flatten($sections))->firstWhere('route', 'chat.index');
    $iconButton = 'relative inline-flex size-11 shrink-0 items-center justify-center rounded-md text-fg-muted transition-colors hover:bg-surface-2 hover:text-fg';
@endphp
<header x-data class="sticky top-0 z-30 border-b border-line bg-canvas pt-[env(safe-area-inset-top)] lg:hidden">
    <div class="flex h-appbar items-center gap-1 px-2 sm:px-4">
        @if ($user && $tabs === [])
            <button type="button" @click="$dispatch('cf-drawer-open')" aria-haspopup="dialog" aria-controls="cf-drawer" class="{{ $iconButton }}">
                <x-heroicon-o-bars-3 class="size-6" aria-hidden="true" />
                <span class="sr-only">Buka menu</span>
            </button>
        @endif
        <a href="{{ $home }}" class="inline-flex shrink-0 rounded-md p-1.5">
            <x-brand.logo type="mark" :size="28" />
        </a>
        <p class="min-w-0 flex-1 truncate px-1 font-display text-[1.0625rem] font-title tracking-display text-fg">{{ $pageTitle }}</p>

        @if ($user)
            <button type="button" @click="$dispatch('cf-palette-open')" aria-haspopup="dialog" aria-controls="cf-palette" class="{{ $iconButton }}">
                <x-heroicon-o-magnifying-glass class="size-5" aria-hidden="true" />
                <span class="sr-only">Cari menu</span>
            </button>
            @if ($chat)
                <a href="{{ $chat['url'] }}" class="{{ $iconButton }}" @if ($chat['is_active']) aria-current="page" @endif>
                    <x-heroicon-o-chat-bubble-left-right class="size-5" aria-hidden="true" />
                    <span class="sr-only">Pesan{{ $chat['badge'] ? ', '.$chat['badge'].' belum dibaca' : '' }}</span>
                    @if ($chat['badge'])
                        <span class="absolute right-2.5 top-2.5 size-2 rounded-full bg-accent-solid ring-2 ring-canvas" aria-hidden="true"></span>
                    @endif
                </a>
            @endif
            @include('layouts.shell.user-menu', ['menuId' => 'cf-user-menu-mobile', 'showName' => false])
        @endif
    </div>
</header>
