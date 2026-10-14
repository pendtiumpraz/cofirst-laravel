{{--
    Desktop top bar for the sidebar variant: breadcrumbs left, actions right.
--}}
@php
    $roleLabel = \App\Support\Navigation\Navigation::ROLE_LABELS[$role] ?? 'Dasbor';
    $crumbs = [['label' => $roleLabel, 'url' => $home]];
    if ($current && $current['label'] !== $pageTitle && $current['route'] !== \App\Support\Navigation\Navigation::dashboardRoute($role)) {
        $crumbs[] = ['label' => $current['label'], 'url' => $current['url']];
    }
@endphp
<header class="sticky top-0 z-30 hidden h-topbar items-center gap-4 border-b border-line bg-canvas px-6 lg:flex xl:px-8">
    <nav aria-label="Lokasi halaman" class="min-w-0 flex-1">
        <ol role="list" class="flex min-w-0 items-center gap-2 text-[13px] text-fg-subtle">
            @foreach ($crumbs as $crumb)
                <li class="flex shrink-0 items-center gap-2">
                    <a href="{{ $crumb['url'] }}" class="rounded-sm hover:text-fg">{{ $crumb['label'] }}</a>
                    <span class="text-line-strong" aria-hidden="true">/</span>
                </li>
            @endforeach
            <li class="min-w-0 truncate font-medium text-fg" aria-current="page">{{ $pageTitle }}</li>
        </ol>
    </nav>
    @include('layouts.shell.actions')
</header>
