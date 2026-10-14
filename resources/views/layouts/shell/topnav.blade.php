{{--
    Desktop top navigation (appearance.dashboard_nav = topnav, ≥1024px).
    Roles with ≤ 8 destinations get flat links. Larger menus: the unlabelled first group as
    links, then one dropdown per group (2 at lg, 4 at xl) and the rest in a "Lainnya" mega menu.
--}}
@php
    $flat = \App\Support\Navigation\Navigation::flatten($sections);
    $isFlat = count($flat) <= 8;
    $direct = $isFlat ? $flat : (isset($sections[0]) && $sections[0]['label'] === null ? $sections[0]['items'] : []);
    $groups = $isFlat ? [] : array_values(array_filter($sections, fn ($s) => $s['label'] !== null));
    $short = fn (string $label) => explode(' ', $label)[0];
    $link = 'relative inline-flex h-topbar items-center gap-2 whitespace-nowrap px-3 text-sm font-medium transition-colors';
    $indicator = 'absolute inset-x-3 bottom-0 h-0.5 rounded-full bg-indicator';
    $overflowActive = collect(array_slice($groups, 2))->flatMap(fn ($g) => $g['items'])->contains('is_active', true);
@endphp
<header class="sticky top-0 z-30 hidden border-b border-line bg-canvas lg:block">
    <div class="mx-auto flex h-topbar max-w-app items-center gap-4 px-6 xl:px-8">
        <a href="{{ $home }}" class="mr-2 inline-flex shrink-0 rounded-md">
            <x-brand.logo :size="$ui['theme'] === 'b' ? 'sm' : 'md'" />
            <span class="sr-only">— ke beranda</span>
        </a>

        <nav aria-label="Menu utama" class="flex min-w-0 flex-1 items-center">
            <ul role="list" class="flex items-center">
                @foreach ($direct as $item)
                    <li>
                        <a href="{{ $item['url'] }}" @if ($item['is_active']) aria-current="page" @endif
                           @class([$link, 'text-fg' => $item['is_active'], 'text-fg-muted hover:text-fg' => ! $item['is_active']])>
                            {{ $item['label'] }}
                            @if ($item['badge'])<span class="rounded-full bg-accent-solid px-1.5 text-[11px] font-semibold leading-5 text-accent-fg">{{ $item['badge'] }}</span>@endif
                            @if ($item['is_active'])<span class="{{ $indicator }}" aria-hidden="true"></span>@endif
                        </a>
                    </li>
                @endforeach

                @foreach (array_slice($groups, 0, 4) as $index => $group)
                    @php $groupActive = collect($group['items'])->contains('is_active', true); @endphp
                    <li @class(['relative', 'hidden xl:block' => $index >= 2]) x-data="cfMenu" @keydown.escape.stop="close()" @click.outside="close(false)">
                        <button type="button" x-ref="button" @click="toggle()" :aria-expanded="open.toString()" aria-expanded="false" aria-controls="cf-top-{{ $group['key'] }}"
                                @class([$link, 'text-fg' => $groupActive, 'text-fg-muted hover:text-fg' => ! $groupActive])>
                            {{ $short($group['label']) }}
                            <x-heroicon-m-chevron-down class="size-4 text-fg-subtle" aria-hidden="true" />
                            @if ($groupActive)<span class="{{ $indicator }}" aria-hidden="true"></span>@endif
                        </button>
                        <div id="cf-top-{{ $group['key'] }}" x-ref="panel" x-show="open" x-cloak x-transition.opacity.duration.150ms
                             class="absolute left-0 top-full z-50 mt-px w-72 rounded-lg border border-line-strong bg-surface p-1.5 shadow-2">
                            <p class="nav-label px-2.5 pb-1 pt-1.5">{{ $group['label'] }}</p>
                            @include('layouts.shell.menu-links', ['items' => $group['items']])
                        </div>
                    </li>
                @endforeach

                @if (count($groups) > 2)
                    <li @class(['relative', 'xl:hidden' => count($groups) <= 4]) x-data="cfMenu" @keydown.escape.stop="close()" @click.outside="close(false)">
                        <button type="button" x-ref="button" @click="toggle()" :aria-expanded="open.toString()" aria-expanded="false" aria-controls="cf-top-more"
                                @class([$link, 'text-fg' => $overflowActive, 'text-fg-muted hover:text-fg' => ! $overflowActive])>
                            Lainnya
                            <x-heroicon-m-chevron-down class="size-4 text-fg-subtle" aria-hidden="true" />
                        </button>
                        @php
                            // Reason: size the mega menu to its content — one column per group, max three.
                            $moreLg = count($groups) - 2;
                            $moreXl = max(0, count($groups) - 4);
                        @endphp
                        <div id="cf-top-more" x-ref="panel" x-show="open" x-cloak x-transition.opacity.duration.150ms
                             @class([
                                 'absolute left-0 top-full z-50 mt-px grid w-max max-w-[calc(100vw-3rem)] gap-x-4 gap-y-3 rounded-lg border border-line-strong bg-surface p-3 shadow-2',
                                 'grid-cols-3' => $moreLg >= 3,
                                 'grid-cols-2' => $moreLg === 2,
                                 'grid-cols-1' => $moreLg <= 1,
                                 'xl:grid-cols-3' => $moreXl >= 3,
                                 'xl:grid-cols-2' => $moreXl === 2,
                                 'xl:grid-cols-1' => $moreXl === 1,
                             ])>
                            @foreach (array_slice($groups, 2) as $index => $group)
                                <div @class(['w-56', 'xl:hidden' => $index < 2])>
                                    <p class="nav-label px-2.5 pb-1 pt-1.5">{{ $group['label'] }}</p>
                                    @include('layouts.shell.menu-links', ['items' => $group['items']])
                                </div>
                            @endforeach
                        </div>
                    </li>
                @endif
            </ul>
        </nav>

        @include('layouts.shell.actions', ['compactActions' => true])
    </div>
</header>
