{{--
    Right-hand actions of the desktop bars: search (command palette), colour mode,
    messages, notifications, user menu. Variables: the $shell array of layouts.app.
--}}
@php
    $chat = collect(\App\Support\Navigation\Navigation::flatten($sections))->firstWhere('route', 'chat.index');
    $compactActions ??= false;
    $iconButton = 'relative inline-flex size-10 items-center justify-center rounded-md text-fg-muted transition-colors hover:bg-surface-2 hover:text-fg';
@endphp
<div x-data class="flex shrink-0 items-center gap-1">
    @unless ($compactActions)
    <button type="button" @click="$dispatch('cf-palette-open')" aria-haspopup="dialog" aria-controls="cf-palette"
            class="hidden h-10 w-72 items-center gap-2 rounded-md border border-line-input bg-surface px-3 text-sm text-fg-subtle transition-colors hover:border-line-strong xl:flex">
        <x-heroicon-o-magnifying-glass class="size-4 shrink-0" aria-hidden="true" />
        <span class="flex-1 text-left">Cari menu…</span>
        <kbd class="kbd">Ctrl K</kbd>
    </button>
    @endunless
    <button type="button" @click="$dispatch('cf-palette-open')" aria-haspopup="dialog" aria-controls="cf-palette" @class([$iconButton, 'xl:hidden' => ! $compactActions])>
        <x-heroicon-o-magnifying-glass class="size-5" aria-hidden="true" />
        <span class="sr-only">Cari menu</span>
    </button>

    @if ($ui['allow_color_mode'])
        <div class="relative" x-data="cfMenu" @keydown.escape.stop="close()" @click.outside="close(false)">
            <button type="button" x-ref="button" @click="toggle()" :aria-expanded="open.toString()" aria-expanded="false" aria-controls="cf-mode-panel" class="{{ $iconButton }}">
                <x-heroicon-o-sun class="size-5 [.dark_&]:hidden" aria-hidden="true" />
                <x-heroicon-o-moon class="hidden size-5 [.dark_&]:block" aria-hidden="true" />
                <span class="sr-only">Mode tampilan</span>
            </button>
            <div id="cf-mode-panel" x-ref="panel" x-show="open" x-cloak x-transition.opacity.duration.150ms
                 class="absolute right-0 top-full z-50 mt-2 w-64 rounded-lg border border-line-strong bg-surface p-2.5 shadow-2">
                <p id="cf-mode-label" class="mb-2 text-xs font-medium text-fg-subtle">Mode tampilan</p>
                @include('layouts.shell.color-mode-options', ['labelledBy' => 'cf-mode-label'])
            </div>
        </div>
    @endif

    @if (Route::has('notifications.index'))
        <a href="{{ route('notifications.index') }}" class="{{ $iconButton }}">
            <x-heroicon-o-bell class="size-5" aria-hidden="true" />
            <span class="sr-only">Notifikasi</span>
        </a>
    @endif

    @if ($chat)
        <a href="{{ $chat['url'] }}" class="{{ $iconButton }}" @if ($chat['is_active']) aria-current="page" @endif>
            <x-heroicon-o-chat-bubble-left-right class="size-5" aria-hidden="true" />
            <span class="sr-only">Pesan{{ $chat['badge'] ? ', '.$chat['badge'].' belum dibaca' : '' }}</span>
            @if ($chat['badge'])
                <span class="absolute right-2 top-2 size-2 rounded-full bg-accent-solid ring-2 ring-canvas" aria-hidden="true"></span>
            @endif
        </a>
    @endif

    @if ($user)
        <div class="ml-1">
            @include('layouts.shell.user-menu', ['menuId' => 'cf-user-menu-desktop', 'showName' => ! $compactActions])
        </div>
    @elseif (Route::has('login'))
        <a href="{{ route('login') }}" class="ml-1 inline-flex min-h-10 items-center rounded-md px-3 text-sm font-semibold text-fg hover:bg-surface-2">Masuk</a>
    @endif
</div>
