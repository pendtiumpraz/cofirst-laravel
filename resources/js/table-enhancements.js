/**
 * Progressive table enhancements for legacy tables:
 *   <table data-enhance="true" [data-searchable="false"] [data-sortable="false"] [data-show-no="false"]>
 *
 * - search box (client-side filter of the current page; the term is carried
 *   to the server as ?search= when following a pagination link)
 * - click/keyboard sorting per column (skips th[data-sortable="false"],
 *   e.g. headers rendered by <x-ui.th> that already sort server-side)
 * - "No" column that continues the pagination offset
 *
 * Fixes vs. the first version: the No column is inserted before sort indexes
 * are captured (sorting hit the wrong column), IDR amounts ('Rp 1.250.000')
 * parse correctly, empty-state rows are ignored and renumbering keeps the
 * page offset.
 */

const SKIP_HEADERS = ['no', 'aksi', 'actions', 'tindakan'];

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('table[data-enhance="true"]').forEach((table) => enhanceTable(table));
});

function dataRows(table) {
    const tbody = table.tBodies[0];
    if (!tbody) return [];

    // Reason: a single full-width cell (colspan) is an empty-state row, not data.
    return [...tbody.rows].filter((row) => !row.hasAttribute('data-empty') && !(row.cells.length === 1 && row.cells[0].colSpan > 1));
}

function enhanceTable(table) {
    if (table.dataset.enhanced === '1') return;
    table.dataset.enhanced = '1';

    const wrapper = table.closest('.table-wrapper, .table-wrap') || table.parentElement;
    const offset = paginationOffset(wrapper);
    table.dataset.offset = String(offset);

    if (table.dataset.searchable !== 'false') {
        addSearchFunctionality(table, wrapper);
    }

    if (table.dataset.showNo !== 'false') {
        addNumberColumn(table, offset);
    }

    if (table.dataset.sortable !== 'false') {
        addSortingFunctionality(table);
    }

    enhancePagination(wrapper);
}

/** First item number of the current page (from the pagination component). */
function paginationOffset(wrapper) {
    const scope = wrapper?.closest('section, main, body') || document;
    const nav = scope.querySelector('[data-pagination-from]') || document.querySelector('[data-pagination-from]');
    if (nav) {
        const from = parseInt(nav.dataset.paginationFrom, 10);
        return Number.isFinite(from) && from > 0 ? from : 1;
    }

    const legacy = document.querySelector('nav[aria-label="Pagination Navigation"] p.text-sm');
    const match = legacy?.textContent.match(/\b(\d+)\b/);
    return match ? parseInt(match[1], 10) : 1;
}

