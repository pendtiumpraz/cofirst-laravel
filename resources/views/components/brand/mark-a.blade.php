{{--
    Logo mark, Versi A — "CF dalam hanko" (vermilion seal, knocked-out C and F).
    Props: size (px, default 32), tone (seal|mono), title (accessible name; omit when
    the mark sits next to visible text — it is then aria-hidden).
    The seal colour is a brand constant and never changes with theme or dark mode.
--}}
@props(['size' => 32, 'tone' => 'seal', 'title' => null])
<svg {{ $attributes->merge(['class' => 'shrink-0']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 64 64"
     xmlns="http://www.w3.org/2000/svg"
     @if ($title) role="img" aria-label="{{ $title }}" @else aria-hidden="true" focusable="false" @endif>
    @if ($tone === 'mono')
        <path d="M33.39 23.19A11.5 11.5 0 1 0 33.39 40.81" fill="none" stroke="currentColor" stroke-width="8"/>
        <path d="M40 18h13v6.5h-6v4.5h4.5v6.5h-4.5V46h-7z" fill="currentColor"/>
    @else
        <rect width="64" height="64" rx="14" fill="#AE4027"/>
        <path d="M33.39 23.19A11.5 11.5 0 1 0 33.39 40.81" fill="none" stroke="#FFFFFF" stroke-width="8"/>
        <path d="M40 18h13v6.5h-6v4.5h4.5v6.5h-4.5V46h-7z" fill="#FFFFFF"/>
    @endif
</svg>
