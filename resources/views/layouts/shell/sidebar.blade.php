{{--
    Desktop sidebar (appearance.dashboard_nav = sidebar, ≥1024px).
    Collapses to an icon rail; the state lives on <html data-sidebar="collapsed"> (set before
    paint from localStorage) so there is no layout jump. '[' toggles it from the keyboard.
--}}
<aside class="cf-sidebar fixed inset-y-0 left-0 z-40 hidden w-sidebar flex-col border-r border-line bg-sidebar lg:flex [[data-sidebar=collapsed]_&]:w-rail"
       aria-label="Navigasi samping">
    {{-- Reason: same height as the top bar so the lockup and the breadcrumbs share one centre line. --}}
    <div class="flex h-topbar shrink-0 items-center px-5 [[data-sidebar=collapsed]_&]:justify-center [[data-sidebar=collapsed]_&]:px-0">
        <a href="{{ $home }}" class="inline-flex rounded-md">
            {{-- Spec lockup sizes: A 32px mark / 20px wordmark, B 28px / 15px. --}}
            <span class="[[data-sidebar=collapsed]_&]:hidden"><x-brand.logo :size="$ui['theme'] === 'b' ? 'sm' : 'md'" /></span>
            <span class="hidden [[data-sidebar=collapsed]_&]:inline-flex"><x-brand.logo type="mark" :size="$ui['theme'] === 'b' ? 28 : 32" /></span>
            <span class="sr-only">— ke beranda</span>
        </a>
    </div>

    @if (count($roles) > 1)
        <div class="relative px-3 pb-3 [[data-sidebar=collapsed]_&]:hidden" x-data="cfMenu" @keydown.escape.stop="close()" @click.outside="close(false)">
            <button type="button" x-ref="button" @click="toggle()" :aria-expanded="open.toString()" aria-expanded="false" aria-controls="cf-sidebar-roles"
                    class="flex min-h-10 w-full items-center gap-2 rounded-md border border-line bg-surface px-3 text-left text-xs text-fg-muted transition-colors hover:border-line-strong">
                <x-heroicon-o-identification class="size-4 shrink-0 text-fg-subtle" aria-hidden="true" />
                <span class="min-w-0 flex-1 truncate">Sebagai <strong class="font-semibold text-fg">{{ \App\Support\Navigation\Navigation::ROLE_LABELS[$role] ?? $role }}</strong></span>
                <x-heroicon-m-chevron-up-down class="size-4 shrink-0 text-fg-subtle" aria-hidden="true" />
            </button>
            <div id="cf-sidebar-roles" x-ref="panel" x-show="open" x-cloak x-transition.opacity.duration.150ms
                 class="absolute inset-x-3 top-full z-50 mt-1 rounded-lg border border-line-strong bg-surface p-1.5 shadow-2">
                @include('layouts.shell.role-switcher', ['roles' => $roles, 'role' => $role])
            </div>
        </div>
    @endif

    <nav class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 pb-4 [scrollbar-width:thin] [[data-sidebar=collapsed]_&]:[scrollbar-width:none]" aria-label="Menu utama">
        @include('layouts.shell.nav-list', ['sections' => $sections, 'rail' => true, 'filterable' => false, 'idPrefix' => 'sb'])
    </nav>

    <div class="shrink-0 space-y-0.5 border-t border-line px-3 py-3">
        <ul role="list" class="space-y-0.5">
            @foreach ($account as $item)
                @include('layouts.shell.nav-item', ['item' => $item, 'rail' => true, 'filterable' => false])
            @endforeach
        </ul>
        <button type="button" x-data="cfSidebarToggle" @click="toggle()" :aria-pressed="collapsed.toString()" aria-pressed="false"
                class="flex min-h-10 w-full items-center gap-3 rounded-md px-3 text-sm font-medium text-fg-subtle transition-colors hover:bg-surface-2 hover:text-fg [[data-sidebar=collapsed]_&]:justify-center [[data-sidebar=collapsed]_&]:px-0"
                title="Ciutkan atau lebarkan menu ( [ )">
            <x-heroicon-o-chevron-double-left class="size-5 shrink-0 transition-transform motion-reduce:transition-none [[data-sidebar=collapsed]_&]:rotate-180" aria-hidden="true" />
            <span class="[[data-sidebar=collapsed]_&]:sr-only">Ciutkan menu</span>
        </button>
    </div>
</aside>
