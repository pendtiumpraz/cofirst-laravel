# PLANNING.md — Coding First LMS (rombak total v2)

This is the architecture contract for the overhaul. Read it before changing code.
(The generic Python rules in CLAUDE.md do not apply to this PHP project: we use
Laravel conventions, PHPUnit feature tests in `tests/`, Pint formatting, and
PHPDoc/Google-style docblocks on public methods.)

## 1. Product

Coding First (codingfirst.id) — coding school for kids, teens and adults; instructors
from Japanese tech companies. One Laravel app serves:

* **Public site** — landing page (2 templates, switchable), programs, about, contact,
  certificate verification, privacy/legal pages.
* **Dashboards for 6 roles** — superadmin, admin, teacher, student, parent, finance
  (spatie/laravel-permission; a user may hold several roles, `session('active_role')`
  picks the active one).
* **PWA** — installable on phones, offline fallback page.

UI language: **Bahasa Indonesia** everywhere (labels, flash messages, validation).

## 2. Stack & conventions

* Laravel 12, PHP 8.2, Blade + Alpine.js 3, Tailwind CSS v3 (PostCSS), Vite 6.
* Icons: `blade-ui-kit/blade-heroicons` → `<x-heroicon-o-home class="size-5" />`
  (outline for UI, `-s-` solid for small accents, `-m-` mini 20px).
* Charts: `chart.js` from npm (never a CDN).
* Fonts: self-hosted `@fontsource*` packages imported in `resources/js/app.js`.
* **No third-party requests without consent** (UU PDP): no Google/Bunny fonts, no CDN
  scripts, no hotlinked images. Optional embeds (maps, analytics) load only after
  cookie consent.
* Money: integers/decimal columns, format with `number_format($v, 0, ',', '.')` → `Rp 1.250.000`.
* Dates: `Asia/Jakarta` by default (setting `general.timezone`), Indonesian format
  (`translatedFormat('d M Y')`, locale `id`).
* Files under 500 lines; split into partials/components/services when bigger.
* Never put business logic in route closures — controllers + Form Requests.
* Authorization: every route that takes a model must check ownership/role (Policies or
  explicit checks). Parents may only see their own children; students only their own data;
  teachers only their classes.

## 3. Routes

`routes/web.php` holds public pages and requires one file per area from `routes/web/`:

| File | Area |
|---|---|
| `shared.php` | profile, project gallery, class reports, certificates, chat, gamification |
| `account.php` | account centre: preferences, privacy centre, sessions (auth) |
| `admin.php` | `admin.*` (admin, superadmin) |
| `settings.php` | `admin.settings.*` |
| `privacy-admin.php` | `admin.privacy.*` PDP back-office |
| `privacy.php` | public privacy/legal pages, public DSR form, cookie consent endpoint |
| `pwa.php` | manifest, offline page |
| `teacher.php`, `student.php`, `parent.php`, `finance.php` | role portals |

Route **names are a public contract** — do not rename existing names. Register static
segments (`create`, `featured`, …) **before** wildcard routes.

## 4. Settings module

* Table `settings` (key, JSON value, updated_by). Per-user preferences: `users.preferences` (JSON).
* Registry: `config/settings.php` — every key with group, type, label, default, rules.
* Read: `setting('appearance.theme')` (cached; defaults when unset). Write via
  `setting()->set($key, $value, auth()->id())` / `setMany()`.
* Admin UI: Admin › Pengaturan, one tab per group, rendered from the registry.
* **A/B version toggles** (owner chooses in Settings › Tampilan & Versi):
  * `appearance.theme` — `a` Sumi & Shu (Editorial, default) / `b` Asagi (Presisi)
  * `appearance.allow_user_theme` — let each user pick A/B (stored in `users.preferences.theme`)
  * `appearance.logo_variant` — `a` CF dalam hanko / `b` C1 caret / `auto` (follow theme, default)
  * `appearance.landing_version` — `a` Majalah sekolah / `b` Halaman produk
  * `appearance.dashboard_nav` — `sidebar` / `topnav` (desktop)
  * `appearance.mobile_nav` — `tabs` (bottom tab bar) / `drawer`
  * `appearance.stat_style` — `strip` / `cards` (pure CSS via `html[data-stat]`)
  * `appearance.student_home` — `tenang` / `ceria` (student home partial only)
  * `appearance.auth_layout` — `split` / `centered`
  * `appearance.density` — `comfortable` / `compact` (user-overridable)

## 5. Design system (see §8 for tokens)

