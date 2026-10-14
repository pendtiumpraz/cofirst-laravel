/**
 * Coding First — application entry.
 *
 * Alpine 3 + plugins (focus → x-trap, collapse → x-collapse), the colour-mode
 * controller, toast store, confirm dialog, lazy Chart.js helper, the Alpine
 * behaviours behind <x-ui.*> components and the legacy table enhancements.
 * Small helpers are exposed on window.CF for inline scripts.
 */

import './bootstrap';
import './table-enhancements';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';

import { createColorModeStore } from './color-mode';
import { createToastStore, confirmDialog, installDeclarativeConfirm } from './feedback';
import charts from './charts';
import format from './format';
import registerComponents from './components';

Alpine.plugin(focus);
Alpine.plugin(collapse);

Alpine.store('colorMode', createColorModeStore());
Alpine.store('toast', createToastStore());
registerComponents(Alpine);

installDeclarativeConfirm();
charts.install();

/** '/' focuses the page search ([data-search-shortcut]) unless typing. */
document.addEventListener('keydown', (event) => {
    if (event.key !== '/' || event.ctrlKey || event.metaKey || event.altKey) return;

    const target = event.target;
    const typing = target instanceof HTMLElement && (target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName));
    if (typing) return;

    const search = document.querySelector('[data-search-shortcut]');
    if (search) {
        event.preventDefault();
        search.focus();
        search.select?.();
    }
});

window.Alpine = Alpine;

window.CF = {
    /** CF.toast.success('Tersimpan.') · CF.toast.push({ tone, title, message, timeout }) */
    get toast() {
        return Alpine.store('toast');
    },
    /** CF.colorMode.set('dark' | 'light' | 'system') · .toggle() · .cycle() · .mode · .resolved */
    get colorMode() {
        return Alpine.store('colorMode');
    },
    /** await CF.confirm({ title, message, confirmText, cancelText, tone }) → boolean */
    confirm: confirmDialog,
    /** CF.charts.init(rootElement) after injecting markup · CF.charts.refresh() */
    charts,
    /** CF.format.idr(1250000) → 'Rp 1.250.000' (see resources/js/format.js) */
    format,
};

Alpine.start();