function addSearchFunctionality(table, wrapper) {
    const container = document.createElement('div');
    container.className = 'mb-4';
    container.innerHTML = `
        <label class="relative block max-w-sm">
            <span class="sr-only">Cari di tabel</span>
            <svg class="pointer-events-none absolute left-3 top-1/2 size-[1.125rem] -translate-y-1/2 text-fg-subtle" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true" data-slot="icon">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <input type="search" class="table-search input pl-10" placeholder="Cari di tabel…" autocomplete="off">
        </label>`;

    wrapper.insertBefore(container, wrapper.firstChild);

    const input = container.querySelector('.table-search');
    const initial = new URLSearchParams(window.location.search).get('search');
    if (initial) input.value = initial;

    const apply = () => {
        const term = input.value.trim().toLowerCase();
        dataRows(table).forEach((row) => {
            row.style.display = !term || row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
        updateRowNumbers(table);
    };

    input.addEventListener('input', apply);
    if (initial) apply();
}

/** Parse a cell for sorting: numbers, 'Rp 1.250.000', '94,1%', else text. */
function sortValue(text) {
    const trimmed = text.trim();
    const numeric = trimmed.replace(/^rp\s*/i, '').replace(/[%\s]/g, '');

    // Indonesian format: '.' thousands, ',' decimals.
    if (/^-?\d{1,3}(\.\d{3})+(,\d+)?$/.test(numeric) || /^-?\d+(,\d+)?$/.test(numeric)) {
        return { number: parseFloat(numeric.replace(/\./g, '').replace(',', '.')), text: trimmed };
    }

    if (/^-?\d+(\.\d+)?$/.test(numeric)) {
        return { number: parseFloat(numeric), text: trimmed };
    }

    return { number: null, text: trimmed };
}

function addSortingFunctionality(table) {
    const headerRow = table.tHead?.rows[0];
    if (!headerRow) return;

    const headers = [...headerRow.cells];

    headers.forEach((header, index) => {
        const label = header.textContent.trim().toLowerCase();
        if (header.dataset.sortable === 'false' || SKIP_HEADERS.includes(label)) return;

        header.style.cursor = 'pointer';
        header.style.userSelect = 'none';
        header.tabIndex = 0;
        header.setAttribute('aria-sort', 'none');

        const indicator = document.createElement('span');
        indicator.className = 'ml-1 inline-block align-middle text-fg-subtle';
        indicator.setAttribute('aria-hidden', 'true');
        indicator.innerHTML =
            '<svg class="inline-block size-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" data-slot="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>';
        header.appendChild(indicator);

        const sort = () => {
            const direction = header.getAttribute('aria-sort') === 'ascending' ? 'descending' : 'ascending';

            headers.forEach((other) => {
                if (other.hasAttribute('aria-sort')) other.setAttribute('aria-sort', 'none');
            });
            header.setAttribute('aria-sort', direction);

            const tbody = table.tBodies[0];
            const rows = dataRows(table);
            const factor = direction === 'ascending' ? 1 : -1;

            rows.sort((a, b) => {
                const left = sortValue(a.cells[index]?.textContent ?? '');
                const right = sortValue(b.cells[index]?.textContent ?? '');

                if (left.number !== null && right.number !== null) {
                    return (left.number - right.number) * factor;
                }

                return left.text.localeCompare(right.text, 'id', { numeric: true, sensitivity: 'base' }) * factor;
            });

            rows.forEach((row) => tbody.appendChild(row));
            updateRowNumbers(table);
        };

        header.addEventListener('click', sort);
        header.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                sort();
            }
        });
    });
}

function addNumberColumn(table, offset) {
    const headerRow = table.tHead?.rows[0];
    if (!headerRow) return;

    const reference = headerRow.cells[0];
    const header = document.createElement('th');
    header.className = reference?.className || '';
    header.classList.add('num');
    header.scope = 'col';
    header.textContent = 'No';
    header.dataset.sortable = 'false';
    headerRow.insertBefore(header, reference || null);

    dataRows(table).forEach((row, index) => {
        const cell = document.createElement('td');
        cell.className = 'num whitespace-nowrap text-fg-subtle';
        cell.dataset.cfNo = '1';
        cell.dataset.label = 'No';
        cell.textContent = String(offset + index);
        row.insertBefore(cell, row.firstChild);
    });

    // Empty-state rows span the extra column too.
    [...(table.tBodies[0]?.rows ?? [])].forEach((row) => {
        if (row.cells.length === 1 && row.cells[0].colSpan > 1) row.cells[0].colSpan += 1;
    });
}

function updateRowNumbers(table) {
    const offset = parseInt(table.dataset.offset || '1', 10);
    let visible = 0;

    dataRows(table).forEach((row) => {
        const cell = row.querySelector('td[data-cf-no]');
        if (!cell || row.style.display === 'none') return;
        cell.textContent = String(offset + visible);
        visible += 1;
    });
}

function enhancePagination(wrapper) {
    const scope = wrapper?.parentElement || document;
    scope.querySelectorAll('.pagination a, .pagination-cf a').forEach((link) => {
        link.addEventListener('click', (event) => {
            const term = wrapper.querySelector('.table-search')?.value;
            if (!term) return;

            event.preventDefault();
            const url = new URL(link.href);
            url.searchParams.set('search', term);
            window.location.href = url.toString();
        });
    });
}

// Public API (kept for scripts that call it directly).
window.TableEnhancements = {
    enhance: enhanceTable,
    addSearch: addSearchFunctionality,
    addSorting: addSortingFunctionality,
    addNumbers: addNumberColumn,
};
