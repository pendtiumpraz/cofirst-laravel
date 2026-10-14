{{--
    Standalone wrapper for the UI kit, independent of the dashboard shell so the preview can force
    theme / colour mode / density / KPI style from the query string.
--}}
@php
    $isDark = $preview['mode'] === 'dark';
    $switchUrl = fn (array $changes) => request()->fullUrlWithQuery(array_merge($changes, ['page' => null]));
@endphp
<!DOCTYPE html>
<html lang="id"
    data-theme="{{ $preview['theme'] }}"
    data-density="{{ $preview['density'] }}"
    data-stat="{{ $preview['stat'] }}"
    data-nav="sidebar"
    data-mobile-nav="tabs"
    data-default-color-mode="{{ $preview['mode'] }}"
    data-user-color-mode="{{ $preview['mode'] }}"
    data-allow-color-mode="true"
    @class(['dark' => $isDark])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="{{ $preview['theme'] === 'b' ? '#FAFAFA' : '#FAF8F4' }}" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="{{ $preview['theme'] === 'b' ? '#09090B' : '#0F0E0D' }}" media="(prefers-color-scheme: dark)">
    <title>UI Kit · {{ setting('general.site_name', 'Coding First') }}</title>
    <x-ui.color-mode-script />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh">
    <a href="#konten" class="btn btn-primary btn-sm sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[90]">Lewati ke konten utama</a>

    <header class="z-40 border-b border-line bg-canvas/95 lg:sticky lg:top-0">
        <div class="mx-auto flex max-w-app flex-wrap items-center gap-x-6 gap-y-3 px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <x-dynamic-component :component="'brand.mark-'.\App\Support\Navigation\Appearance::logoVariant(null, $preview['theme'])" :size="32" />
                <div class="min-w-0">
                    <p class="truncate font-display text-base font-title">UI Kit</p>
                    <p class="truncate text-xs text-fg-subtle">Sistem desain Coding First</p>
                </div>
            </div>

            <nav aria-label="Pratinjau" class="flex flex-wrap items-center gap-2 sm:ml-auto">
                <div class="segmented" role="group" aria-label="Versi tema">
                    <a href="{{ $switchUrl(['theme' => 'a']) }}" class="segmented-item" aria-pressed="{{ $preview['theme'] === 'a' ? 'true' : 'false' }}">A · Sumi &amp; Shu</a>
                    <a href="{{ $switchUrl(['theme' => 'b']) }}" class="segmented-item" aria-pressed="{{ $preview['theme'] === 'b' ? 'true' : 'false' }}">B · Asagi</a>
                </div>
                <div class="segmented" role="group" aria-label="Mode warna">
                    @foreach (['light' => 'Terang', 'dark' => 'Gelap', 'system' => 'Sistem'] as $mode => $text)
                        <a href="{{ $switchUrl(['mode' => $mode]) }}" class="segmented-item" aria-pressed="{{ $preview['mode'] === $mode ? 'true' : 'false' }}">{{ $text }}</a>
                    @endforeach
                </div>
                <div class="segmented" role="group" aria-label="Kerapatan">
                    <a href="{{ $switchUrl(['density' => 'comfortable']) }}" class="segmented-item" aria-pressed="{{ $preview['density'] === 'comfortable' ? 'true' : 'false' }}">Lega</a>
                    <a href="{{ $switchUrl(['density' => 'compact']) }}" class="segmented-item" aria-pressed="{{ $preview['density'] === 'compact' ? 'true' : 'false' }}">Rapat</a>
                </div>
                <div class="segmented" role="group" aria-label="Gaya KPI">
                    <a href="{{ $switchUrl(['stat' => 'strip']) }}" class="segmented-item" aria-pressed="{{ $preview['stat'] === 'strip' ? 'true' : 'false' }}">Strip</a>
                    <a href="{{ $switchUrl(['stat' => 'cards']) }}" class="segmented-item" aria-pressed="{{ $preview['stat'] === 'cards' ? 'true' : 'false' }}">Kartu</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="mx-auto grid max-w-app gap-8 px-4 pb-24 sm:px-6 lg:grid-cols-[13rem_minmax(0,1fr)] lg:px-8">
        <nav aria-label="Daftar isi" class="hidden lg:block">
            <ol class="sticky top-24 space-y-1 pt-8 text-sm">
                @foreach ([
                    'token' => 'Token warna',
                    'tipografi' => 'Tipografi & bentuk',
                    'aksi' => 'Tombol & lencana',
                    'umpan-balik' => 'Umpan balik',
                    'tata-letak' => 'Tata letak & kartu',
                    'formulir' => 'Formulir',
                    'data' => 'Tabel & grafik',
                    'overlay' => 'Dialog & menu',
                    'warisan' => 'Komponen lama',
                ] as $anchor => $text)
                    <li><a href="#{{ $anchor }}" class="block rounded-md px-3 py-1.5 text-fg-muted no-underline hover:bg-surface-2 hover:text-fg">{{ $text }}</a></li>
                @endforeach
            </ol>
        </nav>

        <main id="konten" class="min-w-0 space-y-16 pt-8">
            @yield('content')
        </main>
    </div>

    <x-ui.toast />
</body>
</html>
