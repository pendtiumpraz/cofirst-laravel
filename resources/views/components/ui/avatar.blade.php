{{--
    <x-ui.avatar :user="$student" size="sm" />
    <x-ui.avatar name="Siti Nurhaliza" src="/storage/…" size="lg" />
    <x-ui.avatar :user="$teacher" decorative />          when the name is printed next to it (alt="")
    Photo when the user has profile_photo_path (or src is given); otherwise initials on one of
    6 brand-safe tints hashed from the user id (stable per person). Multibyte-safe initials.
    size: xs (24) | sm (32) | md (40, default) | lg (56) | xl (72) | 2xl (96) · status: true shows an online dot
--}}
@props([
    'user' => null,
    'name' => null,
    'src' => null,
    'size' => 'md',
    'decorative' => false,
    'status' => false,
])

@php
    $displayName = trim((string) ($name ?? ($user->name ?? '')));
    $photo = $src;
    if ($photo === null && $user && ! empty($user->profile_photo_path)) {
        $photo = $user->profile_photo_url ?? null;
    }

    $parts = preg_split('/\s+/u', $displayName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = '';
    if ($parts) {
        $initials = mb_strtoupper(mb_substr($parts[0], 0, 1));
        if (count($parts) > 1) {
            $initials .= mb_strtoupper(mb_substr(end($parts), 0, 1));
        }
    }
    $initials = $initials !== '' ? $initials : '?';

    $seed = (string) ($user->id ?? $displayName);
    $tint = match (abs(crc32($seed)) % 6) {
        0 => 'avatar-tint-0',
        1 => 'avatar-tint-1',
        2 => 'avatar-tint-2',
        3 => 'avatar-tint-3',
        4 => 'avatar-tint-4',
        default => 'avatar-tint-5',
    };

    $sizeClass = match ($size) {
        'xs' => 'avatar-xs',
        'sm' => 'avatar-sm',
        'lg' => 'avatar-lg',
        'xl' => 'avatar-xl',
        '2xl' => 'avatar-2xl',
        default => 'avatar-md',
    };
@endphp

<span {{ $attributes->class(['avatar', $sizeClass, $tint => ! $photo]) }}
    @if (! $decorative && ! $photo && $displayName !== '') role="img" aria-label="{{ $displayName }}" @endif
    @if ($decorative) aria-hidden="true" @endif>
    @if ($photo)
        <img src="{{ $photo }}" alt="{{ $decorative ? '' : $displayName }}" loading="lazy" decoding="async">
    @else
        <span aria-hidden="true">{{ $initials }}</span>
    @endif
    @if ($status)
        <span class="avatar-status"><span class="sr-only">Sedang daring</span></span>
    @endif
</span>
