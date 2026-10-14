{{-- Sign-in page wireframes: split brand panel vs centred card. --}}
@php
    $mark = 'brand.mark-'.\App\Support\Navigation\Appearance::logoVariant();
@endphp
@if ($option === 'centered')
    <span class="flex h-28 flex-col items-center justify-center gap-1.5 rounded-md border border-line bg-canvas">
        <x-dynamic-component :component="$mark" :size="14" />
        <span class="flex w-24 flex-col gap-1 rounded-sm border border-line bg-surface p-2">
            <span class="h-1.5 w-1/2 rounded-full bg-fg/50"></span>
            <span class="h-3 rounded-sm border border-line-input bg-surface"></span>
            <span class="h-3 rounded-sm border border-line-input bg-surface"></span>
            <span class="h-3 rounded-sm bg-brand"></span>
        </span>
    </span>
@else
    <span class="flex h-28 overflow-hidden rounded-md border border-line bg-canvas">
        <span class="flex w-2/5 shrink-0 flex-col justify-between bg-brand-soft p-2">
            <x-dynamic-component :component="$mark" :size="14" />
            <span class="flex flex-col gap-1">
                <span class="h-1.5 w-11/12 rounded-full bg-brand-soft-fg/50"></span>
                <span class="h-1.5 w-2/3 rounded-full bg-brand-soft-fg/50"></span>
                <span class="h-1 w-1/2 rounded-full bg-brand-soft-fg/25"></span>
            </span>
        </span>
        <span class="flex flex-1 flex-col justify-center gap-1 px-3">
            <span class="h-1.5 w-1/2 rounded-full bg-fg/50"></span>
            <span class="h-3 rounded-sm border border-line-input bg-surface"></span>
            <span class="h-3 rounded-sm border border-line-input bg-surface"></span>
            <span class="h-3 rounded-sm bg-brand"></span>
        </span>
    </span>
@endif