* Colours are CSS variables (RGB triplets) named `--cf-*`, defined per theme in
  `resources/css/themes/a.css` / `b.css` under `:root[data-theme=a|b]` and `.dark[data-theme=a|b]`.
  Tailwind maps them to semantic utilities — **use only these in markup**:
  `bg-canvas`, `bg-surface`, `bg-surface-2`, `bg-surface-3`, `border-line`, `border-line-strong`,
  `border-line-input`, `text-fg`, `text-fg-muted`, `text-fg-subtle`, `bg-brand`, `hover:bg-brand-hover`,
  `text-brand-fg` (text on brand), `text-brand-text` (brand-coloured text/links), `bg-brand-soft`,
  `text-brand-soft-fg`, `text-accent-text`, `bg-accent-solid`, `text-accent-fg`,
  `bg-success-soft text-success`, `bg-success-solid`, (same for `warning`, `danger`, `info`), `ring-ring`.
  Raw ramps (`primary-50..950`, `accent-50..950`, `neutral-50..950`) only for illustrations, charts and
  course covers. **Never use `dark:` colour variants** — the tokens switch automatically in dark mode.
  Never hard-code hex colours or Tailwind palette colours (`bg-blue-600`, `text-gray-500`).
* `<html>` carries the UI state rendered by the server: `lang="id" data-theme="a|b" data-nav="sidebar|topnav"
  data-mobile-nav="tabs|drawer" data-density="comfortable|compact" data-stat="strip|cards"` and `class="dark"`
  when dark mode is active.
* Dark mode: `class` strategy, user preference in `users.preferences.color_mode` (light|dark|system),
  default `appearance.color_mode`.
* UI components (anonymous Blade, `resources/views/components/ui/`):
  `x-ui.button`, `x-ui.card`, `x-ui.stat`, `x-ui.badge`, `x-ui.alert`, `x-ui.page-header`,
  `x-ui.table` (+ `x-ui.th`), `x-ui.empty-state`, `x-ui.field`, `x-ui.input`, `x-ui.select`,
  `x-ui.textarea`, `x-ui.checkbox`, `x-ui.toggle`, `x-ui.file`, `x-ui.modal`,
  `x-ui.dropdown`, `x-ui.tabs`, `x-ui.avatar`, `x-ui.progress`, `x-ui.breadcrumbs`,
  `x-ui.confirm-delete`, `x-ui.filter-bar`, `x-ui.toast` (flash), `x-ui.chart`.
  Component docs + live preview: `resources/views/components/ui/README.md` and the
  dev-only UI kit page.
* Layouts:
  * `<x-app-layout>` (dashboard shell; slots `header`, default slot, `title` prop) — renders
    sidebar or top-nav per `appearance.dashboard_nav`, mobile bottom tab bar + drawer.
    Navigation items come from `App\Support\Navigation\Navigation` (per active role); do not
    hard-code menus in layouts.
  * `<x-guest-layout>` (auth pages; split/centered per `appearance.auth_layout`).
  * `<x-public-layout>` (public site header/footer, cookie banner).
* Mobile first: every table is wrapped in a horizontal-scroll container or collapses to cards
  under `md`; tap targets ≥ 44px; forms single-column on mobile.

## 6. Privacy & UU PDP compliance (UU No. 27 Tahun 2022)

Modules (each owns its models/controllers/views/routes):

1. **Consent & policies** — `ConsentPurpose`, `ConsentRecord` (append-only history: who,
   for whom (parental), purpose, version, granted/withdrawn, method, evidence),
   `PolicyDocument` (privacy notice, terms, cookie policy, children's notice, DPA template;
   versioned, publish/effective dates), `PolicyAcceptance`; cookie banner; registration
   consent; parental/guardian consent for users under `privacy.child_age_threshold`;
   re-acceptance middleware when a new policy version is published.
2. **Data subject rights (DSR) & privacy centre** — `DataSubjectRequest` (types: access,
   portability/export, rectification, erasure, restriction, objection, withdraw consent,
   other; SLA `privacy.dsr_sla_hours`), user privacy centre, data export (JSON/CSV zip),
   erasure/anonymisation service honouring retention obligations, public DSR form, admin
   handling with deadlines.
3. **Governance** — ROPA (`ProcessingActivity`), processors register + DPA
   (`DataProcessor`), DPIA (`PrivacyImpactAssessment`), breach log (`DataBreach`, 3×24h),
   `AuditLog` of access to personal data, retention purge command (scheduled),
   security headers middleware, privacy dashboard for the DPO.

Privacy by default: children's names/photos are never public without recorded parental
consent; leaderboards show first names/initials; no analytics without consent.

## 7. Testing

* `php artisan test` (SQLite in-memory). `tests/Feature/Smoke/RouteSmokeTest.php` hits every
  GET route as every role and fails on any 5xx — keep it green.
* Every new feature: feature tests for happy path, an edge case and a failure/authorization case.
* Local manual DB: `database/database.sqlite`
  (`DB_CONNECTION=sqlite DB_DATABASE=<abs path> php artisan migrate:fresh --seed`).

## 8. Design tokens

Source of truth: `resources/css/themes/a.css` and `b.css` (values), `tailwind.config.js` (utility names),
`resources/views/components/ui/README.md` (components, layouts, shell API), live preview at `/admin/ui-kit`.

**Versions** (`setting('appearance.theme')`, rendered as `<html data-theme>`):

