{{--
    Legacy avatar (props: user, size xs|sm|md|lg|xl|2xl|3xl, class, showName, gradient), restyled:
    photo or initials on a brand-safe tint hashed from the user id (gradient is ignored).
    Prefer <x-ui.avatar>. With showName, eager-load `roles` to avoid N+1 queries.
--}}
@props([
    'user',
    'size' => 'md',
    'class' => '',
    'showName' => false,
    'gradient' => 'blue',
    'showOnline' => false,
])

@php
    $avatarSize = match ($size) {
        'xs' => 'xs',
        'sm' => 'sm',
        'lg' => 'lg',
        'xl' => 'xl',
        '2xl', '3xl' => '2xl',
        default => 'md',
    };

    $roleLabels = [
        'superadmin' => 'Superadmin',
        'admin' => 'Admin',
        'teacher' => 'Guru',
        'student' => 'Siswa',
        'parent' => 'Orang tua',
        'finance' => 'Keuangan',
    ];
@endphp

<div class="flex min-w-0 items-center {{ $class }}">
    <x-ui.avatar :user="$user" :size="$avatarSize" :decorative="$showName"
        :status="$showOnline && ($user->is_online ?? false)"
        :class="$size === '3xl' ? 'size-32 text-4xl' : null" />

    @if ($showName)
        <div class="ml-3 min-w-0">
            <p class="truncate font-medium text-fg">{{ $user->name }}</p>
            @php($role = $user->roles->first()?->name)
            @if ($role)
                <p class="truncate text-sm text-fg-subtle">{{ $roleLabels[$role] ?? ucfirst($role) }}</p>
            @endif
        </div>
    @endif
</div>
