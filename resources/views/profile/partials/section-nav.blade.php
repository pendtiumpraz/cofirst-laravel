{{--
    In-page navigation of the profile: a row of anchor chips (scrolls sideways on phones).
    Variable: $privacyUrl (the "Hapus akun" section only exists when the privacy centre does).
--}}
@php
    $sections = [
        'informasi' => ['Informasi pribadi', 'identification'],
        'foto' => ['Foto profil', 'photo'],
        'keamanan' => ['Keamanan', 'key'],
        'sesi' => ['Sesi aktif', 'computer-desktop'],
    ];
    if ($privacyUrl) {
        $sections['hapus-akun'] = ['Hapus akun', 'user-minus'];
    }
@endphp

{{-- Reason: min-w-0 lets the chip row scroll inside its own box instead of widening the page on phones. --}}
<nav aria-label="Bagian profil" class="min-w-0">
    <ul role="list" class="scrollbar-none -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0">
        @foreach ($sections as $anchor => [$label, $icon])
            <li class="shrink-0">
                <a href="#{{ $anchor }}"
                    class="inline-flex min-h-11 items-center gap-2 whitespace-nowrap rounded-md border border-line bg-surface px-3 text-sm text-fg-muted transition-colors duration-fast hover:border-line-strong hover:text-fg motion-reduce:transition-none">
                    <x-ui.icon :name="$icon" class="size-[1.125rem] text-fg-subtle" />
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
