{{-- Mini dashboard drawn with the tokens of version $option (nested data-theme re-declares them, also in dark mode). --}}
<span data-theme="{{ $option }}" class="flex h-28 overflow-hidden rounded-md border border-line bg-canvas text-fg">
    <span class="flex w-14 shrink-0 flex-col gap-1.5 border-r border-line bg-sidebar p-2">
        @if ($option === 'b')
            <x-brand.mark-b size="16" />
        @else
            <x-brand.mark-a size="16" />
        @endif
        <span class="mt-1 flex items-center gap-1">
            <span class="h-2.5 w-0.5 rounded-full bg-indicator"></span>
            <span class="h-1.5 flex-1 rounded-full bg-fg/40"></span>
        </span>
        <span class="h-1.5 w-4/5 rounded-full bg-fg/15"></span>
        <span class="h-1.5 w-3/5 rounded-full bg-fg/15"></span>
        <span class="h-1.5 w-4/5 rounded-full bg-fg/15"></span>
    </span>
    <span class="flex min-w-0 flex-1 flex-col gap-1.5 p-2.5">
        <span class="cf-display font-display text-[15px] font-title leading-none tracking-display text-fg">Selamat <em>pagi</em></span>
        <span class="grid grid-cols-3 gap-1">
            @foreach (['312', '94%', '4,9'] as $number)
                <span class="min-w-0 rounded-sm border border-line bg-surface px-1.5 py-1">
                    <span class="font-num block text-[11px] leading-tight text-fg">{{ $number }}</span>
                    <span class="mt-0.5 block h-1 w-3/4 rounded-full bg-fg/15"></span>
                </span>
            @endforeach
        </span>
        <span class="mt-auto flex items-center gap-1.5">
            <span class="h-4 w-12 rounded-sm bg-brand"></span>
            <span class="h-4 w-9 rounded-sm border border-line-strong bg-surface"></span>
            <span class="ml-auto rounded-badge bg-success-soft px-1.5 text-[8px] font-semibold leading-4 text-success">Lunas</span>
            <span class="size-1.5 rounded-full bg-accent-solid"></span>
        </span>
    </span>
</span>
