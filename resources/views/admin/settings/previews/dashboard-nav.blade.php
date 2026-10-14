{{-- Desktop shell wireframes: grouped sidebar vs top navigation bar. --}}
@php
    $cards = $option === 'topnav' ? 3 : 2;
@endphp
<span @class(['flex h-28 overflow-hidden rounded-md border border-line bg-canvas', 'flex-col' => $option === 'topnav'])>
    @if ($option === 'topnav')
        <span class="flex h-6 shrink-0 items-center gap-2 border-b border-line bg-surface px-2">
            <span class="size-2.5 rounded-sm bg-accent-solid"></span>
            <span class="relative h-1.5 w-6 rounded-full bg-fg/50">
                <span class="absolute -bottom-[7px] left-0 h-0.5 w-full rounded-full bg-indicator"></span>
            </span>
            <span class="h-1.5 w-5 rounded-full bg-fg/15"></span>
            <span class="h-1.5 w-6 rounded-full bg-fg/15"></span>
            <span class="h-1.5 w-4 rounded-full bg-fg/15"></span>
            <span class="ml-auto size-3 rounded-full bg-fg/20"></span>
        </span>
    @else
        <span class="flex w-1/4 shrink-0 flex-col gap-1.5 border-r border-line bg-sidebar p-2">
            <span class="size-2.5 rounded-sm bg-accent-solid"></span>
            <span class="mt-1 flex items-center gap-1">
                <span class="h-2.5 w-0.5 rounded-full bg-indicator"></span>
                <span class="h-1.5 flex-1 rounded-full bg-fg/50"></span>
            </span>
            <span class="h-1.5 w-4/5 rounded-full bg-fg/15"></span>
            <span class="h-1.5 w-3/5 rounded-full bg-fg/15"></span>
            <span class="mt-1 h-1 w-1/2 rounded-full bg-fg/10"></span>
            <span class="h-1.5 w-4/5 rounded-full bg-fg/15"></span>
        </span>
    @endif
    <span class="flex min-w-0 flex-1 flex-col gap-1.5 p-2">
        <span class="h-2 w-1/3 rounded-full bg-fg/50"></span>
        <span @class(['grid gap-1.5', 'grid-cols-3' => $cards === 3, 'grid-cols-2' => $cards === 2])>
            @for ($i = 0; $i < $cards; $i++)
                <span class="h-7 rounded-sm border border-line bg-surface"></span>
            @endfor
        </span>
        <span class="min-h-0 flex-1 rounded-sm border border-line bg-surface"></span>
    </span>
</span>
