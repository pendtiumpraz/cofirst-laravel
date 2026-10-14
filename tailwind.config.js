import fs from 'node:fs';
import forms from '@tailwindcss/forms';

/*
|--------------------------------------------------------------------------
| Coding First design system — Tailwind v3 mapping of the --cf-* token contract
|--------------------------------------------------------------------------
|
| Colours are CSS variables holding RGB triplets, defined per theme in
| resources/css/themes/{a,b}.css under [data-theme] and .dark. Markup uses
| ONLY the semantic utilities below (bg-surface, text-fg-muted, bg-brand …)
| and never `dark:` colour variants — the tokens switch automatically.
*/

/** rgb(var(--cf-name) / <alpha-value>) so opacity modifiers keep working. */
const v = (name) => `rgb(var(--cf-${name}) / <alpha-value>)`;

const STEPS = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

/** A full 50..950 ramp backed by --cf-{name}-{step}. */
const ramp = (name) => Object.fromEntries(STEPS.map((step) => [step, v(`${name}-${step}`)]));

const status = (name) => ({
    DEFAULT: v(name),
    soft: v(`${name}-soft`),
    solid: v(`${name}-solid`),
});

/**
 * Every class defined by the design-system CSS (component + utility layers).
 * Reason: area engineers adopt these classes view by view; safelisting them
 * keeps the bundle stable instead of depending on which views exist at build time.
 */
