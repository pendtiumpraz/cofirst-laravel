{{--
    <x-ui.page-header title="Pengguna" subtitle="Kelola akun siswa, orang tua, guru dan staf."
        :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'Pengguna']]">
        <x-slot:badge><x-ui.badge>134</x-ui.badge></x-slot:badge>
        <x-slot:meta><span>KLS-0142</span><span>Dibuat 3 Okt 2026</span></x-slot:meta>
        <x-slot:actions><x-ui.button icon="plus" :href="route('admin.users.create')">Tambah pengguna</x-ui.button></x-slot:actions>
    </x-ui.page-header>
    The title may also be a slot to allow <em>: <x-slot:title>Selamat pagi, <em>Budi</em>.</x-slot:title>
    Default slot renders under the subtitle (e.g. link tabs).
--}}
@props([
    'title' => null,
    'subtitle' => null,
    'eyebrow' => null,
    'breadcrumbs' => [],
])

<header {{ $attributes->class('page-header') }}>
    <div class="min-w-0 flex-1">
        @if (! empty($breadcrumbs))
            <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-3" />
        @endif
        @if (filled($eyebrow))
            <p class="eyebrow mb-2">{{ $eyebrow }}</p>
        @endif
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
            <h1 class="page-title">{{ $title }}</h1>
            @isset($badge)
                {{ $badge }}
            @endisset
        </div>
        @if (filled($subtitle))
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
        @isset($meta)
            <div class="page-meta">{{ $meta }}</div>
        @endisset
        @if ($slot->hasActualContent())
            <div class="mt-4">{{ $slot }}</div>
        @endif
    </div>
    @isset($actions)
        <div class="page-actions">{{ $actions }}</div>
    @endisset
</header>
