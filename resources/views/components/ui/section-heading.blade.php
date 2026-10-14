{{--
    <x-ui.section-heading title="Perlu tindakan" description="Hal yang menunggu Anda hari ini." number="01">
        <x-slot:actions><x-ui.button variant="link" size="sm" arrow href="…">Lihat semua</x-ui.button></x-slot:actions>
    </x-ui.section-heading>
    number: optional editorial section number ('01') · eyebrow · level: 2 (default) | 3 | 4
--}}
@props([
    'title',
    'description' => null,
    'eyebrow' => null,
    'number' => null,
    'level' => 2,
])

@php
    $heading = 'h'.max(2, min(4, (int) $level));
@endphp

<div {{ $attributes->class('section-heading mb-4') }}>
    <div class="min-w-0">
        @if (filled($eyebrow) || filled($number))
            <p class="eyebrow mb-1.5 flex items-center gap-2">
                @if (filled($number))
                    <span class="section-num text-sm normal-case tracking-normal">{{ $number }}</span>
                @endif
                @if (filled($number) && filled($eyebrow))
                    <span aria-hidden="true">/</span>
                @endif
                @if (filled($eyebrow))
                    <span>{{ $eyebrow }}</span>
                @endif
            </p>
        @endif
        <{{ $heading }} class="section-title">{{ $title }}</{{ $heading }}>
        @if (filled($description))
            <p class="mt-1 text-fg-muted">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
