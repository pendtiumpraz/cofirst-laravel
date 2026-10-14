/**
 * Indonesian (id-ID) number, money, percent and date formatting.
 *
 *   format.number(1250000)        → '1.250.000'
 *   format.idr(1250000)           → 'Rp 1.250.000'
 *   format.idrCompact(12400000)   → 'Rp 12,4 jt'
 *   format.percent(94.12)         → '94,1%'
 *   format.date('2026-10-13')     → '13 Okt 2026'
 */

const LOCALE = 'id-ID';

function toNumber(value) {
    const number = typeof value === 'number' ? value : Number(value);
    return Number.isFinite(number) ? number : 0;
}

export function number(value, options = {}) {
    return new Intl.NumberFormat(LOCALE, { maximumFractionDigits: 2, ...options }).format(toNumber(value));
}

export function idr(value) {
    return `Rp ${new Intl.NumberFormat(LOCALE, { maximumFractionDigits: 0 }).format(Math.round(toNumber(value)))}`;
}

export function idrCompact(value) {
    const amount = toNumber(value);
    const abs = Math.abs(amount);
    const short = (n) => new Intl.NumberFormat(LOCALE, { maximumFractionDigits: 1 }).format(n);

    if (abs >= 1e12) return `Rp ${short(amount / 1e12)} T`;
    if (abs >= 1e9) return `Rp ${short(amount / 1e9)} M`;
    if (abs >= 1e6) return `Rp ${short(amount / 1e6)} jt`;
    if (abs >= 1e3) return `Rp ${short(amount / 1e3)} rb`;

    return idr(amount);
}

export function percent(value, digits = 1) {
    return `${new Intl.NumberFormat(LOCALE, { maximumFractionDigits: digits }).format(toNumber(value))}%`;
}

export function date(value, options = {}) {
    const parsed = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(parsed.getTime())) return '';

    return new Intl.DateTimeFormat(LOCALE, { day: 'numeric', month: 'short', year: 'numeric', ...options }).format(parsed);
}

/**
 * Format by name, used by charts ('number' | 'idr' | 'idr-compact' | 'percent').
 */
export function byName(name, value) {
    switch (name) {
        case 'idr':
            return idr(value);
        case 'idr-compact':
            return idrCompact(value);
        case 'percent':
            return percent(value);
        default:
            return number(value);
    }
}

export default { number, idr, idrCompact, percent, date, byName };