const designSystemClasses = (() => {
    const files = [
        ...fs
            .readdirSync(new URL('./resources/css/components/', import.meta.url))
            .filter((file) => file.endsWith('.css'))
            .map((file) => new URL(`./resources/css/components/${file}`, import.meta.url)),
        new URL('./resources/css/utilities.css', import.meta.url),
    ];

    const names = new Set();
    files.forEach((file) => {
        const css = fs
            .readFileSync(file, 'utf8')
            .split(/\r?\n/)
            .filter((line) => !/^\s*(@apply|\/\*|\*)/.test(line))
            .join(' ');
        for (const match of css.matchAll(/\.([a-z][a-z0-9-]*)(?=[\s,:.{[>+~)])/g)) {
            names.add(match[1]);
        }
    });

    return [...names];
})();

const primaryRamp = ramp('primary');
const accentRamp = ramp('accent');
const neutralRamp = ramp('neutral');

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/View/Components/**/*.php',
        './app/Support/**/*.php',
        './config/navigation.php',
    ],

    safelist: designSystemClasses,

    theme: {
        extend: {
            colors: {
                // ── Semantic tokens (use these in markup) ──
                canvas: v('canvas'),
                surface: {
                    DEFAULT: v('surface'),
                    2: v('surface-2'),
                    3: v('surface-3'),
                },
                line: {
                    DEFAULT: v('border'),
                    strong: v('border-strong'),
                    input: v('border-input'),
                },
                fg: {
                    DEFAULT: v('fg'),
                    muted: v('fg-muted'),
                    subtle: v('fg-subtle'),
                },
                brand: {
                    DEFAULT: v('brand'),
                    hover: v('brand-hover'),
                    fg: v('on-brand'),
                    text: v('brand-text'),
                    soft: v('brand-soft'),
                    'soft-fg': v('on-brand-soft'),
                },
                accent: {
                    DEFAULT: v('accent'),
                    text: v('accent-text'),
                    solid: v('accent-solid'),
                    'solid-hover': v('accent-solid-hover'),
                    fg: v('on-accent'),
                    ...accentRamp,
                },
                success: status('success'),
                warning: status('warning'),
                danger: status('danger'),
                info: status('info'),
                ring: v('ring'),

                // Extras beyond the contract (shell + charts + code cards).
                sidebar: v('sidebar-bg'),
                indicator: v('nav-indicator'),
                'tab-active': v('tab-active'),
                code: { DEFAULT: v('code-bg'), fg: v('code-fg') },
                grid: v('grid'),
                chart: {
                    1: v('chart-1'),
                    2: v('chart-2'),
                    3: v('chart-3'),
                    4: v('chart-4'),
                    5: v('chart-5'),
                    6: v('chart-6'),
                },

                // ── Raw ramps (illustrations, charts, course covers only) ──
                primary: primaryRamp,
                neutral: neutralRamp,

                // ── SAFETY NET for legacy views still using raw palette classes ──
                // Reason: ~150 views are rebuilt area by area; until then their
                // blue/purple/gray utilities render in the active brand ramps.
                blue: primaryRamp,
                indigo: primaryRamp,
                purple: primaryRamp,
                violet: primaryRamp,
                sky: primaryRamp,
                pink: accentRamp,
                fuchsia: accentRamp,
                orange: accentRamp,
                gray: neutralRamp,
                slate: neutralRamp,
                zinc: neutralRamp,
                stone: neutralRamp,
            },

            fontFamily: {
                sans: ['var(--cf-font-sans)'],
                display: ['var(--cf-font-display)'],
                mono: ['var(--cf-font-mono)'],
                num: ['var(--cf-font-num)'],
                eyebrow: ['var(--cf-font-eyebrow)'],
            },

            fontSize: {
                ui: 'var(--cf-ui-text)',
                eyebrow: ['var(--cf-eyebrow-size)', { lineHeight: '1rem' }],
                'page-title': ['var(--cf-page-title-size)', { lineHeight: '1.2' }],
                'card-title': ['var(--cf-card-title-size)', { lineHeight: '1.35' }],
                kpi: ['var(--cf-kpi-size)', { lineHeight: '1.1' }],
            },

            fontWeight: {
                display: 'var(--cf-display-weight)',
                title: 'var(--cf-title-weight)',
                'nav-active': 'var(--cf-nav-active-weight)',
            },

            letterSpacing: {
                display: 'var(--cf-display-tracking)',
                eyebrow: 'var(--cf-eyebrow-tracking)',
            },

            borderRadius: {
                DEFAULT: 'var(--cf-radius-sm)',
                sm: 'var(--cf-radius-sm)',
                md: 'var(--cf-radius-md)',
                lg: 'var(--cf-radius-lg)',
                xl: 'var(--cf-radius-xl)',
                '2xl': 'var(--cf-radius-2xl)',
                // Reason: legacy views overuse rounded-3xl; cap it at the token.
                '3xl': 'var(--cf-radius-2xl)',
                badge: 'var(--cf-badge-radius)',
            },

            boxShadow: {
                DEFAULT: 'var(--cf-shadow-1)',
                sm: 'var(--cf-shadow-1)',
                md: 'var(--cf-shadow-2)',
                lg: 'var(--cf-shadow-2)',
                xl: 'var(--cf-shadow-3)',
                '2xl': 'var(--cf-shadow-3)',
                1: 'var(--cf-shadow-1)',
                2: 'var(--cf-shadow-2)',
                3: 'var(--cf-shadow-3)',
            },

            spacing: {
                control: 'var(--cf-control-h)',
                'control-sm': 'var(--cf-control-h-sm)',
                'control-lg': 'var(--cf-control-h-lg)',
                row: 'var(--cf-row-h)',
                card: 'var(--cf-card-p)',
                gutter: 'var(--cf-gap)',
                'nav-item': 'var(--cf-nav-item-h)',
                sidebar: 'var(--cf-sidebar-w)',
                rail: 'var(--cf-rail-w)',
                topbar: 'var(--cf-topbar-h)',
                appbar: 'var(--cf-appbar-h)',
                tabbar: 'var(--cf-tabbar-h)',
            },

            maxWidth: {
                app: '80rem',
                landing: '75rem',
                measure: '68ch',
            },

            transitionTimingFunction: {
                cf: 'var(--cf-ease-out)',
            },

            transitionDuration: {
                fast: 'var(--cf-dur-1)',
                base: 'var(--cf-dur-2)',
                slow: 'var(--cf-dur-3)',
                reveal: 'var(--cf-dur-4)',
            },

            keyframes: {
                'cf-shimmer': {
                    '0%': { backgroundPosition: '100% 0' },
                    '100%': { backgroundPosition: '-100% 0' },
                },
                'cf-rise': {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'cf-fade': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                // Kept for legacy views that still use animate-fade-in / animate-slide-in.
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideIn: {
                    '0%': { transform: 'translateY(-10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
            },

            animation: {
                shimmer: 'cf-shimmer 1.6s linear infinite',
                rise: 'cf-rise 200ms var(--cf-ease-out) both',
                fade: 'cf-fade 150ms linear both',
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-in': 'slideIn 0.3s ease-out',
            },
        },
    },

    plugins: [forms],
};
