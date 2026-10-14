{{-- Meeting progress status badge. Params: $status (completed|in_progress|not_started), optional $locked (bool). --}}
@php
    [$tone, $icon, $label] = match ($status ?? null) {
        'completed' => ['success', 'check-circle', 'Selesai'],
        'in_progress' => ['info', 'play-circle', 'Sedang berjalan'],
        default => ['neutral', 'clock', 'Belum dimulai'],
    };
@endphp
<x-ui.badge :tone="$tone" :icon="$icon">{{ $label }}</x-ui.badge>
