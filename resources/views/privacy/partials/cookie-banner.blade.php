{{--
    Cookie consent banner + window.CFConsent (UU PDP Pasal 20 ayat (2) huruf a, 22; PP 33/2026 Pasal 32-35).

    Included by the public and guest layouts (@includeIf) and pushed onto the dashboard layout by
    EnsurePolicyAccepted. @once keeps it single when several includes meet.

    JS API for other modules (load optional scripts only after consent):
        CFConsent.has('analytics' | 'functional' | 'essential') → bool
        CFConsent.get() → {v, functional, analytics, ts} | null;  CFConsent.decided() → bool
        CFConsent.onChange(fn)  ·  window event 'cf-consent-changed' (detail = state)
        CFConsent.acceptAll() · CFConsent.rejectOptional() · CFConsent.save({functional, analytics}) · CFConsent.open()
        <script type="text/plain" data-cf-consent="analytics" data-src="https://…"></script>  runs once granted.
    Equal-weight choices: Tolak opsional · Atur · Terima semua. It never blocks reading.
--}}
@once
@php
    // Reason: no `use` statements here — @once compiles to an if-block, where PHP forbids them.
    $cookieConsent = app(\App\Services\Privacy\CookieConsentService::class)->state(request(), auth()->user());
    $bannerOn = (bool) setting('privacy.cookie_banner_enabled', true);
    $policyUrl = Route::has('privacy.cookies') ? route('privacy.cookies') : null;
    $prefsUrl = Route::has('privacy.cookies.preferences') ? route('privacy.cookies.preferences') : null;
    $endpoint = auth()->check() && Route::has('privacy.cookies.update') ? route('privacy.cookies.update') : null;
    $measurementId = trim((string) setting('integrations.analytics_measurement_id', ''));
    $nonce = \App\Support\Navigation\Appearance::nonceAttribute();
    $lifetime = \App\Services\Privacy\CookieConsentService::LIFETIME_DAYS * 86400;
    $bannerConfig = [
        'version' => $cookieConsent['version'],
        'minor' => $cookieConsent['minor'],
        'server' => $cookieConsent['decided'] ? $cookieConsent['choices'] : null,
        'endpoint' => $endpoint,
        'prefs' => $prefsUrl,
        'banner' => $bannerOn,
    ];
@endphp
<script{!! $nonce !!}>
(function () {
    var NAME = 'cf_consent', MAX_AGE = {{ $lifetime }};
    var cfg = @json($bannerConfig);
    var listeners = [];

    function read() {
        var m = document.cookie.match(/(?:^|;\s*)cf_consent=([^;]*)/);
        if (!m) return null;
        try { var v = JSON.parse(decodeURIComponent(m[1])); return v && v.v === cfg.version ? v : null; } catch (e) { return null; }
    }

    var state = read() || (cfg.server ? { v: cfg.version, functional: !!cfg.server.functional, analytics: !!cfg.server.analytics } : null);

    function has(category) {
        if (category === 'essential') return true;
        if (category === 'analytics' && cfg.minor) return false;
        return !!(state && state[category]);
    }

    // Reason: optional scripts are inert text/plain until their category is granted.
    function activate() {
        document.querySelectorAll('script[type="text/plain"][data-cf-consent]').forEach(function (el) {
            if (el.getAttribute('data-cf-activated') || !has(el.getAttribute('data-cf-consent'))) return;
            el.setAttribute('data-cf-activated', '1');
            var s = document.createElement('script');
            if (el.getAttribute('data-src')) { s.src = el.getAttribute('data-src'); s.async = true; } else { s.text = el.text; }
            if (el.nonce) s.nonce = el.nonce;
            el.parentNode.insertBefore(s, el.nextSibling);
        });
    }

    function clearAnalyticsCookies() {
        document.cookie.split(';').forEach(function (c) {
            var name = c.split('=')[0].trim();
            if (/^_ga/.test(name)) {
                var host = location.hostname.split('.');
                for (var i = 0; i < host.length - 1; i++) {
                    document.cookie = name + '=; Max-Age=0; Path=/; Domain=.' + host.slice(i).join('.');
                }
                document.cookie = name + '=; Max-Age=0; Path=/';
            }
        });
    }

    function save(choices) {
        var hadAnalytics = has('analytics');
        state = { v: cfg.version, functional: !!choices.functional, analytics: !cfg.minor && !!choices.analytics, ts: Date.now() };
        document.cookie = NAME + '=' + encodeURIComponent(JSON.stringify(state)) + '; Max-Age=' + MAX_AGE + '; Path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');

        if (cfg.endpoint) {
            var token = document.querySelector('meta[name="csrf-token"]');
            fetch(cfg.endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token ? token.content : '' },
                body: JSON.stringify({ action: 'custom', functional: state.functional, analytics: state.analytics, source: 'banner' })
            }).catch(function () {});
        }

        listeners.forEach(function (fn) { try { fn(Object.assign({}, state)); } catch (e) {} });
        window.dispatchEvent(new CustomEvent('cf-consent-changed', { detail: Object.assign({}, state) }));

        if (hadAnalytics && !state.analytics) {
            clearAnalyticsCookies();
            // Reason: a loaded analytics script cannot be unloaded; reload so it stops.
            if (document.querySelector('script[data-cf-consent="analytics"][data-cf-activated]')) { location.reload(); return; }
        }
        activate();
    }

    window.CFConsent = {
        has: has,
        get: function () { return state ? Object.assign({}, state) : null; },
        decided: function () { return !!state; },
        save: save,
        acceptAll: function () { save({ functional: true, analytics: true }); },
        rejectOptional: function () { save({ functional: false, analytics: false }); },
        onChange: function (fn) { listeners.push(fn); },
        open: function () { window.dispatchEvent(new CustomEvent('cf-cookie-settings')); },
        minor: cfg.minor
    };

    if (!cfg.banner && cfg.prefs) {
        window.addEventListener('cf-cookie-settings', function () { window.location.href = cfg.prefs; });
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', activate); } else { activate(); }

    document.addEventListener('alpine:init', function () {
        window.Alpine.data('cfCookieBanner', function () {
            return {
                open: !state,
                prefs: false,
                functional: has('functional'),
                analytics: has('analytics'),
                openPrefs: function () {
                    this.functional = has('functional');
                    this.analytics = has('analytics');
                    this.prefs = true;
                    this.open = true;
                    this.$nextTick(function () { if (this.$refs.first) this.$refs.first.focus(); }.bind(this));
                },
                choose: function (kind) {
                    if (kind === 'all') window.CFConsent.acceptAll();
                    else if (kind === 'none') window.CFConsent.rejectOptional();
                    else window.CFConsent.save({ functional: this.functional, analytics: this.analytics });
                    this.open = false;
                    this.prefs = false;
                    if (window.CF && window.CF.toast) window.CF.toast.success('Preferensi cookie Anda tersimpan.');
                }
            };
        });
    });
})();
</script>

