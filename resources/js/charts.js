/**
 * Theme-aware Chart.js helper for <x-ui.chart>.
 *
 * - chart.js is imported from npm on demand (separate chunk, only on pages
 *   that contain a chart) and only the controllers we use are registered.
 * - Charts initialise lazily when they scroll into view.
 * - Colours, fonts and radii are read from the --cf-* CSS variables of the
 *   chart's own element, so charts follow theme A/B and dark mode. They are
 *   rebuilt on `cf:theme` and whenever <html> class/data-theme changes.
 *
 * Markup contract (rendered by <x-ui.chart>):
 *   <canvas data-cf-chart role="img" aria-label="…"></canvas>
 *   <script type="application/json" data-cf-chart-config>{…}</script>
 * Config: { type, data: { labels, datasets: [{ label, data, tone?, color?, fill? }] },
 *           options?, format?: 'number'|'idr'|'idr-compact'|'percent',
 *           stacked?, horizontal?, legend?, highlightLast? }
 */

import { byName } from './format';

const instances = new Map();
let chartPromise = null;
let observer = null;

function loadChartJs() {
    if (!chartPromise) {
        // Reason: destructuring in the parameter lets Rollup tree-shake chart.js.
        chartPromise = import('chart.js').then(
            ({
                Chart,
                LineController,
                BarController,
                DoughnutController,
                PieController,
                LineElement,
                BarElement,
                PointElement,
                ArcElement,
                CategoryScale,
                LinearScale,
                Tooltip,
                Legend,
                Filler,
            }) => {
                Chart.register(
                    LineController,
                    BarController,
                    DoughnutController,
                    PieController,
                    LineElement,
                    BarElement,
                    PointElement,
                    ArcElement,
                    CategoryScale,
                    LinearScale,
                    Tooltip,
                    Legend,
                    Filler,
                );

                return Chart;
            },
        );
    }

    return chartPromise;
}

function reducedMotion() {
    return (
        document.documentElement.classList.contains('reduce-motion') ||
        (typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches)
    );
}

/** Read theme tokens from the element (nested [data-theme] previews work). */
function tokenReader(element) {
    const style = getComputedStyle(element);

    const raw = (name) => style.getPropertyValue(`--cf-${name}`).trim();
    // Reason: Chart.js' colour helper only parses the comma syntax, so the
    // "r g b" triplets are converted to rgb(r, g, b) / rgba(r, g, b, a).
    const color = (name, alpha = 1) => {
        const value = raw(name);
        if (!value) return undefined;
        const channels = value.split(/[\s,]+/).filter(Boolean).join(', ');
        return alpha < 1 ? `rgba(${channels}, ${alpha})` : `rgb(${channels})`;
    };

    return { raw, color };
}

/** rgb(r, g, b) → rgba(r, g, b, alpha); other colour strings are returned as-is. */
function withAlpha(value, alpha) {
    const match = /^rgb\(([^)]+)\)$/.exec(value || '');
    return match ? `rgba(${match[1]}, ${alpha})` : value;
}

function isPlainObject(value) {
    return value !== null && typeof value === 'object' && !Array.isArray(value);
}

function deepMerge(target, source) {
    if (!isPlainObject(source)) return target;

    Object.keys(source).forEach((key) => {
        if (isPlainObject(source[key]) && isPlainObject(target[key])) {
            target[key] = deepMerge({ ...target[key] }, source[key]);
        } else {
            target[key] = source[key];
        }
    });

    return target;
}

function readConfig(canvas) {
    const holder = canvas.parentElement?.querySelector('script[data-cf-chart-config]');

    try {
        return JSON.parse(holder ? holder.textContent : canvas.dataset.cfChart || '{}');
    } catch {
        return null;
    }
}

