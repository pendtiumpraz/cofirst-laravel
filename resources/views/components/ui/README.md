# Coding First design system: components, layouts and shell

Anonymous Blade components on top of the `--cf-*` token contract
(`resources/css/themes/a.css`, `b.css`). One markup serves **Versi A · Sumi & Shu** (default, editorial)
and **Versi B · Asagi** (precise), in light and dark mode. Only the tokens change between them.

- Live preview of every token and component: **`/admin/ui-kit`**
  (`?theme=a|b&mode=light|dark|system&density=comfortable|compact&stat=strip|cards&errors=1`).
- Token values, fonts and logo rules: `PLANNING.md` §8.

## Ground rules

- **Colours.** Use only the semantic utilities:
  - surfaces and lines: `bg-canvas`, `bg-surface`, `bg-surface-2/3`, `border-line`, `border-line-strong`, `border-line-input`;
  - text: `text-fg`, `text-fg-muted`, `text-fg-subtle`;
  - brand: `bg-brand text-brand-fg`, `hover:bg-brand-hover`, `text-brand-text`, `bg-brand-soft text-brand-soft-fg`;
  - accent: `text-accent-text`, `bg-accent-solid text-accent-fg`;
  - status: `bg-{success|warning|danger|info}-soft text-{…}`, `bg-{…}-solid text-white`;
  - shell and code: `ring-ring`, `bg-indicator`, `text-tab-active`, `bg-sidebar`, `bg-code text-code-fg`, `bg-grid`.
- **Never** write `dark:` colour variants, hex colours or raw palette classes (`bg-blue-600`, `text-gray-500`).
  - The tokens switch automatically in dark mode and in Versi B.
  - Raw ramps (`primary-*`, `accent-*`, `neutral-*`, `chart-1..6`) are only for illustrations, charts and course covers.
  - `dark:hidden` / `dark:block` for swapping artwork is fine; it is not a colour.