| | **A · Sumi & Shu — Editorial** (default) | **B · Asagi — Presisi** |
|---|---|---|
| Mood | warm washi paper, ink text, hairlines, almost shadowless | zinc/ink, 1px borders, mono metadata |
| Canvas light / dark | `#FAF8F4` / `#0F0E0D` | `#FAFAFA` / `#09090B` |
| Primary (actions, links) | aizome `#1D5D80` (600), hover `#174B69` | asagi `#056A81` (600), hover `#01566A` |
| Accent (signature only) | hanko vermilion `#AE4027` | shu caret `#EE4D1C` (text `#AC2F02`) |
| Neutral ramp | stone | zinc |
| Status success/warning/danger/info | `#2E7650` `#9A5B0B` `#B0283F` `#2C5C99` | `#15803D` `#B45309` `#BE123C` `#4F46E5` |
| Display / body / mono | Newsreader / Plus Jakarta Sans / JetBrains Mono | Geist / Geist / Geist Mono |
| KPI numerals, eyebrows | Newsreader; Jakarta caps .14em | Geist Mono; Geist Mono caps .08em |
| Radii sm/md/lg/xl/2xl, badge | 6/10/14/20/28, pill | 4/6/10/14/20, 4px |
| Controls / rows (comfortable) | 44 / 52px (compact 40/40) | 40 / 44px (compact 32/36) |
| Sidebar / rail | 264 / 72px on canvas | 248 / 60px on surface (canvas in dark) |
| Active nav marker | 2px vermilion bar, text weight 600 | 2px teal bar, weight 500 |

Fonts are self-hosted `@fontsource-variable/*`, imported in `resources/css/app.css` (supersedes §2: not
app.js); browsers download only the active theme's faces. Landing A adds the Newsreader opsz cut via `landing-a.css`.

**Logo** (`appearance.logo_variant` = `a` | `b` | `auto` → follows the theme): A "CF dalam hanko" — vermilion seal
`#AE4027` with C and F knocked out (never recoloured, also in dark); B "C1 caret" — ink tile `#09090B`, paper C,
caret `#EE4D1C` (zinc keyline in dark). Use `<x-brand.logo>` / `<x-brand.lockup>`; icon sets in `public/brand/{a,b}/`.

**Using tokens**
* Colours are RGB-triplet variables `--cf-*` (`--cf-canvas`, `--cf-surface[-2|-3]`, `--cf-border[-strong|-input]`,
  `--cf-fg[-muted|-subtle]`, `--cf-brand[-hover|-text|-soft]`, `--cf-on-brand`, `--cf-accent[-text|-solid]`,
  `--cf-ring`, `--cf-nav-indicator`, `--cf-tab-active`, `--cf-sidebar-bg`, `--cf-{success|warning|danger|info}[-soft|-solid]`,
  `--cf-code-bg/fg`, `--cf-chart-1..6`, ramps `--cf-{primary|accent|neutral}-50..950`). In markup use only the
  Tailwind utilities listed in §5 plus `bg-indicator`, `text-tab-active`, `bg-sidebar`, `bg-code`, `chart-1..6`.
* Non-colour tokens: fonts (`font-sans|display|mono|num|eyebrow`), `font-title`, `tracking-display`,
  `text-page-title|card-title|kpi|eyebrow|ui`, radii `rounded-sm..2xl|badge`, shadows `shadow-1|2|3`, density
  (`h-control`, `h-row`, `p-card`, `gap-gutter`, `h-nav-item`), shell (`w-sidebar`, `w-rail`, `h-topbar`,
  `h-appbar`, `h-tabbar`, `max-w-app`, `max-w-landing`), motion (`ease-cf`, `duration-fast|base|slow|reveal`).
* `<em>` in headings: italic in A, brand-coloured roman in B (`--cf-em-style/--cf-em-color`).
* Legacy safety net: `blue/indigo/purple/violet/sky-*` map to the primary ramp, `pink/fuchsia/orange-*` to accent,
  `gray/slate/zinc/stone-*` to neutral — constants that do **not** follow dark mode; migrate views to tokens.

**Dark mode rules**
* `class` strategy: `<html class="dark">`, resolved before first paint by `<x-ui.color-mode-script/>`
  (user preference when `appearance.allow_user_color_mode` → device choice `localStorage['cf-color-mode']` →
  `appearance.color_mode`, default `system`). Never write `dark:` colour variants — every token has a dark value.
* Elevation in dark comes from a surface step plus `border-line-strong`; A drops all shadows, B keeps only `shadow-3`.
  Overlays therefore always carry `border border-line-strong bg-surface`.
* Dark A: light-indigo brand button with ink text; the seal stays `#AE4027`; photos get `brightness(.92)`.
* Every text pair ≥ 4.5:1 and control boundary ≥ 3:1 in both versions and modes; focus ring is
  `outline: 2px solid rgb(var(--cf-ring))`, offset 2px, never removed. `prefers-reduced-motion` or
  `html.reduce-motion` removes transforms and transitions.
