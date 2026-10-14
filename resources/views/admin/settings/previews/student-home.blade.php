{{-- Student home: calm cards vs playful greeting card with progress ring and streak. --}}
<span class="flex h-28 flex-col gap-1.5 overflow-hidden rounded-md border border-line bg-canvas p-2.5">
    @if ($option === 'ceria')
        <span class="flex items-center gap-2 rounded-md bg-brand-soft p-2">
            <span class="min-w-0 flex-1">
                <span class="cf-display block font-display text-[13px] font-title leading-tight text-brand-soft-fg">Halo, <em>Andi</em>!</span>
                <span class="mt-1 flex gap-0.5">
                    @for ($i = 0; $i < 5; $i++)
                        <span @class(['size-1.5 rounded-full', 'bg-accent-solid' => $i < 4, 'bg-fg/20' => $i >= 4])></span>
                    @endfor
                </span>
            </span>
            <svg viewBox="0 0 36 36" class="size-9 shrink-0 -rotate-90" aria-hidden="true">
                <circle cx="18" cy="18" r="14" fill="none" stroke-width="5" class="stroke-fg/15" />
                <circle cx="18" cy="18" r="14" fill="none" stroke-width="5" stroke-linecap="round" stroke-dasharray="62 88" class="stroke-brand" />
            </svg>
        </span>
        <span class="grid flex-1 grid-cols-2 gap-1.5">
            <span class="rounded-md border border-line bg-surface"></span>
            <span class="rounded-md border border-line bg-surface"></span>
        </span>
    @else
        <span class="h-2 w-1/3 rounded-full bg-fg/50"></span>
        <span class="h-1 w-1/2 rounded-full bg-fg/15"></span>
        <span class="mt-0.5 flex flex-1 flex-col gap-1 rounded-md border border-line bg-surface p-1.5">
            <span class="h-1.5 w-2/5 rounded-full bg-fg/40"></span>
            <span class="h-1 w-3/4 rounded-full bg-fg/15"></span>
            <span class="mt-auto h-1 w-full rounded-full bg-fg/10">
                <span class="block h-1 w-2/3 rounded-full bg-brand"></span>
            </span>
        </span>
        <span class="h-4 rounded-md border border-line bg-surface"></span>
    @endif
</span>