/** Translate our compact JSON config into a themed Chart.js config. */
function buildChartConfig(canvas, config) {
    const { raw, color } = tokenReader(canvas);
    const theme = canvas.closest('[data-theme]')?.dataset.theme || 'a';
    const type = config.type || 'line';
    const circular = type === 'doughnut' || type === 'pie';
    const format = config.format || 'number';
    const palette = [1, 2, 3, 4, 5, 6].map((n) => color(`chart-${n}`));
    const surface = color('surface');
    const fontSans = raw('font-sans') || 'system-ui, sans-serif';
    const fontMono = raw('font-mono') || 'monospace';
    const tickFont = { family: theme === 'b' ? fontMono : fontSans, size: theme === 'b' ? 11 : 12 };
    const radius = parseFloat(raw('radius-sm')) || 6;
    const labels = config.data?.labels || [];

    const toneColor = (dataset, index) => {
        if (dataset.color) return dataset.color;
        if (dataset.tone) return color(dataset.tone === 'primary' ? 'brand' : dataset.tone) || palette[index % 6];
        return palette[index % palette.length];
    };

    const datasets = (config.data?.datasets || []).map((dataset, index) => {
        const { tone, color: _ignored, ...rest } = dataset;
        const base = toneColor(dataset, index);

        if (circular) {
            return {
                backgroundColor: (rest.data || []).map((_, i) => palette[i % palette.length]),
                borderColor: surface,
                borderWidth: 2,
                hoverOffset: 4,
                ...rest,
            };
        }

        if (type === 'bar') {
            const lastIndex = labels.length - 1;
            const highlight = config.highlightLast && index === 0 ? color('chart-2') : null;

            return {
                backgroundColor: highlight ? labels.map((_, i) => (i === lastIndex ? highlight : base)) : base,
                borderRadius: theme === 'b' ? 3 : radius,
                borderSkipped: 'start',
                maxBarThickness: 28,
                ...rest,
            };
        }

        const fill = rest.fill ?? index === 0;
        return {
            borderColor: base,
            backgroundColor: fill ? withAlpha(base, 0.08) : base,
            fill,
            borderWidth: 2,
            tension: theme === 'b' ? 0.3 : 0.25,
            pointRadius: 0,
            pointHoverRadius: 4,
            pointBackgroundColor: base,
            ...rest,
        };
    });

    const formatter = (value) => byName(format, value);
    const gridColor = color('border');

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        animation: reducedMotion() || canvas.dataset.cfRendered ? false : { duration: theme === 'b' ? 300 : 400 },
        interaction: { mode: circular ? 'nearest' : 'index', intersect: circular },
        indexAxis: config.horizontal ? 'y' : 'x',
        layout: { padding: 4 },
        plugins: {
            legend: {
                display: config.legend ?? (circular || datasets.length > 1),
                position: 'top',
                align: 'start',
                labels: {
                    color: color('fg-muted'),
                    font: { family: fontSans, size: 12 },
                    boxWidth: 10,
                    boxHeight: 10,
                    padding: 16,
                },
            },
            tooltip: {
                backgroundColor: surface,
                titleColor: color('fg'),
                bodyColor: color('fg-muted'),
                borderColor: color('border-strong'),
                borderWidth: 1,
                padding: 10,
                cornerRadius: parseFloat(raw('radius-md')) || 8,
                titleFont: { family: fontSans, weight: '600' },
                bodyFont: { family: theme === 'b' ? fontMono : fontSans },
                boxPadding: 4,
                callbacks: {
                    label(context) {
                        const value = circular ? context.parsed : context.parsed[config.horizontal ? 'x' : 'y'];
                        const prefix = context.dataset.label || context.label || '';
                        return `${prefix ? `${prefix}: ` : ''}${formatter(value)}`;
                    },
                },
            },
        },
    };

    if (circular) {
        options.cutout = type === 'doughnut' ? '72%' : 0;
    } else {
        const valueAxis = {
            beginAtZero: true,
            stacked: !!config.stacked,
            border: { display: false, dash: theme === 'b' ? [3, 3] : undefined },
            grid: { color: gridColor, drawTicks: false, ...(theme === 'b' ? { tickBorderDash: [3, 3] } : {}) },
            ticks: { color: color('fg-subtle'), font: tickFont, padding: 8, callback: formatter, maxTicksLimit: 6 },
        };
        const categoryAxis = {
            stacked: !!config.stacked,
            border: { color: color('border-strong') },
            grid: { display: false },
            ticks: { color: color('fg-subtle'), font: tickFont, padding: 6, maxRotation: 0, autoSkipPadding: 12 },
        };

        options.scales = config.horizontal ? { x: valueAxis, y: categoryAxis } : { x: categoryAxis, y: valueAxis };
    }

    return {
        type,
        data: { labels, datasets },
        options: deepMerge(options, config.options || {}),
    };
}

async function render(canvas) {
    const config = readConfig(canvas);
    if (!config) return;

    const Chart = await loadChartJs();
    instances.get(canvas)?.destroy();

    const chart = new Chart(canvas, buildChartConfig(canvas, config));
    instances.set(canvas, chart);
    canvas.dataset.cfRendered = '1';
}

function watch(canvas) {
    if (canvas.dataset.cfWatched) return;
    canvas.dataset.cfWatched = '1';

    if (!('IntersectionObserver' in window)) {
        render(canvas);
        return;
    }

    observer ??= new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    observer.unobserve(entry.target);
                    render(entry.target);
                }
            });
        },
        { rootMargin: '200px 0px' },
    );

    observer.observe(canvas);
}

/** Find and lazily initialise every chart under `root`. */
export function initCharts(root = document) {
    root.querySelectorAll('canvas[data-cf-chart]').forEach(watch);
}

/** Re-render already drawn charts with the current theme tokens. */
export function refreshCharts() {
    instances.forEach((chart, canvas) => {
        if (!canvas.isConnected) {
            chart.destroy();
            instances.delete(canvas);
            return;
        }
        render(canvas);
    });
}

let refreshQueued = false;
function queueRefresh() {
    if (refreshQueued || instances.size === 0) return;
    refreshQueued = true;
    requestAnimationFrame(() => {
        refreshQueued = false;
        refreshCharts();
    });
}

/** Boot: scan the page, follow theme changes. */
export function installCharts() {
    const boot = () => initCharts();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }

    window.addEventListener('cf:theme', queueRefresh);

    new MutationObserver(queueRefresh).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class', 'data-theme'],
    });
}

export default { init: initCharts, refresh: refreshCharts, install: installCharts };
