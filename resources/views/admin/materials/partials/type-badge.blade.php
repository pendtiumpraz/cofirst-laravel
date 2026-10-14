{{-- Material type badge. Params: $type (document|video|exercise|quiz|project). --}}
@php
    [$tone, $icon] = match ($type ?? null) {
        'video' => ['primary', 'play-circle'],
        'exercise' => ['success', 'pencil-square'],
        'quiz' => ['warning', 'question-mark-circle'],
        'project' => ['info', 'cube'],
        default => ['neutral', 'document-text'],
    };
@endphp
<x-ui.badge :tone="$tone" :icon="$icon">{{ \App\Models\Material::getTypes()[$type ?? ''] ?? 'Materi' }}</x-ui.badge>
