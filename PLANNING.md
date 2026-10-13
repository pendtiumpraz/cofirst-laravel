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
* **A/B version toggles** (owner chooses in Settings):
  * `appearance.theme` — colour theme A / B
  * `appearance.logo_variant` — logo A / B
  * `appearance.landing_version` — landing template A / B
  * `appearance.dashboard_nav` — `sidebar` / `topnav`
  * `appearance.auth_layout` — `split` / `centered`

## 5. Design system (see §8 for tokens)

* Colours are CSS variables (RGB triplets) on `:root` / `[data-theme="b"]` / `.dark`,
  mapped in `tailwind.config.js` → use semantic classes only:
  `bg-canvas`, `bg-surface`, `bg-surface-muted`, `text-ink`, `text-ink-muted`,
  `text-ink-subtle`, `border-line`, `bg-primary-600`, `text-primary-700`,
  `bg-accent-500`, `text-success-700`, `bg-warning-50`, `text-danger-600`, `bg-info-50` …
  Never hard-code hex colours or raw Tailwind palette colours (`bg-blue-600`) in views.
* Dark mode: `class` strategy (`<html class="dark">`), user preference in
  `users.preferences.color_mode` (light|dark|system), default `appearance.color_mode`.
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

_Filled in from the design review (Version A / Version B)._
