{{--
    Settings groups: a horizontal, scrollable chip row on phones and tablets,
    a sticky vertical list on desktop. Read-only groups carry a lock icon.
--}}
<nav aria-label="Bagian pengaturan" class="min-w-0 lg:sticky lg:top-[calc(var(--cf-topbar-h)+1.5rem)] lg:self-start"
    x-data
    x-init="if (window.innerWidth < 1024) { $el.querySelector('[aria-current=page]')?.scrollIntoView({ block: 'nearest', inline: 'center' }) }">
    <p class="nav-label mb-2 hidden px-3 lg:block">Bagian</p>
    <ul role="list" class="scrollbar-none -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:-mx-6 sm:px-6 lg:mx-0 lg:flex-col lg:gap-0.5 lg:overflow-visible lg:px-0 lg:pb-0">
        @foreach ($groups as $item)
            @php $active = $item['key'] === $group['key']; @endphp
            <li class="shrink-0 lg:shrink">
                <a href="{{ route('admin.settings.edit', $item['key']) }}"
                    title="{{ $item['description'] }}"
                    @if ($active) aria-current="page" @endif
                    @class([
                        'relative flex min-h-11 items-center gap-2.5 whitespace-nowrap rounded-full border px-4 text-sm transition-colors duration-fast motion-reduce:transition-none',
                        'lg:min-h-10 lg:whitespace-normal lg:rounded-md lg:border-transparent lg:px-3',
                        'border-transparent bg-brand-soft font-semibold text-brand-soft-fg lg:bg-surface-2 lg:text-fg' => $active,
                        'border-line bg-surface text-fg-muted hover:text-fg lg:bg-transparent lg:hover:bg-surface-2' => ! $active,
                    ])>
                    @if ($active)
                        <span class="absolute inset-y-2 left-0 hidden w-0.5 rounded-full bg-indicator lg:block" aria-hidden="true"></span>
                    @endif
                    <x-ui.icon :name="$item['icon']" :class="$active ? 'size-[1.125rem]' : 'size-[1.125rem] text-fg-subtle'" />
                    <span class="min-w-0 flex-1">{{ $item['label'] }}</span>
                    @unless ($item['editable'])
                        <x-ui.icon name="m-lock-closed" class="size-4 shrink-0 text-fg-subtle" label="Hanya dapat dilihat" />
                    @endunless
                </a>
            </li>
        @endforeach
    </ul>
</nav>
