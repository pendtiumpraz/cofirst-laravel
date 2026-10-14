{{--
    Service-worker registration (only when setting('pwa.enabled')). When the PWA is
    switched off, previously installed workers and their caches are removed instead.
    The install event is kept on window.cfInstallEvent for the install banner and the
    "Pasang aplikasi" item in the user menu.
--}}
@php
    $nonce = \App\Support\Navigation\Appearance::nonceAttribute();
    $pwaOn = (bool) setting('pwa.enabled');
    $ownPrompt = $pwaOn && (bool) setting('pwa.install_prompt');
@endphp
<script{!! $nonce !!}>
(function (w, n) {
    if (!('serviceWorker' in n)) { return; }
@if ($pwaOn)
    if (w.isSecureContext) {
        w.addEventListener('load', function () {
            n.serviceWorker.register(@js(asset('sw.js')), { scope: '/' }).catch(function () {});
        });
    }
    w.addEventListener('beforeinstallprompt', function (e) {
        @if ($ownPrompt) e.preventDefault(); @endif
        w.cfInstallEvent = e;
        w.dispatchEvent(new CustomEvent('cf-installable'));
    });
    w.addEventListener('appinstalled', function () { w.cfInstallEvent = null; w.dispatchEvent(new CustomEvent('cf-installed')); });
@else
    n.serviceWorker.getRegistrations().then(function (list) { list.forEach(function (r) { r.unregister(); }); });
    if (w.caches) {
        w.caches.keys().then(function (keys) { keys.forEach(function (k) { if (k.indexOf('cf-') === 0) { w.caches.delete(k); } }); });
    }
@endif
})(window, navigator);
</script>
