{{--
    One navigation link (with optional one-level children).
    Variables: $item (resolved Navigation item), $rail (bool), $filterable (bool), $nested (bool).
--}}
@php
    $rail ??= false;
    $filterable ??= false;
    $nested ??= false;
    $active = (bool) $item['is_active'];
    $badge = $item['badge'] ?? null;
@endphp
<li @if ($filterable) data-label="{{ mb_strtolower($item['label'].' '.($item['section'] ?? '')) }}" x-show="!q || $el.dataset.label.includes(q.toLowerCase())" @endif>
    <a href="{{ $item['url'] }}"
       @if ($active) aria-current="page" @endif
       @if ($rail) title="{{ $item['label'] }}" @endif
       @class([
           'group relative flex min-h-nav-item items-center gap-3 rounded-md px-3 text-sm transition-colors duration-150 motion-reduce:transition-none [@media(pointer:coarse)]:min-h-11',
           'pl-10' => $nested,
           '[[data-sidebar=collapsed]_.cf-sidebar_&]:justify-center [[data-sidebar=collapsed]_.cf-sidebar_&]:px-0' => $rail,
           'bg-surface-2 text-fg font-nav-active' => $active,
           'font-medium text-fg-muted hover:bg-surface-2 hover:text-fg' => ! $active,
       ])>
        @if ($active)
            <span class="absolute inset-y-2 left-0 w-0.5 rounded-full bg-indicator" aria-hidden="true"></span>
        @endif
        @svg('heroicon-o-'.$item['icon'], 'size-5 shrink-0 '.($active ? 'text-fg' : 'text-fg-subtle group-hover:text-fg-muted'), ['aria-hidden' => 'true'])
        <span @class(['min-w-0 flex-1 truncate', '[[data-sidebar=collapsed]_.cf-sidebar_&]:sr-only' => $rail])>{{ $item['label'] }}</span>
        @if ($badge)
            <span @class([
                'ml-auto inline-flex min-w-5 items-center justify-center rounded-full bg-accent-solid px-1.5 text-[11px] font-semibold leading-5 text-accent-fg tabular-nums',
                '[[data-sidebar=collapsed]_.cf-sidebar_&]:absolute [[data-sidebar=collapsed]_.cf-sidebar_&]:right-2 [[data-sidebar=collapsed]_.cf-sidebar_&]:top-1 [[data-sidebar=collapsed]_.cf-sidebar_&]:min-w-4 [[data-sidebar=collapsed]_.cf-sidebar_&]:px-1 [[data-sidebar=collapsed]_.cf-sidebar_&]:text-[10px] [[data-sidebar=collapsed]_.cf-sidebar_&]:leading-4' => $rail,
            ])>
                {{ $badge > 99 ? '99+' : $badge }}<span class="sr-only"> belum dibaca</span>
            </span>
        @endif
    </a>
    @if (! empty($item['children']))
        <ul role="list" @class(['mt-0.5 space-y-0.5', '[[data-sidebar=collapsed]_.cf-sidebar_&]:hidden' => $rail])>
            @foreach ($item['children'] as $child)
                @include('layouts.shell.nav-item', ['item' => $child, 'rail' => false, 'filterable' => $filterable, 'nested' => true])
            @endforeach
        </ul>
    @endif
</li>
