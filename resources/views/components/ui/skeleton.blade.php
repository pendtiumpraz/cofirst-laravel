{{--
    <x-ui.skeleton />                              3 text lines (:lines="n")
    <x-ui.skeleton variant="title" />              heading bar
    <x-ui.skeleton variant="avatar" />             circle
    <x-ui.skeleton variant="block" class="h-40" /> free-size block
    <x-ui.skeleton variant="card" />               card with title + lines
    <x-ui.skeleton variant="rows" :rows="5" />     table rows at the final row height
    Announced once as 'Memuat…' to screen readers; the shimmer stops under reduced motion.
--}}
@props([
    'variant' => 'text',
    'lines' => 3,
    'rows' => 5,
])

@php
    $lineCount = max(1, (int) $lines);
    $rowCount = max(1, (int) $rows);
@endphp

<div role="status" {{ $attributes->class('w-full') }}>
    <span class="sr-only">Memuat…</span>
    @switch($variant)
        @case('title')
            <span class="skeleton h-6 w-1/2" aria-hidden="true"></span>
            @break

        @case('avatar')
            <span class="skeleton size-10 rounded-full" aria-hidden="true"></span>
            @break

        @case('block')
            <span class="skeleton h-full min-h-[6rem] w-full" aria-hidden="true"></span>
            @break

        @case('card')
            <div class="card card-body space-y-3" aria-hidden="true">
                <span class="skeleton h-5 w-2/5"></span>
                <span class="skeleton h-3.5 w-full"></span>
                <span class="skeleton h-3.5 w-11/12"></span>
                <span class="skeleton h-3.5 w-3/5"></span>
            </div>
            @break

        @case('rows')
            <div class="divide-y divide-line" aria-hidden="true">
                @for ($i = 0; $i < $rowCount; $i++)
                    <div class="flex h-row items-center gap-4 px-4">
                        <span class="skeleton size-8 shrink-0 rounded-full"></span>
                        <span class="skeleton h-3.5 w-1/3"></span>
                        <span class="skeleton ml-auto h-3.5 w-16"></span>
                    </div>
                @endfor
            </div>
            @break

        @default
            <div class="space-y-2.5" aria-hidden="true">
                @for ($i = 0; $i < $lineCount; $i++)
                    <span class="skeleton h-3.5 {{ $lineCount > 1 && $i === $lineCount - 1 ? 'w-3/5' : 'w-full' }}"></span>
                @endfor
            </div>
    @endswitch
</div>
