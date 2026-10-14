{{--
    No-flash colour mode. Put it in <head> BEFORE @vite, after the <html> tag carries:
      data-default-color-mode="{{ setting('appearance.color_mode') }}"       (light|dark|system)
      data-allow-color-mode="{{ setting('appearance.allow_user_color_mode') ? 'true' : 'false' }}"
      data-user-color-mode="…"   (only for a signed-in user with a saved preference)
    The server may already render class="dark" for an explicit 'dark' preference; this script
    resolves 'system' and a guest's localStorage choice before first paint (try/catch guarded).
    Same precedence as resources/js/color-mode.js. Uses the Vite CSP nonce when one is set.
--}}
@php
    $nonce = \Illuminate\Support\Facades\Vite::cspNonce();
@endphp
<script @if ($nonce) nonce="{{ $nonce }}" @endif>
(function () {
    try {
        var d = document.documentElement, modes = ['light', 'dark', 'system'], m = null;
        var fallback = modes.indexOf(d.getAttribute('data-default-color-mode')) > -1 ? d.getAttribute('data-default-color-mode') : 'system';
        if (d.getAttribute('data-allow-color-mode') !== 'false') {
            m = d.getAttribute('data-user-color-mode');
            if (modes.indexOf(m) < 0) {
                try { m = window.localStorage.getItem('cf-color-mode'); } catch (e) { m = null; }
            }
        }
        if (modes.indexOf(m) < 0) { m = fallback; }
        var dark = m === 'dark' || (m === 'system' && !!window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
        d.classList.toggle('dark', dark);
        d.setAttribute('data-color-mode', m);
    } catch (e) {}
})();
</script>