- **Type.**
  - Families: `font-sans`; `font-display` (headings, card titles; also applies the theme's display weight);
    `font-mono` (IDs, codes, invoice numbers); `font-num` (KPIs).
  - Weights and tracking: `font-title` (A 500, B 600) for titles, `tracking-display`.
  - Sizes: `text-page-title`, `text-card-title`, `text-kpi`, `text-eyebrow`, `text-ui`.
  - Labels: `.eyebrow` (section labels) and `.nav-label` (menu group labels).
  - Numbers in tables and money: `.num` (tabular, lining).
  - Never hard-code `font-medium` on a display heading: B needs 600, so use `font-title`.
- **Shape and density.**
  - Radii `rounded-sm|md|lg|xl|2xl|badge` and shadows `shadow-1|2|3` follow the theme. Cards at rest have no shadow.
  - Density tokens as utilities: `h-control`, `h-row`, `p-card`, `gap-gutter`, `h-nav-item`, `w-sidebar`, `w-rail`,
    `h-topbar`, `h-appbar`, `h-tabbar`.
  - Max widths: `max-w-app` (1280), `max-w-landing` (1200), `max-w-measure` (68ch).
  - Motion: `ease-cf`, `duration-fast|base|slow|reveal`; add `motion-reduce:transition-none` to custom transitions.
- **Icons.** `<x-ui.icon name="academic-cap" />` (heroicons; the `s-`/`m-`/`c-` prefixes give solid/mini/micro), or
  `<x-heroicon-o-name class="size-5" />` directly. The stroke follows the theme (A 1.5, B 1.75).
- **Alpine on component tags.** On a **Blade component tag**, write `x-on:click` / `x-bind:…`, not `@click` / `:attr`;
  otherwise Blade evaluates the attribute as PHP and the page returns 500. For loading states use `loading-when`.
- **Buttons.** Use `<x-ui.button>` or the `.btn` classes; never hand-roll `bg-brand px-4 …` buttons.
  Accent-filled buttons (`variant="accent"`) are for landing conversion only, never in dashboards.
- **Mobile first.**
  - Tap targets are at least 44px on touch devices; the tokens enforce this.
  - Inputs are 16px on phones.
  - No horizontal page scroll at 320px.
  - Wide tables use `mobile="scroll"` or `mobile="stack"`.

---

## Layouts and shell

### `<x-app-layout>`: dashboard shell (`layouts.app`)

Use it for every signed-in page. It supports two conventions:

```blade
<x-app-layout title="Pengguna">                {{-- <title>, mobile app bar, breadcrumb --}}
    <x-slot name="header">…</x-slot>           {{-- optional; legacy h1/h2 inside are restyled as the page title --}}
    …content…
</x-app-layout>

@extends('layouts.app')                         {{-- also supported --}}
@section('title', 'Pengguna')  @section('header') … @endsection  @section('content') … @endsection
```

**Page title.** The title is taken from the first of these that exists:
1. the `title` prop;
2. `@section('title')`;
3. the text of the header slot;
4. the active menu item;
5. 'Dasbor'.

**Layout and padding.**
- The layout owns the padding: a `max-w-app` container with `px-4 sm:px-6 lg:px-8 py-6 lg:py-8`. Pages must not add their
  own outer container or padding.
- Prefer `<x-ui.page-header>` as the first element of the content over the legacy header slot.
- **Stacks:** `@push('styles')` (end of `<head>`) and `@push('scripts')` (end of `<body>`).

**Desktop navigation** (`setting('appearance.dashboard_nav')`):
- `sidebar`: 264px in A / 248px in B, grouped and collapsible. It collapses to a 72px / 60px icon rail with `[`,
  remembered in `localStorage['cf-sidebar']` and applied before paint.
- `topnav`: lockup, flat links (8 items or fewer) or group dropdowns, and a "Lainnya" mega menu.

**Mobile, below 1024px.** One app bar (mark, page title, search, messages, account), plus either:
- bottom tabs with a "Lainnya" sheet (`appearance.mobile_nav=tabs`); or
- an 85vw drawer (`drawer`).
Both are focus-trapped dialogs that close on Esc and return focus to their trigger.

**Always present.**
- Skip link to `#konten`.
- Ctrl/⌘K command palette over every menu page.
- Colour-mode menu (when `appearance.allow_user_color_mode`).
- Chat unread dot.
- Account menu: Profil, Preferensi, Privasi & Data Saya, Ganti peran, Pasang aplikasi, Keluar.
- Announcement bar (`system.dashboard_announcement`) and maintenance bar (`system.maintenance_notice`).
- `<x-ui.toast/>`.
- PWA install banner.

**Menus.** Menus come **only** from `App\Support\Navigation\Navigation`. Never hard-code menus in a view; add items in
`App\Support\Navigation\Menus` (`Menus::item(label, route, icon, activePatterns, ?roles, ?badge)`, `Menus::section(key, label, items)`).
- An item appears only when `Route::has(route)` and the user passes the route's `role:`/`permission:` middleware
  (`RouteAccess::allows`).
- `Navigation` API:
  - `for(User, ?role)` returns `[{key, label|null, items:[{label, route, url, icon, active, is_active, badge, children}]}]`;
  - `accountItems(User)`;
  - `mobileTabs(User, ?role)` returns 4 tabs; "Lainnya" is added by the layout;
  - `activeRole(User, ?requested)` reads `session('active_role')`, else the first of superadmin, admin, teacher, parent, student, finance;
  - `rolesOf(User)`;
  - `dashboardRoute(role)`;
  - `flatten()`, `current()`, `isActive()`, `unreadMessages(User)` (cached 30 s);
  - constants `ROLE_LABELS` and `DASHBOARDS`.
- `PrivacyMenu::items()` builds the admin "Privasi & PDP" group from the `admin.privacy.*.index` routes.

### `<x-guest-layout>`: auth pages (`layouts.guest`)

```blade
<x-guest-layout title="Masuk" heading="Masuk ke akun Anda" subheading="Gunakan email yang terdaftar.">…form…</x-guest-layout>
```

- Also works with `@extends('layouts.guest')` + `@section('title')` / `@section('content')`.
- `setting('appearance.auth_layout')`:
  - `split`: brand panel (theme illustration, tagline, UU PDP trust points) with the form on the right; on phones,
    the logo and the form sit at the top;
  - `centered`: stacked lockup with descriptor above a card, vertically centred.
- Includes the legal links footer and `@includeIf('privacy.partials.cookie-banner')`.
- **No toast region**: auth pages show `session('status')` inline with `<x-auth-session-status>` or `<x-ui.alert>`, and
  field errors inline.

### `<x-public-layout>`: public site (`layouts.public`)

```blade
<x-public-layout title="Program" description="…" :transparent-header="true">…sections…</x-public-layout>
```

- Also works with `@extends('layouts.public')` + `title` / `description` / `content` sections.
- **Header:** sticky; Beranda / Program / Tentang / Kontak, each shown only when its route exists; Masuk + Daftar, or Dasbor.
- **Footer:**
  - contact from `general.*` and socials;
  - DPO email (`privacy.dpo_email`) and the data-controller line;
  - legal links from `LegalLinks::all()` (privacy, terms, cookies, children, rights, centre), each shown once its route exists;
  - a "Pengaturan cookie" button that dispatches the window event `cf-cookie-settings`.
- **Also included:** Open Graph tags, EducationalOrganization JSON-LD, `<x-ui.toast :validation="false"/>` (do not
  repeat flashes inline), the cookie banner, and the optional WhatsApp button (`integrations.whatsapp_button` + `general.whatsapp`).
- Content goes in `<main id="konten">`. Wrap sections in `max-w-landing` yourself.
- Landings add `@vite('resources/css/landing-a.css')` or `@vite('resources/css/landing-b.css')` through `@push('styles')`.

### Shared head, `<html>` state and appearance

- `@include('layouts.partials.head', ['pageTitle' => ?, 'description' => ?, 'robots' => ?, 'withCsrf' => true, 'social' => false])`
  outputs:
  - `<title>Judul · Coding First</title>` and the meta description;
  - two `theme-color` metas;
  - icons of the active logo variant;
  - the manifest and iOS metas (when `pwa.enabled`);
  - `[x-cloak]`, `<x-ui.color-mode-script/>`, the sidebar-rail script, `@vite`, PWA registration and `@stack('styles')`.
- `@include('layouts.partials.skip-link')` renders "Lewati ke konten utama" and targets `#konten`.
- `<html {!! App\Support\Navigation\Appearance::htmlAttributes($user) !!}>` renders:
  - `lang="id" data-theme="a|b" data-nav="sidebar|topnav" data-mobile-nav="tabs|drawer"`;
  - `data-density="comfortable|compact" data-text="sm|md|lg" data-stat="strip|cards"`;
  - `data-color-mode`, `data-default-color-mode`, `data-allow-color-mode="true|false"`, `[data-user-color-mode]`;
  - `[data-color-mode-endpoint]` (auto-detected route `account.color-mode`, `account.preferences.color-mode` or `preferences.color-mode`);
  - `class="[dark] [reduce-motion]"`.
- `Appearance::state(?User)` returns theme, logo, nav, mobile_nav, density, stat, text, color_mode, default_color_mode,
  user_color_mode, allow_color_mode, dark, reduce_motion, auth_layout. Precedence for each value:
  1. `session('appearance_preview')` (settings "Pratinjau"; keys theme, logo_variant, dashboard_nav, mobile_nav, density, stat_style, auth_layout);
  2. `users.preferences`: `theme` only when `appearance.allow_user_theme`; `color_mode` only when `appearance.allow_user_color_mode`;
     `density`, `text_size` (sm|md|lg) and `reduce_motion` always;
  3. the setting;
  4. the default.
- Other `Appearance` helpers:
  - `logoVariant(?variant, ?theme)` returns `a|b` (`auto` follows the theme);
  - `colors(?theme)` returns `canvas`, `canvas_dark`, `primary`, `mark` as hex;
  - `customLogoUrl(bool $dark)`;
  - `nonceAttribute()` for inline scripts: `<script{!! Appearance::nonceAttribute() !!}>`.

### Shell JS (inline, registered on `alpine:init`)

- **`Alpine.data`:**
  - `cfDialog`: focus-trapped dialog. Needs `x-ref="panel"`, optional `x-ref="initial"`; provides `show()`, `hide()`, `trap($event)`.
  - `cfMenu`: disclosure menu. Needs `x-ref="button"` / `x-ref="panel"`; provides `toggle()`, `close()`.
  - `cfNavGroup(key, forceOpen)`: closed groups are remembered in `localStorage['cf-nav-closed']`.
  - `cfSidebarToggle`, `cfNotice(hash)`, `cfPalette(items)`, `cfInstall`.
- **Window events:** `cf-palette-open`, `cf-sheet-open`, `cf-drawer-open`, `cf-sidebar-changed`, `cf-installable`, `cf-installed`
  and `cf-cookie-settings`. `window.cfToggleSidebar()` toggles the rail.
- **Logout:** every logout form must carry `data-cf-logout` (or use `@include('layouts.shell.logout')`). The shell then
  posts `CF_LOGOUT` to the service worker, which deletes every cache.

### Brand

- `<x-brand.logo variant="a|b|auto|null" type="full|mark" size="sm|md|lg|xl|{px}" layout="horizontal|stacked" :descriptor="bool" title="…"/>`
  - `null` reads `appearance.logo_variant`; `auto` follows the theme.
  - `appearance.custom_logo` (and `custom_logo_dark`) replace the artwork when set.
  - The shell uses `sm` in B and `md` in A.
- `<x-brand.lockup variant size layout descriptor name/>`: mark plus a live-text wordmark.
  - A: Newsreader 500 with *First* in italic; the descriptor "SEKOLAH CODING · MENTOR INDUSTRI JEPANG" sits underneath.
  - B: Geist 600; the mono domain follows a divider.
  - Mark sizes: sm 28, md 32, lg 40, xl 56 px.
- `<x-brand.mark-a size tone="seal|mono" title/>` (vermilion hanko #AE4027 with CF knocked out) and
  `<x-brand.mark-b size tone="tile|mono" title/>` (ink tile, paper C, caret #EE4D1C, with a zinc keyline in dark mode).
  Without `title` they are `aria-hidden`.
- Static files live in `public/brand/{a,b}/`: `mark.svg`, `logo.svg`, `logo-dark.svg`, `favicon.svg`, `monochrome.svg`,
  `favicon.ico`, `favicon-16/32.png`, `apple-touch-icon.png`, `icon-192/512.png`, `maskable-192/512.png`, `og-image.png`.
- To regenerate them, run `node scripts/generate-icons.mjs`, then bump `CACHE_VERSION` in `public/sw.js`.

### PWA

- `GET /manifest.webmanifest` (`pwa.manifest`) is built from the `pwa.*` settings and the active theme and logo. It returns 404 when `pwa.enabled` is off.
- `GET /offline` (`pwa.offline`) is anonymous and `Cache-Control: public`.
- `public/sw.js` caches only the offline page, its build assets and the brand icons. It never caches authenticated HTML,
  JSON, or any response marked `no-store`, `private` or non-200.
- `pwa.partials.register` registers the service worker. When the PWA is off, it unregisters workers and deletes `cf-*` caches.
- `pwa.partials.install-prompt` shows the install banner from the second visit, with iOS instructions and a 30-day dismissal.

---

## Actions

### `x-ui.button`
Props: `variant` primary (default) · secondary · ghost · soft · danger · danger-ghost · link · accent;
`size` xs · sm · md (default) · lg; `href` (renders `<a>`); `type` (default **`button`** — pass `type="submit"` in forms);
`icon`, `icon-right` (heroicon names); `arrow` (trailing → that nudges); `label` (aria-label; icon-only buttons
also get a tooltip); `loading` (server state: spinner, disabled, `aria-busy`); `loading-text`;
`loading-when` (Alpine expression, e.g. `isSubmitting`); `block` (full width); `disabled`.
Icon-only = `icon` + no slot (always pass `label`).
```blade
<x-ui.button type="submit" icon="check" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan perubahan</x-ui.button>
<x-ui.button variant="secondary" :href="route('admin.users.index')">Batal</x-ui.button>
<x-ui.button variant="ghost" size="sm" icon="pencil-square" label="Ubah" />
```
CSS: `.btn .btn-primary|secondary|ghost|soft|danger|danger-ghost|accent|link .btn-xs|sm|lg .btn-icon .btn-block .btn-group .spinner .link`.

### `x-ui.badge`
Props: `tone` neutral · primary · accent · success · warning · danger · info; `dot`; `mono` (IDs/tech tags);
`outline` (role chips); `solid` (urgent count, accent fill); `icon`.
```blade
<x-ui.badge tone="success" dot>Lunas</x-ui.badge>  <x-ui.badge mono>INV-2026-0142</x-ui.badge>
```
Also `.count-pill` for counts in tabs/nav.

### `x-ui.dropdown` + `x-ui.dropdown-item`
Dropdown props: `label`, `icon`, `variant` (trigger: secondary|ghost|primary), `size`, `align` right|left,
`width` 48|56|64|72|auto, `up`. Slots: default (items), `trigger` (custom; use `x-bind:aria-expanded="open.toString()"`).
Item props: `href`, `icon`, `danger`, `active`, `method` (post|put|patch|delete → tiny form with CSRF).
Divider/label: `<div class="dropdown-divider"></div>`, `<p class="dropdown-label">Format</p>`.
```blade
<x-ui.dropdown label="Ekspor" icon="arrow-down-tray">
    <x-ui.dropdown-item :href="route('…', ['format' => 'csv'])" icon="table-cells">CSV</x-ui.dropdown-item>
    <div class="dropdown-divider"></div>
    <x-ui.dropdown-item :href="route('logout')" method="post" icon="arrow-right-start-on-rectangle">Keluar</x-ui.dropdown-item>
</x-ui.dropdown>
```

### `x-ui.kbd` · `x-ui.divider` · `x-ui.icon`
`<x-ui.kbd>Ctrl K</x-ui.kbd>` · `<x-ui.divider />`, `label="atau"`, `vertical` ·
`<x-ui.icon name="bell" size="16|20|24" label="…(only if meaningful)" />` (unknown names render nothing).

## Surfaces & layout

### `x-ui.page-header`
Props: `title` (or `<x-slot:title>` to allow `<em>`), `subtitle`, `eyebrow`, `breadcrumbs` (array, see breadcrumbs).
Slots: `badge`, `meta` (mono meta row), `actions`, default (under the subtitle, e.g. link tabs).
```blade
<x-ui.page-header title="Pengguna" subtitle="Kelola akun." :breadcrumbs="['Admin' => route('admin.dashboard'), 'Pengguna' => null]">
    <x-slot:actions><x-ui.button icon="plus" :href="route('admin.users.create')">Tambah pengguna</x-ui.button></x-slot:actions>
</x-ui.page-header>
```

### `x-ui.breadcrumbs`
`items`: `[['label' => 'Admin', 'url' => '/admin'], ['label' => 'Pengguna']]` or `['Admin' => '/admin', 'Pengguna' => null]`.
Last item gets `aria-current="page"`.

### `x-ui.section-heading`
Props: `title`, `description`, `eyebrow`, `number` ('01' editorial numeral), `level` 2|3|4. Slot `actions`.

### `x-ui.card`
Props: `title`, `subtitle`, `eyebrow`, `padding` md (default) · sm · none (flush body for lists/tables; header gets a rule),
`variant` default · interactive · highlight · hero · callout · inset · code, `href` (stretched link → whole card clickable),
`as` div|section|article|aside|li, `level` (heading level, default 2).
Slots: default, `actions`, `footer` (right-aligned actions on surface-2), `header` (replaces title block).
Callout colour: `style="--callout-c: var(--cf-success)"`. `highlight` / `hero` draw a 2px top rule (nav indicator /
seal colour) that stops short of the rounded corners.
```blade
<x-ui.card title="Kelas berikutnya" eyebrow="Hari ini" variant="highlight">
    <x-slot:actions><x-ui.button variant="link" size="sm" arrow :href="…">Lihat semua</x-ui.button></x-slot:actions>
    …
</x-ui.card>
```

### `x-ui.stat` and `x-ui.metric-strip`
Stat props: `label`, `value` (null/'' → '—' "Belum ada data", never a fake zero), `unit`, `hint`, `trend` up|down|flat,
`delta` ('12%'), `trend-tone` success|danger|neutral (override; default up=success, down=danger), `icon`,
`tone` (icon colour), `href` (whole cell links), `loading` (skeleton). Default slot under the numbers.
Labels and figures wrap instead of being truncated; on phones the KPI size steps down (A 26px, B 20px) so
'Rp 48,2 jt' fits a half-width cell.
Metric strip props: `stats` (array of stat prop arrays; `trendTone` or `trend_tone`), `cols` 1–6
(default = count), `label` (aria-label, default 'Ringkasan'). Or put `<x-ui.stat>`s in the slot and pass `:cols`.
Strip vs separate cards is **CSS only** via `html[data-stat]`.
```blade
<x-ui.metric-strip :stats="[
    ['label' => 'Siswa aktif', 'value' => '312', 'trend' => 'up', 'delta' => '4%', 'hint' => 'bulan ini', 'icon' => 'users'],
    ['label' => 'Pendapatan', 'value' => 'Rp 48,2', 'unit' => 'jt', 'href' => route('finance.dashboard')],
]" />
```

### `x-ui.tabs` + `x-ui.tab-panel`
Tabs props: `tabs` (key ⇒ label | `['label','count','icon','href']`), `active`, `group` (unique id prefix per page),
`label` (aria-label), `hash` (default true: `#key` selects the tab, updated with replaceState).
If any tab has `href` → server-side link tabs (`aria-current`). Otherwise Alpine tabs with arrow/Home/End keys.
Panel props: `name` (= tab key). Pass `active` to avoid a flash before Alpine boots.
```blade
<x-ui.tabs group="akun" active="profil" :tabs="['profil' => 'Profil', 'keamanan' => ['label' => 'Keamanan', 'count' => 2]]">
    <x-ui.tab-panel name="profil">…</x-ui.tab-panel>
    <x-ui.tab-panel name="keamanan">…</x-ui.tab-panel>
</x-ui.tabs>
```

### `x-ui.avatar`
Props: `user` (model: `name`, `id`, `profile_photo_path`/`profile_photo_url`) or `name` + `src`; `size` xs|sm|md|lg|xl|2xl;
`decorative` (alt="" when the name is printed next to it); `status` (online dot).
Initials are multibyte-safe; tint hashed from the user id (6 brand-safe tints, stable per person).

### `x-ui.empty-state`
Props: `icon` (default inbox; false = none), `title`, `description`, `hint` (mono), `size` inline|page.
Slots: `actions`, default (extra description). Copy: helpful, never cute ('Belum ada kelas minggu ini.').

## Feedback

### `x-ui.alert`
Props: `tone` info|success|warning|danger|neutral, `title`, `icon` (name or false), `dismissible`. Slot `actions`.
danger/warning use `role="alert"`, others `role="status"`.

### `x-ui.toast` (once per layout)
Renders session flashes `success|error|warning|info|message|status` (string, or `['title' => …, 'message' => …]`)
plus a validation summary when `$errors` is not empty (`:validation="false"` to disable). Client toasts:
`CF.toast.success('Tersimpan.')`, `Alpine.store('toast').push({ tone, title, message, timeout })`.
Success/info/warning auto-hide after 5 s; errors persist. `aria-live="polite"`.

### `x-ui.progress` · `x-ui.progress-ring`
Progress props: `value`, `max` (100), `tone` brand|success|warning|danger|info|accent, `label`, `show-value`, `size` sm|md|lg.
Ring props: `value` (0–100), `size` (px, 64), `stroke` (6), `label`, `marker` (leading seal/caret dot). Slot = centre text.

### `x-ui.skeleton`
`variant` text (`lines`) · title · avatar · block · card · rows (`rows`). Announces "Memuat…" once.

## Forms

All controls read `old()` after a failed submit (except passwords), set `aria-invalid` from `$errors`, and
use dot-notation error keys derived from the name (`items[0][name]` → `items.0.name`).

### `x-ui.field`
Props: `label`, `for` (control id — controls inside a field adopt it as their id and link the hint/error via
`aria-describedby`), `hint`, `error` (error-bag key, default derived from `for`; `false` hides), `optional`
('(opsional)'), `required` (visual '*'; also set `required` on the control), `sr-only-label`.

### `x-ui.input`
Props: `name`, `type` (text), `value`, `id` (default: field `for`, else name with `.`→`_`), `error`, `icon` (leading
heroicon), `prefix` / `suffix` (addons: 'Rp', '+62', '%'). All other attributes go to `<input>`.

### `x-ui.select`
Props: `name`, `options` ([value ⇒ label] · [['value','label','disabled']] · optgroups [group ⇒ [value ⇒ label]];
Collections accepted), `value` (scalar or array for `multiple`), `placeholder` (empty first option), `id`, `error`.
Slot: extra `<option>`s. In GET filter bars pass `:value="request('status')"`.

### `x-ui.textarea`
Props: `name`, `value`, `rows` (4), `id`, `error`.

### `x-ui.checkbox` · `x-ui.toggle`
Props: `name` (`roles[]` for groups), `value` ('1'), `checked`, `label` (or slot, may contain links), `description`,
`id`, `error`, `unchecked-value` (hidden input so an unticked box still submits, e.g. '0').
Toggle = `role="switch"` 44×24, label left / switch right (settings rows). Consents: one unchecked box per purpose.

### `x-ui.radio-cards`
Props: `name`, `options` (value ⇒ label | `['label','description','icon','disabled']`), `value`, `legend`, `hint`
(e.g. SLA note), `cols` 1|2|3, `error`, `required`. Renders a `<fieldset>`.

### `x-ui.file`
Props: `name` (`photos[]` + `multiple`), `accept`, `multiple`, `max-kb` (client check), `hint` ('PNG/JPG · maks. 2 MB'),
`id`, `error`, `preview` (image preview). Dropped files are attached to the real input. Form needs `enctype="multipart/form-data"`.

### `x-ui.filter-bar`
GET form: search (`search-name`, default `search`; '/' focuses it), filter controls in the slot, 'Terapkan' and
'Atur ulang' (shown when filters are active). Props: `action` (default current URL), `search` (bool), `placeholder`,
`value`, `keep` (query keys preserved: sort, direction, per_page; `page` always resets), `submit-label`. Slot `actions`.

### `x-ui.color-mode-toggle`
`variant` segmented (Terang · Gelap · Sistem) | icon (cycles). Hidden when `data-allow-color-mode="false"`.

Form layout helpers (CSS): `.form-section` (+ `.form-section-intro`, `.form-section-body`: 1/3 + 2/3 settings layout),
`.form-actions` (sticky action bar, full-width buttons on phones), `.segmented` / `.segmented-item[aria-pressed]`.

## Data

### `x-ui.table` + `x-ui.th`
Table props: `caption` (sr-only + region label), `mobile` scroll (default) | stack (rows become cards under 768px —
give each `<td>` a `data-label`), `sticky` (sticky header inside a 70vh scroll area), `sticky-first`, `flush`
(no card frame), `enhance` (+ `enhance-search`, `enhance-numbers`: legacy `table-enhancements.js`).
Slots: `head` (th cells, no `<tr>`), default (`<tr>` rows), `empty` (shown when there are no rows), `toolbar`, `footer`.
Cell classes: `.num` (right, tabular), `.cell-primary`, `.cell-secondary`, `.cell-actions`; selected row `aria-selected="true"`.
Th props: `sort` (column key → link `?sort=key&direction=asc|desc`, keeps the query string, resets `page`, sets
`aria-sort`), `numeric`, `align` left|right|center, `sort-param`, `direction-param`, `default-direction`.
```blade
<x-ui.table caption="Daftar pengguna" mobile="stack">
    <x-slot:toolbar><x-ui.filter-bar placeholder="Cari nama, email…" class="w-full" /></x-slot:toolbar>
    <x-slot:head>
        <x-ui.th sort="name">Nama</x-ui.th>
        <x-ui.th sort="amount" numeric>Jumlah</x-ui.th>
        <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
    </x-slot:head>
    @foreach ($rows as $row)
        <tr>
            <td class="cell-primary" data-label="Nama">{{ $row->name }}</td>
            <td class="num" data-label="Jumlah">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
            <td class="cell-actions"><x-ui.confirm-delete :action="route('…', $row)" icon-only size="xs" /></td>
        </tr>
    @endforeach
    <x-slot:empty><x-ui.empty-state title="Belum ada data." /></x-slot:empty>
    <x-slot:footer>{{ $rows->withQueryString()->links() }}</x-slot:footer>
</x-ui.table>
```
Pagination (`->links()`) is restyled with Indonesian copy: "Menampilkan 1–20 dari 134 · Sebelumnya · Berikutnya".

### `x-ui.chart`
Props: `type` line|bar|doughnut|pie, `labels`, `datasets` ([`label`, `data`, optional `tone` primary|success|warning|
danger|info|accent or `color`, `fill`, any Chart.js dataset option]), `format` number|idr|idr-compact|percent,
`label` (**required takeaway sentence** = aria-label), `height` (280), `table` (default true: 'Lihat tabel' toggle
with an accessible data table), `stacked`, `horizontal`, `legend`, `highlight-last` (bar: current period in series 2),
`options` (raw Chart.js options, deep-merged), `caption`.
Chart.js is bundled (lazy chunk, only on pages with charts), initialises when visible and re-renders on theme/mode change.
After injecting chart markup dynamically call `CF.charts.init(element)`.

## Overlays

### `x-ui.modal`
Props: `name` (required), `title`, `description`, `max-width` sm|md|lg (default)|xl|2xl|4xl, `show` (e.g.
`:show="$errors->any()"`), `closeable` (true). Slots: default (body), `footer`, `trigger` (click opens).
Open/close: `$dispatch('open-modal', 'name')`, `$dispatch('close-modal', 'name')`; inside: `hide()` or `$dispatch('close')`.
Focus trap (`@alpinejs/focus`), Esc/backdrop close, focus restored, bottom sheet on phones, teleported to `<body>`.
Submit buttons in the footer need `form="form-id"`.

### `x-ui.confirm-delete`
Props: `action` (required URL), `title` ('Hapus data ini?'), `message`, `label` ('Hapus'), `confirm-text` ('Ya, hapus'),
`cancel-text`, `variant` (trigger; danger-ghost default), `size` (sm), `icon` (trash), `icon-only`, `method` (DELETE),
`confirm-phrase` (typed confirmation, e.g. 'HAPUS'), `name` (modal name; auto). Slots: default (extra details), `trigger`.

### JS confirm
`await CF.confirm({ title, message, confirmText, cancelText, tone: 'danger'|'primary' })` → boolean (native `<dialog>`).
Declarative: `data-confirm="Pesan"` on a `<form>`, submit button or link (+ `data-confirm-title`,
`data-confirm-ok`, `data-confirm-cancel`, `data-confirm-tone`).

## JS helpers (`window.CF`)

- `CF.toast` — the toast store (`success|error|warning|info(message, title?)`, `push({...})`, `dismiss(id)`).
- `CF.colorMode` — `Alpine.store('colorMode')`: `mode`, `resolved`, `isDark`, `allowed`, `set('light'|'dark'|'system')`,
  `toggle()`, `cycle()`, `labels`. Emits `cf:theme` and `cf:color-mode-change`; stores guests' choice in
  `localStorage['cf-color-mode']`; POSTs `{color_mode}` to `<html data-color-mode-endpoint>` when present.
- `CF.confirm(options)` — see above.
- `CF.charts.init(root)`, `CF.charts.refresh()`.
- `CF.format.idr(1250000)` → 'Rp 1.250.000', `idrCompact(12400000)` → 'Rp 12,4 jt', `percent(94.12)` → '94,1%',
  `number(…)`, `date('2026-10-13')` → '13 Okt 2026'.
- '/' focuses the first `[data-search-shortcut]` input on the page.
- Alpine plugins available: `x-trap` (focus), `x-collapse`.

## Legacy components (restyled, same props/slots)

`x-primary-button`, `x-secondary-button`, `x-danger-button`, `x-text-input`, `x-input-label`, `x-input-error`,
`x-auth-session-status`, `x-modal`, `x-dropdown`, `x-dropdown-link`, `x-nav-link`, `x-responsive-nav-link`,
`x-data-table` (render callbacks are now escaped unless they return `Htmlable`), `x-user-avatar` (`gradient` ignored),
`x-photo-upload` (behaviour in `Alpine.data('photoUpload')`; drag & drop fixed; no CDN). Prefer `x-ui.*` for new code.
