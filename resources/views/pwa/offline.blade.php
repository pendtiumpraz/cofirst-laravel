{{--
    Offline fallback (GET /offline, route pwa.offline). Precached by public/sw.js and
    shown for page navigations without a connection. Anonymous by design: no CSRF token,
    no user data, so it is safe to keep in the cache of a shared device.
--}}
<!DOCTYPE html>
<html {!! \App\Support\Navigation\Appearance::htmlAttributes() !!}>
<head>
    @include('layouts.partials.head', ['pageTitle' => 'Anda sedang offline', 'withCsrf' => false, 'robots' => 'noindex'])
</head>
<body class="min-h-dvh bg-canvas font-sans text-fg antialiased">
    <main class="mx-auto flex min-h-dvh max-w-md flex-col items-center justify-center px-6 py-16 text-center">
        <x-brand.logo type="mark" :size="56" />

        <div class="mt-10 inline-flex size-12 items-center justify-center rounded-lg border border-line bg-surface-2 text-fg-subtle">
            <x-heroicon-o-signal-slash class="size-6" aria-hidden="true" />
        </div>

        <h1 class="mt-6 font-display text-2xl font-title tracking-display text-fg">Anda sedang offline.</h1>
        <p class="mt-3 text-fg-muted">
            Periksa koneksi internet Anda. Halaman akan dimuat ulang otomatis begitu koneksi kembali.
        </p>
        <p class="mt-3 text-sm text-fg-subtle">
            Demi keamanan data pribadi, halaman akun tidak disimpan di perangkat ini.
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <button type="button" data-cf-retry class="btn btn-primary">
                <x-heroicon-o-arrow-path class="size-4" aria-hidden="true" />
                Coba lagi
            </button>
            <a href="{{ url('/') }}" class="btn btn-secondary">
                Ke beranda
            </a>
        </div>
    </main>

    <script{!! \App\Support\Navigation\Appearance::nonceAttribute() !!}>
        (function () {
            var retry = function () { window.location.reload(); };
            document.querySelector('[data-cf-retry]').addEventListener('click', retry);
            window.addEventListener('online', retry);
        })();
    </script>
</body>
</html>
