{{--
    Brand panel of the split auth layout (≥1024px): lockup, theme illustration,
    tagline and trust points. Variables: $home, $theme ('a' | 'b').
--}}
@php
    $tagline = setting('general.tagline', 'Belajar coding bersama mentor dari perusahaan teknologi Jepang');
    $points = [
        ['academic-cap', 'Kurikulum terstruktur dengan mentor dari industri teknologi Jepang'],
        ['chart-bar', 'Laporan perkembangan yang transparan untuk orang tua'],
        ['shield-check', 'Data Anda dilindungi sesuai UU PDP (UU No. 27 Tahun 2022)'],
    ];
@endphp
<aside class="relative hidden flex-col justify-between overflow-hidden border-r border-line bg-surface-2 px-10 py-10 lg:flex xl:px-14" aria-label="Tentang {{ setting('general.site_name', 'Coding First') }}">
    <a href="{{ $home }}" class="inline-flex self-start rounded-md">
        <x-brand.logo size="md" :descriptor="true" />
        <span class="sr-only">— ke beranda</span>
    </a>

    <div class="my-10 max-w-md">
        @if ($theme === 'a')
            {{-- Manuscript page with code lines and the approval seal (illustration: raw ramps allowed). --}}
            <svg viewBox="0 0 360 240" class="w-full max-w-sm" role="img" aria-label="Naskah kode dengan stempel persetujuan">
                <rect x="24" y="12" width="232" height="216" rx="10" class="fill-surface stroke-line-strong" stroke-width="1"/>
                <path d="M48 44h120M48 66h168M64 88h132M64 110h96M48 132h152M64 154h112M48 176h80" class="stroke-line-strong" stroke-width="6" stroke-linecap="round"/>
                <path d="M48 44h40M64 110h44M48 176h24" class="stroke-brand" stroke-width="6" stroke-linecap="round"/>
                <g transform="translate(232 136) rotate(-3) scale(1.4)">
                    <rect width="64" height="64" rx="14" fill="#AE4027"/>
                    <rect x="4" y="4" width="56" height="56" rx="11" fill="none" stroke="#FFFFFF" stroke-width="1.25"/>
                    <path d="M33.39 23.19A11.5 11.5 0 1 0 33.39 40.81" fill="none" stroke="#FFFFFF" stroke-width="8"/>
                    <path d="M40 18h13v6.5h-6v4.5h4.5v6.5h-4.5V46h-7z" fill="#FFFFFF"/>
                </g>
            </svg>
        @else
            {{-- Terminal card on the 32px grid with the shu caret. --}}
            <svg viewBox="0 0 360 240" class="w-full max-w-sm" role="img" aria-label="Jendela kode dengan kursor">
                <defs>
                    <pattern id="cf-auth-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                        <path d="M32 0H0V32" fill="none" class="stroke-line" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="360" height="240" fill="url(#cf-auth-grid)"/>
                <rect x="36" y="36" width="288" height="168" rx="10" class="fill-neutral-950 stroke-neutral-700" stroke-width="1"/>
                <circle cx="56" cy="56" r="4" class="fill-neutral-700"/><circle cx="70" cy="56" r="4" class="fill-neutral-700"/><circle cx="84" cy="56" r="4" class="fill-neutral-700"/>
                <path d="M60 92h56M124 92h88M60 116h40M108 116h120M76 140h96M60 164h64" class="stroke-neutral-600" stroke-width="6" stroke-linecap="round"/>
                <path d="M60 92h56M76 140h40" class="stroke-primary-400" stroke-width="6" stroke-linecap="round"/>
                <rect x="134" y="154" width="6" height="20" rx="1.5" class="fill-accent-500"/>
            </svg>
        @endif

        <p class="mt-10 font-display text-3xl leading-tight tracking-display text-fg xl:text-[2.125rem]">{{ $tagline }}</p>

        <ul role="list" class="mt-8 space-y-4">
            @foreach ($points as [$icon, $text])
                <li class="flex items-start gap-3 text-sm text-fg-muted">
                    <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-md border border-line bg-surface text-brand-text">
                        @svg('heroicon-o-'.$icon, 'size-4', ['aria-hidden' => 'true'])
                    </span>
                    <span class="pt-1.5">{{ $text }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <p class="text-xs text-fg-subtle">© {{ now()->year }} {{ setting('general.legal_name', 'Coding First Indonesia') }}</p>
</aside>