@if ($bannerOn)
    <section x-data="cfCookieBanner" x-show="open" x-cloak
        x-on:cf-cookie-settings.window="openPrefs()"
        x-on:keydown.escape="if (window.CFConsent.decided()) { open = false; prefs = false }"
        x-transition:enter="transition duration-base ease-cf motion-reduce:transition-none"
        x-transition:enter-start="translate-y-4 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        role="region" aria-labelledby="cf-cookie-title"
        class="fixed inset-x-0 bottom-0 z-[60] px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:inset-x-auto sm:bottom-4 sm:left-4 sm:px-0 sm:pb-0 print:hidden">
        <div class="max-h-[85dvh] w-full overflow-y-auto rounded-xl border border-line-strong bg-surface shadow-2 sm:w-[26rem] sm:rounded-lg">
            <div class="p-5">
                <div class="flex items-start gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-md bg-brand-soft text-brand-soft-fg" aria-hidden="true">
                        <x-ui.icon name="shield-check" class="size-5" />
                    </span>
                    <div class="min-w-0">
                        <h2 id="cf-cookie-title" class="font-display text-[1.0625rem] font-title leading-snug text-fg">Privasi Anda penting bagi kami</h2>
                        <p class="mt-1.5 text-[0.8125rem] leading-relaxed text-fg-muted">
                            {{ setting('privacy.cookie_banner_text') }}
                            @if ($policyUrl)
                                <a href="{{ $policyUrl }}" class="link font-medium">Kebijakan Cookie</a>
                            @endif
                        </p>
                    </div>
                </div>

                <div x-show="prefs" x-cloak class="mt-4 space-y-1 border-t border-line pt-3" role="group" aria-label="Kategori cookie">
                    <div class="flex items-start justify-between gap-4 py-2">
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-fg">Esensial</span>
                            <span class="mt-0.5 block text-[0.8125rem] text-fg-subtle">Sesi masuk dan keamanan formulir. Selalu aktif.</span>
                        </span>
                        <x-ui.badge class="mt-0.5 shrink-0">Selalu aktif</x-ui.badge>
                    </div>
                    <x-ui.toggle id="cf-cookie-functional" label="Fungsional" description="Konten pihak ketiga yang Anda minta, misalnya peta lokasi." x-model="functional" x-ref="first" />
                    @if ($cookieConsent['minor'])
                        <p class="py-2 text-[0.8125rem] text-fg-subtle">Analitik tidak pernah aktif untuk akun anak.</p>
                    @else
                        <x-ui.toggle id="cf-cookie-analytics" label="Analitik" description="Statistik kunjungan agregat untuk memperbaiki situs." x-model="analytics" />
                    @endif
                </div>

                <div class="mt-5 grid grid-cols-1 gap-2 min-[400px]:grid-cols-3">
                    <x-ui.button variant="secondary" size="sm" x-on:click="choose('none')">Tolak opsional</x-ui.button>
                    <x-ui.button variant="secondary" size="sm" x-show="! prefs" x-on:click="openPrefs()">Atur</x-ui.button>
                    <x-ui.button variant="secondary" size="sm" x-show="prefs" x-cloak x-on:click="choose('custom')">Simpan pilihan</x-ui.button>
                    <x-ui.button variant="secondary" size="sm" x-on:click="choose('all')">Terima semua</x-ui.button>
                </div>
            </div>
        </div>
    </section>
@endif

@if ($measurementId !== '' && preg_match('/^G-[A-Z0-9]{4,20}$/', $measurementId) && ! $cookieConsent['minor'])
    {{-- Loaded only after CFConsent grants 'analytics' (integrations.analytics_measurement_id). --}}
    <script type="text/plain" data-cf-consent="analytics" data-src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"{!! $nonce !!}></script>
    <script type="text/plain" data-cf-consent="analytics"{!! $nonce !!}>window.dataLayer = window.dataLayer || []; function gtag(){ dataLayer.push(arguments); } gtag('js', new Date()); gtag('config', @json($measurementId), { anonymize_ip: true });</script>
@endif
@endonce
