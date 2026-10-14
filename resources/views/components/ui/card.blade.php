{{--
    <x-ui.card title="Kelas berikutnya" subtitle="Senin, 13 Okt" eyebrow="Hari ini">
        <x-slot:actions><x-ui.button variant="link" size="sm" :href="…" arrow>Lihat semua</x-ui.button></x-slot:actions>
        …body…
        <x-slot:footer>…</x-slot:footer>
    </x-ui.card>
    padding: md (default) | sm | none (flush body for tables/lists; header gets a rule)
    variant: default | interactive | highlight | hero | callout | inset | code
    href: makes the title a stretched link (whole card clickable) and implies interactive.
--}}
@props([
    'title' => null,
    'subtitle' => null,
    'eyebrow' => null,
    'padding' => 'md',
    'variant' => 'default',
    'href' => null,
    'as' => 'div',
    'level' => 2,
])

@php
    $variantClass = match ($href ? 'interactive' : $variant) {
        'interactive' => 'card-interactive',
        'highlight' => 'card-highlight',
        'hero' => 'card-hero',
        'callout' => 'card-callout',
        'inset' => 'card-inset',
        'code' => 'card-code',
        default => null,
    };

    $tag = in_array($as, ['div', 'section', 'article', 'aside', 'li'], true) ? $as : 'div';
    $heading = 'h'.max(2, min(4, (int) $level));
    $hasHeader = filled($title) || filled($subtitle) || filled($eyebrow) || isset($actions) || isset($header);
    $bodyClass = match ($padding) {
        'none' => null,
        'sm' => 'card-body-sm',
        default => 'card-body',
    };
@endphp

<{{ $tag }} {{ $attributes->class(['card', $variantClass]) }}>
    @if ($bodyClass)
        <div class="{{ $bodyClass }}">
    @endif

    @if ($hasHeader)
        <div class="{{ $bodyClass ? 'card-header' : 'card-header-flush' }}">
            @isset($header)
                {{ $header }}
            @else
                <div class="min-w-0">
                    @if (filled($eyebrow))
                        <p class="eyebrow mb-1">{{ $eyebrow }}</p>
                    @endif
                    @if (filled($title))
                        <{{ $heading }} class="card-title">
                            @if ($href)
                                <a href="{{ $href }}" class="card-link">{{ $title }}</a>
                            @else
                                {{ $title }}
                            @endif
                        </{{ $heading }}>
                    @endif
                    @if (filled($subtitle))
                        <p class="card-subtitle">{{ $subtitle }}</p>
                    @endif
                </div>
            @endisset
            @isset($actions)
                <div class="relative z-[1] flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    @if ($bodyClass && $hasHeader)
        <div class="card-content">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif

    @if ($bodyClass)
        </div>
    @endif

    @isset($footer)
        <div {{ $footer->attributes->class('card-footer') }}>{{ $footer }}</div>
    @endisset
</{{ $tag }}>
