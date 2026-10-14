/**
 * Colour-mode controller (light | dark | system).
 *
 * Precedence (same as the inline <x-ui.color-mode-script/> in <head>, which
 * applies the class before first paint so there is no flash):
 *   1. <html data-allow-color-mode="false">  → always the site default
 *   2. <html data-user-color-mode="…">       → saved preference of the signed-in user
 *   3. localStorage 'cf-color-mode'          → explicit choice of a guest (written on user action only)
 *   4. <html data-default-color-mode="…">    → setting appearance.color_mode (fallback 'system')
 *
 * Exposed as Alpine.store('colorMode') and window.CF.colorMode.
 * Fires `cf:theme` on window after every change (charts re-render on it) and
 * `cf:color-mode-change` when the user picks a mode. When the page declares
 * <html data-color-mode-endpoint="/url">, the choice is POSTed there as
 * { color_mode } so the server can persist it in users.preferences.
 */

export const STORAGE_KEY = 'cf-color-mode';
export const MODES = ['light', 'dark', 'system'];

const root = document.documentElement;
const media = typeof window.matchMedia === 'function' ? window.matchMedia('(prefers-color-scheme: dark)') : null;

function readStored() {
    try {
        return window.localStorage.getItem(STORAGE_KEY);
    } catch {
        return null;
    }
}

function writeStored(mode) {
    try {
        window.localStorage.setItem(STORAGE_KEY, mode);
    } catch {
        // Private mode / blocked storage: the choice simply is not remembered.
    }
}

function isAllowed() {
    return root.dataset.allowColorMode !== 'false';
}

function defaultMode() {
    const value = root.dataset.defaultColorMode;
    return MODES.includes(value) ? value : 'system';
}

/** Resolve the mode to use right now (see precedence above). */
export function initialMode() {
    if (!isAllowed()) {
        return defaultMode();
    }

    const user = root.dataset.userColorMode;
    if (MODES.includes(user)) {
        return user;
    }

    const stored = readStored();
    if (MODES.includes(stored)) {
        return stored;
    }

    return defaultMode();
}

function systemPrefersDark() {
    return media ? media.matches : false;
}

function canvasColor() {
    const triplet = getComputedStyle(root).getPropertyValue('--cf-canvas').trim();
    return triplet ? `rgb(${triplet})` : null;
}

/** Keep <meta name="theme-color"> in step with a forced light/dark choice. */
function syncThemeColor(mode) {
    document.querySelectorAll('meta[name="theme-color"]').forEach((meta) => {
        if (!meta.dataset.cfOriginal) {
            meta.dataset.cfOriginal = meta.getAttribute('content') || '';
            meta.dataset.cfMedia = meta.getAttribute('media') || '';
        }

        if (mode === 'system') {
            meta.setAttribute('content', meta.dataset.cfOriginal);
            if (meta.dataset.cfMedia) {
                meta.setAttribute('media', meta.dataset.cfMedia);
            }
            return;
        }

        const color = canvasColor();
        if (color) {
            meta.removeAttribute('media');
            meta.setAttribute('content', color);
        }
    });
}

/**
 * Apply a mode to <html>.
 *
 * @param {string} mode light|dark|system
 * @param {{animate?: boolean}} options cross-fade colours (not on first paint)
 * @returns {'light'|'dark'} the resolved mode
 */
export function applyMode(mode, { animate = false } = {}) {
    const resolved = mode === 'dark' || (mode === 'system' && systemPrefersDark()) ? 'dark' : 'light';

    if (animate) {
        root.classList.add('cf-theme-switching');
        window.setTimeout(() => root.classList.remove('cf-theme-switching'), 260);
    }

    root.classList.toggle('dark', resolved === 'dark');
    root.dataset.colorMode = mode;
    syncThemeColor(mode);

    window.dispatchEvent(
        new CustomEvent('cf:theme', {
            detail: { mode, resolved, theme: root.dataset.theme || 'a' },
        }),
    );

    return resolved;
}

function persistRemotely(mode) {
    const endpoint = root.dataset.colorModeEndpoint;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!endpoint || !window.fetch) {
        return;
    }

    window
        .fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(token ? { 'X-CSRF-TOKEN': token } : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ color_mode: mode }),
        })
        .catch(() => {
            // Offline or endpoint missing: the local choice still applies.
        });
}

/**
 * Build the Alpine store object.
 *
 * @returns {object} store with mode, resolved, allowed, set(), toggle(), cycle()
 */
export function createColorModeStore() {
    const mode = initialMode();

    return {
        mode,
        resolved: root.classList.contains('dark') ? 'dark' : 'light',
        allowed: isAllowed(),
        labels: { light: 'Terang', dark: 'Gelap', system: 'Ikuti sistem' },

        init() {
            this.resolved = applyMode(this.mode);

            const onSystemChange = () => {
                if (this.mode === 'system') {
                    this.resolved = applyMode('system', { animate: true });
                }
            };

            if (media?.addEventListener) {
                media.addEventListener('change', onSystemChange);
            } else if (media?.addListener) {
                media.addListener(onSystemChange);
            }

            // Keep several open tabs in sync (guests).
            window.addEventListener('storage', (event) => {
                if (event.key === STORAGE_KEY && MODES.includes(event.newValue) && !root.dataset.userColorMode) {
                    this.mode = event.newValue;
                    this.resolved = applyMode(this.mode, { animate: true });
                }
            });
        },

        get isDark() {
            return this.resolved === 'dark';
        },

        /** Pick a mode (user action): stores it locally and remotely. */
        set(next) {
            if (!MODES.includes(next) || !this.allowed) {
                return;
            }

            this.mode = next;
            writeStored(next);

            if (root.dataset.userColorMode) {
                root.dataset.userColorMode = next;
            }

            this.resolved = applyMode(next, { animate: true });
            window.dispatchEvent(new CustomEvent('cf:color-mode-change', { detail: { mode: next } }));
            persistRemotely(next);
        },

        /** Flip between light and dark (from whatever is shown now). */
        toggle() {
            this.set(this.resolved === 'dark' ? 'light' : 'dark');
        },

        /** light → dark → system → light. */
        cycle() {
            const index = MODES.indexOf(this.mode);
            this.set(MODES[(index + 1) % MODES.length]);
        },
    };
}
