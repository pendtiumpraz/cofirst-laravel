{{--
    Dashboard shell. Serves both conventions used by the role views:
      • <x-app-layout title="…"> <x-slot name="header">…</x-slot> … </x-app-layout>
      • @extends('layouts.app') + @section('title'), @section('header'), @section('content')
    Desktop navigation: setting('appearance.dashboard_nav') = sidebar|topnav.
    Below 1024px both share one mobile shell: app bar + bottom tabs (or a drawer),
    per setting('appearance.mobile_nav'). Menus come from App\Support\Navigation\Navigation.
    Views may @push('styles') and @push('scripts').
--}}
@php
    use App\Support\Navigation\Appearance;
    use App\Support\Navigation\Navigation;
    use Illuminate\Contracts\Support\Htmlable;

    $user = auth()->user();
    $ui = Appearance::state($user);
    $role = $user ? Navigation::activeRole($user) : null;
    $roles = $user ? Navigation::rolesOf($user) : [];
    $sections = $user ? Navigation::for($user, $role) : [];
    $account = $user ? Navigation::accountItems($user) : [];
    $tabs = $user && $ui['mobile_nav'] === 'tabs' ? Navigation::mobileTabs($user, $role) : [];
    $current = Navigation::current($sections);
    $home = route(Navigation::dashboardRoute($role));

    // Reason: in @extends mode the child's data is in scope too, so only accept a real slot / string.
    $headerSlot = isset($header) && $header instanceof Htmlable && trim((string) $header->toHtml()) !== '' ? $header : null;
    $pageTitle = (isset($title) && is_string($title) && trim($title) !== '' ? trim($title) : null)
        ?? (trim(strip_tags($__env->yieldContent('title'))) ?: null)
        ?? ($headerSlot ? (Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($headerSlot->toHtml()))), 60, '…') ?: null) : null)
        ?? ($current['label'] ?? 'Dasbor');

    $shell = compact('user', 'ui', 'role', 'roles', 'sections', 'account', 'tabs', 'current', 'home', 'pageTitle');
    $sidebar = $ui['nav'] === 'sidebar';
@endphp
<!DOCTYPE html>
<html {!! Appearance::htmlAttributes($user) !!}>
<head>
    @include('layouts.partials.head', ['pageTitle' => $pageTitle, 'robots' => 'noindex, nofollow'])
</head>
<body class="min-h-dvh bg-canvas font-sans text-fg antialiased">
    @include('layouts.partials.skip-link')

    @if ($sidebar)
        @include('layouts.shell.sidebar', $shell)
    @endif

    <div @class([
        'flex min-h-dvh flex-col',
        'lg:pl-sidebar lg:[[data-sidebar=collapsed]_&]:pl-rail' => $sidebar,
    ])>
        @include('layouts.shell.notices')
        @include('layouts.shell.mobile-bar', $shell)

        @if ($sidebar)
            @include('layouts.shell.topbar', $shell)
        @else
            @include('layouts.shell.topnav', $shell)
        @endif

        <main id="konten" tabindex="-1" @class([
            'flex-1 focus:outline-none',
            'pb-[calc(var(--cf-tabbar-h)+env(safe-area-inset-bottom))] lg:pb-0' => $tabs !== [],
        ])>
            <div class="mx-auto w-full max-w-app px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if ($headerSlot || View::hasSection('header'))
                    {{-- Legacy header slots (<h2 class="font-semibold text-xl text-gray-800">) are restyled as the page title. --}}
                    <header class="mb-6 border-b border-line pb-5 lg:mb-8 [&_h1]:font-display [&_h1]:text-page-title [&_h1]:font-title [&_h1]:tracking-display [&_h1]:text-fg [&_h2]:font-display [&_h2]:text-page-title [&_h2]:font-title [&_h2]:tracking-display [&_h2]:text-fg">
                        {{ $headerSlot ?? '' }}
                        @yield('header')
                    </header>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>

    @if ($user)
        @if ($tabs !== [])
            @include('layouts.shell.bottom-tabs', $shell)
            @include('layouts.shell.more-sheet', $shell)
        @else
            @include('layouts.shell.drawer', $shell)
        @endif
        @include('layouts.shell.command-palette', $shell)
        @include('pwa.partials.install-prompt')
    @endif

    @include('layouts.shell.flash')
    @include('layouts.shell.scripts')
    @stack('scripts')
</body>
</html>
