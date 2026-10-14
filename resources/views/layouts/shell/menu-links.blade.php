{{-- Compact icon + label link list used inside dropdown panels. Variables: $items. --}}
<ul role="list" class="space-y-0.5">
    @foreach ($items as $item)
        <li>
            <a href="{{ $item['url'] }}" @if ($item['is_active']) aria-current="page" @endif
               @class([
                   'flex min-h-10 items-center gap-3 rounded-md px-2.5 text-sm transition-colors',
                   'bg-surface-2 font-semibold text-fg' => $item['is_active'],
                   'text-fg-muted hover:bg-surface-2 hover:text-fg' => ! $item['is_active'],
               ])>
                @svg('heroicon-o-'.$item['icon'], 'size-5 shrink-0 text-fg-subtle', ['aria-hidden' => 'true'])
                <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                @if ($item['badge'])
                    <span class="rounded-full bg-accent-solid px-1.5 text-[11px] font-semibold leading-5 text-accent-fg">{{ $item['badge'] }}</span>
                @endif
            </a>
        </li>
    @endforeach
</ul>
