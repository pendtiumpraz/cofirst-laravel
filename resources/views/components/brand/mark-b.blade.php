{{--
    Logo mark, Versi B — "C1 caret" (ink tile, paper C, shu caret that also reads as "1").
    Props: size (px, default 32), tone (tile|mono), title (accessible name; omit when the
    mark sits next to visible text). In dark mode a 1px zinc keyline keeps the ink tile
    visible on the ink canvas (spec "Dark-UI" variant).
--}}
@props(['size' => 32, 'tone' => 'tile', 'title' => null])
<svg {{ $attributes->merge(['class' => 'shrink-0']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 64 64"
     xmlns="http://www.w3.org/2000/svg"
     @if ($title) role="img" aria-label="{{ $title }}" @else aria-hidden="true" focusable="false" @endif>
    @if ($tone === 'mono')
        <path d="M38.4 21.6A14 14 0 1 0 38.4 42.4" fill="none" stroke="currentColor" stroke-width="8" stroke-linecap="round"/>
        <rect x="46" y="20" width="7" height="24" rx="2" fill="currentColor"/>
    @else
        <rect width="64" height="64" rx="14" fill="#09090B"/>
        <rect class="hidden dark:inline" x="1" y="1" width="62" height="62" rx="13" fill="none" stroke="#3F3F46" stroke-width="2"/>
        <path d="M38.4 21.6A14 14 0 1 0 38.4 42.4" fill="none" stroke="#FAFAFA" stroke-width="8" stroke-linecap="round"/>
        <rect x="46" y="20" width="7" height="24" rx="2" fill="#EE4D1C"/>
    @endif
</svg>
