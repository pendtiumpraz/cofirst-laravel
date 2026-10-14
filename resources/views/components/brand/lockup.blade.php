{{--
    Brand lockup: mark + live-text wordmark (+ optional descriptor).

    Props:
      variant     a|b|auto|null — null reads setting('appearance.logo_variant'); auto follows the theme
      size        sm (28px mark) | md (32px, default) | lg (40px) | xl (56px)
      layout      horizontal (default) | stacked
      descriptor  bool — A: "SEKOLAH CODING · MENTOR INDUSTRI JEPANG", B: domain in mono
      name        wordmark text (default setting('general.site_name'))

    The wrapping link/heading is the caller's job; the mark is aria-hidden because the
    wordmark is real text.
--}}
@props(['variant' => null, 'size' => 'md', 'layout' => 'horizontal', 'descriptor' => false, 'name' => null])
@php
    $v = \App\Support\Navigation\Appearance::logoVariant($variant);
    $name = trim((string) ($name ?? setting('general.site_name', 'Coding First'))) ?: 'Coding First';
    $px = ['sm' => 28, 'md' => 32, 'lg' => 40, 'xl' => 56][$size] ?? 32;
    $gap = ['sm' => 'gap-2.5', 'md' => 'gap-[11px]', 'lg' => 'gap-3.5', 'xl' => 'gap-5'][$size] ?? 'gap-[11px]';
    $text = $v === 'a'
        ? (['sm' => 'text-[17px]', 'md' => 'text-[20px]', 'lg' => 'text-[24px]', 'xl' => 'text-[32px]'][$size] ?? 'text-[20px]')
        : (['sm' => 'text-[15px]', 'md' => 'text-[18px]', 'lg' => 'text-[22px]', 'xl' => 'text-[30px]'][$size] ?? 'text-[18px]');
    $wordmark = $v === 'a'
        ? 'font-display font-medium tracking-[-0.015em]'
        : 'font-sans font-semibold tracking-[-0.025em]';
    // Reason: Versi A sets the last word ("First") in italic — the editorial stress of the brand.
    $lastSpace = mb_strrpos($name, ' ');
    $head = $lastSpace === false ? $name : mb_substr($name, 0, $lastSpace);
    $tail = $lastSpace === false ? '' : mb_substr($name, $lastSpace + 1);
    $domain = \Illuminate\Support\Str::after((string) setting('general.contact_email', 'hello@codingfirst.id'), '@') ?: 'codingfirst.id';
    $stacked = $layout === 'stacked';
@endphp
<span {{ $attributes->class([
    'inline-flex text-fg',
    $stacked ? 'flex-col items-center text-center gap-3' : 'items-center '.$gap,
]) }}>
    <x-dynamic-component :component="'brand.mark-'.$v" :size="$px" />
    {{-- Reason: A's descriptor sits under the wordmark (editorial stack); B's follows a divider. --}}
    <span @class([
        'flex',
        'flex-col items-center gap-2' => $stacked,
        'flex-col items-start gap-1.5' => ! $stacked && $descriptor && $v === 'a',
        'items-center gap-3' => ! $stacked && ! ($descriptor && $v === 'a'),
    ])>
        <span class="{{ $wordmark }} {{ $text }} leading-none whitespace-nowrap">
            @if ($v === 'a' && $tail !== '')
                {{ $head }} <span class="italic">{{ $tail }}</span>
            @else
                {{ $name }}
            @endif
        </span>
        @if ($descriptor)
            @if ($v === 'a')
                <span class="whitespace-nowrap text-[10.5px] font-semibold uppercase leading-tight tracking-[0.14em] text-fg-subtle">Sekolah coding · Mentor industri Jepang</span>
            @else
                @unless ($stacked)<span class="h-4 w-px bg-line-strong" aria-hidden="true"></span>@endunless
                <span class="font-mono text-xs font-medium text-fg-subtle">{{ $domain }}</span>
            @endif
        @endif
    </span>
</span>
