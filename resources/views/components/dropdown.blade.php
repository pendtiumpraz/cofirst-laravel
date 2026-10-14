{{-- Legacy Breeze dropdown (slots: trigger, content), restyled. Prefer <x-ui.dropdown>. --}}
@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'p-1 bg-surface'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false" @keydown.escape.window="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-cf duration-fast"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition duration-fast"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute z-50 mt-2 {{ $width }} rounded-lg border border-line-strong shadow-2 {{ $alignmentClasses }}"
            style="display: none;"
            @click="open = false">
        <div class="rounded-lg text-sm text-fg {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
