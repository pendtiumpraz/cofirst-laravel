{{--
    "Pasang aplikasi" banner (only when pwa.enabled and pwa.install_prompt).
    Shown from the second visit on, when the browser offers installation
    (beforeinstallprompt) or on iOS Safari (manual "Tambah ke Layar Utama" steps).
    "Nanti saja" hides it on this device for 30 days.
--}}
@if (setting('pwa.enabled') && setting('pwa.install_prompt'))
    <script{!! \App\Support\Navigation\Appearance::nonceAttribute() !!}>
    document.addEventListener('alpine:init', function () {
        window.Alpine.data('cfInstall', function () {
            return {
                show: false, ios: false,
                init: function () {
                    var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
                    try {
                        if (standalone || Number(localStorage.getItem('cf-install-dismissed') || 0) > Date.now()) { return; }
                        // Reason: count a visit once per browser session, not once per page view.
                        var visits = Number(localStorage.getItem('cf-visits') || 0);
                        if (!sessionStorage.getItem('cf-visit')) { sessionStorage.setItem('cf-visit', '1'); visits += 1; localStorage.setItem('cf-visits', String(visits)); }
                        if (visits < 2) { return; }
                    } catch (e) { return; }
                    var ua = window.navigator.userAgent;
                    this.ios = /iphone|ipad|ipod/i.test(ua) && /safari/i.test(ua) && !/crios|fxios|edgios/i.test(ua);
                    this.show = this.ios || !!window.cfInstallEvent;
                    window.addEventListener('cf-installable', function () { this.show = true; }.bind(this));
                    window.addEventListener('cf-installed', function () { this.show = false; }.bind(this));
                },
                install: function () {
                    var event = window.cfInstallEvent;
                    if (!event) { return; }
                    event.prompt();
                    event.userChoice.then(function (choice) {
                        window.cfInstallEvent = null;
                        if (choice.outcome !== 'accepted') { this.dismiss(); } else { this.show = false; }
                    }.bind(this));
                },
                dismiss: function () {
                    this.show = false;
                    try { localStorage.setItem('cf-install-dismissed', String(Date.now() + 30 * 864e5)); } catch (e) { /* ignore */ }
                },
            };
        });
    });
    </script>
    <div x-data="cfInstall" x-show="show" x-cloak x-transition.opacity.duration.200ms role="region" aria-labelledby="cf-install-title"
         class="fixed inset-x-3 bottom-[calc(4.75rem+env(safe-area-inset-bottom))] z-40 mx-auto max-w-md rounded-xl border border-line-strong bg-surface p-4 shadow-2 lg:inset-x-auto lg:bottom-6 lg:left-auto lg:right-6 lg:w-96">
        <div class="flex items-start gap-3">
            <x-brand.logo type="mark" :size="40" />
            <div class="min-w-0 flex-1">
                <p id="cf-install-title" class="font-semibold text-fg">Pasang {{ setting('pwa.short_name', 'Coding First') }} di perangkat Anda</p>
                <p class="mt-1 text-sm text-fg-muted" x-show="!ios">Buka jadwal, laporan dan pesan lebih cepat langsung dari layar utama.</p>
                <p class="mt-1 text-sm text-fg-muted" x-show="ios">
                    Ketuk ikon <x-heroicon-o-arrow-up-on-square class="inline size-4 align-text-bottom" aria-hidden="true" /><span class="sr-only">Bagikan</span>
                    lalu pilih <strong class="font-semibold text-fg">Tambah ke Layar Utama</strong>.
                </p>
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <button type="button" @click="dismiss()" class="btn btn-ghost btn-sm">
                <span x-text="ios ? 'Mengerti' : 'Nanti saja'">Nanti saja</span>
            </button>
            <button type="button" x-show="!ios" @click="install()" class="btn btn-primary btn-sm">
                <x-heroicon-o-arrow-down-tray class="size-4" aria-hidden="true" />
                Pasang
            </button>
        </div>
    </div>
@endif
