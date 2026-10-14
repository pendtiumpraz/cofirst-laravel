{{--
    The logo to use everywhere (header, footer, auth, offline page).

    Props:
      variant     a|b|auto|null — null reads setting('appearance.logo_variant'); auto follows the active theme
      type        full (lockup, default) | mark (square mark only)
      size        sm|md|lg|xl (see x-brand.lockup) — for type=mark also a pixel number
      layout      horizontal|stacked (type=full only)
      descriptor  bool (type=full only)
      title       accessible name for type=mark (default: site name)

    When an admin uploaded appearance.custom_logo (and optionally custom_logo_dark) the
    image replaces the built-in artwork in both types.
--}}
@props(['variant' => null, 'type' => 'full', 'size' => 'md', 'layout' => 'horizontal', 'descriptor' => false, 'title' => null])
@php
    $siteName = setting('general.site_name', 'Coding First');
    $custom = \App\Support\Navigation\Appearance::customLogoUrl();
    $customDark = \App\Support\Navigation\Appearance::customLogoUrl(dark: true);
    $px = is_numeric($size) ? (int) $size : (['sm' => 28, 'md' => 32, 'lg' => 40, 'xl' => 56][$size] ?? 32);
    // Reason: classes instead of inline style so a strict CSP (no unsafe-inline styles) keeps working.
    $h = match (true) { $px <= 24 => 'h-6', $px <= 28 => 'h-7', $px <= 32 => 'h-8', $px <= 40 => 'h-10', default => 'h-14' };
@endphp
@if ($custom)
    <span {{ $attributes->class('inline-flex items-center') }}>
        <img src="{{ $custom }}" alt="{{ $siteName }}"
             @class([$h, 'w-auto max-w-[220px] object-contain', 'dark:hidden' => (bool) $customDark])>
        @if ($customDark)
            <img src="{{ $customDark }}" alt="{{ $siteName }}" class="{{ $h }} hidden w-auto max-w-[220px] object-contain dark:inline">
        @endif
    </span>
@elseif ($type === 'mark')
    <x-dynamic-component :component="'brand.mark-'.\App\Support\Navigation\Appearance::logoVariant($variant)"
                         :size="$px" :title="$title ?? $siteName" {{ $attributes }} />
@else
    <x-brand.lockup :variant="$variant" :size="is_numeric($size) ? 'md' : $size" :layout="$layout" :descriptor="$descriptor" {{ $attributes }} />
@endif
