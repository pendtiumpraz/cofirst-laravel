{{--
    Alpine components of the dashboard shell (registered on alpine:init, before
    resources/js/app.js starts Alpine) and two global behaviours:
      • logout forms marked data-cf-logout tell the service worker to drop all caches;
      • Ctrl/⌘ K opens the command palette, "[" toggles the desktop sidebar rail.
--}}
<script{!! \App\Support\Navigation\Appearance::nonceAttribute() !!}>
(function (w, d) {
    var FOCUSABLE = 'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';
    var store = {
        get: function (k) { try { return w.localStorage.getItem(k); } catch (e) { return null; } },
        set: function (k, v) { try { w.localStorage.setItem(k, v); } catch (e) { /* storage blocked */ } },
    };
    var visible = function (el) { return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length); };

    // Focus-trapped modal surface (drawer, "Lainnya" sheet, palette base).
    var dialog = function () {
        return {
            open: false, q: '', returnTo: null,
            show: function () {
                this.returnTo = d.activeElement;
                this.open = true;
                d.documentElement.classList.add('overflow-hidden');
                this.$nextTick(function () {
                    var first = this.$refs.initial || this.focusables()[0];
                    if (first) { first.focus(); }
                }.bind(this));
            },
            hide: function () {
                if (!this.open) { return; }
                this.open = false;
                this.q = '';
                d.documentElement.classList.remove('overflow-hidden');
                if (this.returnTo && this.returnTo.focus) { this.returnTo.focus(); }
            },
            focusables: function () {
                return Array.prototype.filter.call(this.$refs.panel.querySelectorAll(FOCUSABLE), visible);
            },
            trap: function (e) {
                var items = this.focusables();
                if (!items.length) { return; }
                var first = items[0], last = items[items.length - 1];
                if (e.shiftKey && d.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && d.activeElement === last) { e.preventDefault(); first.focus(); }
            },
        };
    };

    d.addEventListener('alpine:init', function () {
        var Alpine = w.Alpine;

        Alpine.data('cfDialog', dialog);

        // Disclosure menu (user menu, dropdowns): Esc / outside click close it.
        Alpine.data('cfMenu', function () {
            return {
                open: false,
                toggle: function () { this.open = !this.open; },
                close: function (focusButton) {
                    if (!this.open) { return; }
                    this.open = false;
                    if (focusButton !== false && this.$refs.button) { this.$refs.button.focus(); }
                },
            };
        });

        // Collapsible sidebar group; closed groups are remembered on this device.
        Alpine.data('cfNavGroup', function (key, forceOpen) {
            return {
                open: true,
                init: function () {
                    if (forceOpen) { return; }
                    try { this.open = JSON.parse(store.get('cf-nav-closed') || '[]').indexOf(key) === -1; } catch (e) { this.open = true; }
                },
                toggle: function () {
                    this.open = !this.open;
                    var closed = [];
                    try { closed = JSON.parse(store.get('cf-nav-closed') || '[]'); } catch (e) { closed = []; }
                    closed = closed.filter(function (k) { return k !== key; });
                    if (!this.open) { closed.push(key); }
                    store.set('cf-nav-closed', JSON.stringify(closed));
                },
            };
        });

        Alpine.data('cfSidebarToggle', function () {
            return {
                collapsed: d.documentElement.getAttribute('data-sidebar') === 'collapsed',
                init: function () { w.addEventListener('cf-sidebar-changed', function (e) { this.collapsed = e.detail; }.bind(this)); },
                toggle: function () { w.cfToggleSidebar(); },
            };
        });

        Alpine.data('cfNotice', function (hash) {
            return {
                visible: store.get('cf-notice-dismissed') !== hash,
                dismiss: function () { this.visible = false; store.set('cf-notice-dismissed', hash); },
            };
        });

        Alpine.data('cfPalette', function (items) {
            var base = dialog();
            base.items = items;
            base.active = 0;
            base.results = items;
            base.init = function () {
                this.$watch('q', function (value) {
                    var needle = (value || '').toLowerCase().trim();
                    this.results = !needle ? this.items : this.items.filter(function (item) {
                        return (item.label + ' ' + item.section).toLowerCase().indexOf(needle) !== -1;
                    });
                    this.active = 0;
                }.bind(this));
            };
            base.move = function (step) {
                if (!this.results.length) { return; }
                this.active = (this.active + step + this.results.length) % this.results.length;
                var el = d.getElementById('cf-palette-opt-' + this.active);
                if (el) { el.scrollIntoView({ block: 'nearest' }); }
            };
            base.go = function () {
                var item = this.results[this.active];
                if (item) { w.location.assign(item.url); }
            };
            return base;
        });
    });

    w.cfToggleSidebar = function () {
        var h = d.documentElement, collapsed = h.getAttribute('data-sidebar') !== 'collapsed';
        if (collapsed) { h.setAttribute('data-sidebar', 'collapsed'); } else { h.removeAttribute('data-sidebar'); }
        store.set('cf-sidebar', collapsed ? 'collapsed' : 'expanded');
        w.dispatchEvent(new CustomEvent('cf-sidebar-changed', { detail: collapsed }));
    };

    d.addEventListener('keydown', function (e) {
        var typing = /^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName) || e.target.isContentEditable;
        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            w.dispatchEvent(new CustomEvent('cf-palette-open'));
        } else if (e.key === '[' && !typing && !e.ctrlKey && !e.metaKey && !e.altKey && d.querySelector('.cf-sidebar')) {
            w.cfToggleSidebar();
        }
    });

    // Logout: personal data must not survive on shared devices.
    d.addEventListener('submit', function (e) {
        if (!e.target.matches || !e.target.matches('[data-cf-logout]')) { return; }
        try {
            if (navigator.serviceWorker && navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage('CF_LOGOUT');
            }
            ['cf-nav-closed', 'cf-notice-dismissed', 'cf-install-dismissed', 'cf-visits'].forEach(function (k) { w.localStorage.removeItem(k); });
        } catch (err) { /* never block logout */ }
    }, true);
})(window, document);
</script>
