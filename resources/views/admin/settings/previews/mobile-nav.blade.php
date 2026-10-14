{{-- Phone wireframes: bottom tab bar vs side drawer. --}}
<span class="flex h-28 items-center justify-center rounded-md border border-line bg-canvas">
    <span class="relative flex h-[6.25rem] w-14 flex-col overflow-hidden rounded-[0.65rem] border-2 border-line-strong bg-surface">
        <span class="flex h-3.5 shrink-0 items-center gap-1 border-b border-line px-1">
            @if ($option === 'drawer')
                <span class="flex flex-col gap-px">
                    <span class="h-px w-2 bg-fg/70"></span>
                    <span class="h-px w-2 bg-fg/70"></span>
                    <span class="h-px w-2 bg-fg/70"></span>
                </span>
            @endif
            <span class="h-1 w-5 rounded-full bg-fg/40"></span>
        </span>
        <span class="flex flex-1 flex-col gap-1 p-1">
            <span class="h-4 rounded-sm border border-line bg-canvas"></span>
            <span class="h-1 w-4/5 rounded-full bg-fg/15"></span>
            <span class="h-1 w-3/5 rounded-full bg-fg/15"></span>
        </span>

        @if ($option === 'drawer')
            <span class="absolute inset-0 bg-fg/20"></span>
            <span class="absolute inset-y-0 left-0 flex w-2/3 flex-col gap-1 border-r border-line bg-surface p-1.5">
                <span class="size-2 rounded-sm bg-accent-solid"></span>
                <span class="mt-0.5 flex items-center gap-0.5">
                    <span class="h-2 w-px bg-indicator"></span>
                    <span class="h-1 flex-1 rounded-full bg-fg/50"></span>
                </span>
                <span class="h-1 w-4/5 rounded-full bg-fg/15"></span>
                <span class="h-1 w-3/5 rounded-full bg-fg/15"></span>
                <span class="h-1 w-4/5 rounded-full bg-fg/15"></span>
            </span>
        @else
            <span class="grid h-4 shrink-0 grid-cols-5 items-center justify-items-center border-t border-line">
                <span class="size-1.5 rounded-full bg-indicator"></span>
                @for ($i = 0; $i < 4; $i++)
                    <span class="size-1.5 rounded-full bg-fg/25"></span>
                @endfor
            </span>
        @endif
    </span>
</span>
