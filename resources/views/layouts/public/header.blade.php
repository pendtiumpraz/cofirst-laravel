{{--
    Public sticky header: lockup, primary nav (Beranda, Program, Tentang, Kontak),
    Masuk / Daftar (or Dasbor when signed in) and a disclosure menu on mobile.
    Variables: $transparentHeader (bool — no hairline/background until scrolled).
--}}
@php
    $nav = collect([
        ['Beranda', 'landing', ['landing']],
        ['Program', 'courses', ['courses', 'courses.*']],
        ['Tentang', 'about', ['about']],
        ['Kontak', 'contact', ['contact']],
    ])->filter(fn ($l) => Route::has($l[1]))
      ->map(fn ($l) => ['label' => $l[0], 'url' => route($l[1]), 'active' => request()->routeIs(...$l[2])])
      ->values();
    $canRegister = Route::has('register') && setting('security.registration_enabled');
    $home = Route::has('landing') ? route('landing') : url('/');
@endphp
<header x-data="{ open: false, scrolled: {{ $transparentHeader ? 'false' : 'true' }} }"
        @if ($transparentHeader) @scroll.window.throttle.100ms="scrolled = window.scrollY > 8" @endif
        @keydown.escape.window="if (open) { open = false; $refs.toggle.focus() }"
        class="sticky top-0 z-40 border-b pt-[env(safe-area-inset-top)] transition-colors duration-150 motion-reduce:transition-none {{ $transparentHeader ? '' : 'border-line bg-canvas/[.92]' }}"
        x-bind:class="scrolled || open ? 'border-line bg-canvas/[.92]' : 'border-transparent bg-transparent'">
    <div class="mx-auto flex h-16 max-w-landing items-center gap-6 px-4 sm:px-6">
        <a href="{{ $home }}" class="inline-flex shrink-0 rounded-md">
            <x-brand.logo size="md" />
            <span class="sr-only">— ke beranda</span>
        </a>

        <nav aria-label="Menu utama" class="hidden flex-1 md:block">
            <ul role="list" class="flex items-center gap-1">
                @foreach ($nav as $link)
                    <li>
                        <a href="{{ $link['url'] }}" @if ($link['active']) aria-current="page" @endif
                           @class([
                               'inline-flex min-h-10 items-center rounded-md px-3 text-sm font-medium transition-colors',
                               'text-fg' => $link['active'],
                               'text-fg-muted hover:bg-surface-2 hover:text-fg' => ! $link['active'],
                           ])>{{ $link['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="ml-auto hidden items-center gap-2 md:flex">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                    Dasbor
                    <x-heroicon-m-arrow-right class="size-4" aria-hidden="true" />
                </a>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm text-fg">Masuk</a>
                @endif
                @if ($canRegister)
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
                @endif
            @endauth
        </div>

        <button type="button" x-ref="toggle" @click="open = !open" :aria-expanded="open.toString()" aria-expanded="false" aria-controls="cf-public-menu"
                class="-mr-2 ml-auto inline-flex size-11 items-center justify-center rounded-md text-fg-muted hover:bg-surface-2 hover:text-fg md:hidden">
            <x-heroicon-o-bars-3 class="size-6" x-show="!open" aria-hidden="true" />
            <x-heroicon-o-x-mark class="size-6" x-show="open" x-cloak aria-hidden="true" />
            <span class="sr-only">Menu</span>
        </button>
    </div>

    <div id="cf-public-menu" x-show="open" x-cloak x-transition.opacity.duration.150ms @click.outside="open = false"
         class="border-t border-line bg-canvas md:hidden">
        <nav aria-label="Menu utama (ponsel)" class="mx-auto max-w-landing px-4 py-3 sm:px-6">
            <ul role="list" class="space-y-1">
                @foreach ($nav as $link)
                    <li>
                        <a href="{{ $link['url'] }}" @if ($link['active']) aria-current="page" @endif
                           @class(['flex min-h-11 items-center rounded-md px-3 text-base font-medium', 'bg-surface-2 text-fg' => $link['active'], 'text-fg-muted hover:bg-surface-2 hover:text-fg' => ! $link['active']])>
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="mt-3 grid grid-cols-2 gap-2 border-t border-line pt-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary col-span-2">Dasbor</a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" @class(['btn btn-secondary', 'col-span-2' => ! $canRegister])>Masuk</a>
                    @endif
                    @if ($canRegister)
                        <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
                    @endif
                @endauth
            </div>
        </nav>
    </div>
</header>
