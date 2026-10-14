@php
    $radii = ['sm' => 'rounded-sm', 'md' => 'rounded-md', 'lg' => 'rounded-lg', 'xl' => 'rounded-xl', '2xl' => 'rounded-2xl', 'badge' => 'rounded-badge'];
    $shadows = ['1' => 'shadow-1 · hover kartu, header saat gulir', '2' => 'shadow-2 · dropdown, toast, palet', '3' => 'shadow-3 · modal, lembar bawah'];
    $density = [
        'control-h' => 'h-control · tinggi kontrol',
        'row-h' => 'h-row · tinggi baris tabel',
        'card-p' => 'p-card · padding kartu',
        'gap' => 'gap-gutter · jarak antar blok',
        'nav-item-h' => 'h-nav-item · item navigasi',
    ];
@endphp

<section id="tipografi" class="scroll-mt-24 space-y-8">
    <x-ui.section-heading number="02" eyebrow="Token" title="Tipografi, radius, bayangan, kerapatan"
        description="Versi A: Newsreader + Plus Jakarta Sans + JetBrains Mono. Versi B: Geist + Geist Mono. Semua di-host sendiri (tanpa CDN)." />

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Skala tipografi" subtitle="font-display · font-sans · font-mono · font-num">
            <div class="space-y-5">
                <div>
                    <p class="eyebrow mb-1">Eyebrow · .eyebrow</p>
                    <p class="page-title">Selamat pagi, <em>Budi</em>.</p>
                    <p class="mt-1 font-mono text-xs text-fg-subtle">.page-title (h1) · &lt;em&gt; miring di A, berwarna di B</p>
                </div>
                <div>
                    <p class="card-title">Kelas berikutnya</p>
                    <p class="font-mono text-xs text-fg-subtle">.card-title</p>
                </div>
                <div>
                    <p class="text-fg">Teks UI {{ $preview['theme'] === 'b' ? '14px' : '15px' }} — kelas kecil, mentor praktisi, dan laporan setiap pertemuan.</p>
                    <p class="text-[0.8125rem] text-fg-subtle">Keterangan 13px · text-fg-subtle</p>
                </div>
                <div class="flex flex-wrap items-baseline gap-x-6 gap-y-2">
                    <p class="stat-value">Rp 48,2 <span class="stat-unit">jt</span></p>
                    <p class="num text-fg">Rp 1.250.000 · 94,1%</p>
                    <p class="font-mono text-sm text-fg-muted">INV-2026-0142</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Teks panjang" subtitle=".prose-cf — kebijakan privasi, S&K, laporan kelas">
            <div class="prose-cf text-[0.9375rem]">
                <h3>Hak Anda sebagai subjek data</h3>
                <p>Anda berhak <strong>mengakses, memperbaiki, dan meminta penghapusan</strong> data pribadi sesuai UU No. 27 Tahun 2022. Ajukan permintaan dari <a href="#">Pusat Privasi</a>.</p>
                <ul>
                    <li>Kami merespons paling lambat 3×24 jam.</li>
                    <li>Data anak hanya diproses dengan persetujuan orang tua/wali.</li>
                </ul>
                <blockquote>Persetujuan dapat ditarik kapan saja tanpa memengaruhi layanan inti.</blockquote>
            </div>
        </x-ui.card>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Radius">
            <ul class="grid grid-cols-3 gap-3">
                @foreach ($radii as $name => $class)
                    <li class="text-center">
                        <div class="mx-auto mb-1.5 size-14 border border-line-strong bg-surface-2 {{ $class }}"></div>
                        <p class="font-mono text-[0.6875rem] text-fg-subtle">{{ $class }}</p>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>

        <x-ui.card title="Bayangan" subtitle="Dark mode: elevasi dari langkah permukaan + garis.">
            <ul class="space-y-4">
                @foreach ($shadows as $level => $text)
                    <li class="rounded-lg border border-line bg-surface p-3 text-[0.8125rem] text-fg-muted shadow-{{ $level }}">{{ $text }}</li>
                @endforeach
            </ul>
            <span class="hidden shadow-1 shadow-2 shadow-3"></span>
        </x-ui.card>

        <x-ui.card title="Kerapatan" subtitle="html[data-density] · sentuh selalu ≥ 44px">
            <ul class="space-y-2.5">
                @foreach ($density as $token => $text)
                    <li class="flex items-center gap-3">
                        <span class="block shrink-0 rounded-sm bg-brand/20" style="width: var(--cf-{{ $token }}); height: 0.75rem; max-width: 7rem"></span>
                        <span class="font-mono text-[0.6875rem] text-fg-subtle">--cf-{{ $token }} · {{ $text }}</span>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    </div>

    <x-ui.card title="Ikon" subtitle="Heroicons v2 outline lewat x-ui.icon; ketebalan garis mengikuti --cf-icon-stroke (A 1.5 · B 1.75).">
        <div class="flex flex-wrap gap-4 text-fg-muted">
            @foreach (['home', 'academic-cap', 'calendar-days', 'document-text', 'banknotes', 'trophy', 'users', 'chat-bubble-left-right', 'shield-check', 'finger-print', 'cog-6-tooth', 'bell'] as $icon)
                <span class="grid size-11 place-items-center rounded-md border border-line bg-surface" title="{{ $icon }}">
                    <x-ui.icon :name="$icon" size="20" />
                </span>
            @endforeach
        </div>
    </x-ui.card>
</section>
