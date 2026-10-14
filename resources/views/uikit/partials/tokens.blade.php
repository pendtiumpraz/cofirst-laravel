@php
    $groups = [
        'Permukaan' => [
            ['canvas', 'bg-canvas'], ['surface', 'bg-surface'], ['surface-2', 'bg-surface-2'], ['surface-3', 'bg-surface-3'],
        ],
        'Garis' => [
            ['border', 'border-line'], ['border-strong', 'border-line-strong'], ['border-input', 'border-line-input'],
        ],
        'Teks' => [
            ['fg', 'text-fg'], ['fg-muted', 'text-fg-muted'], ['fg-subtle', 'text-fg-subtle'],
        ],
        'Brand (aksi)' => [
            ['brand', 'bg-brand'], ['brand-hover', 'hover:bg-brand-hover'], ['on-brand', 'text-brand-fg'],
            ['brand-text', 'text-brand-text'], ['brand-soft', 'bg-brand-soft'], ['on-brand-soft', 'text-brand-soft-fg'],
        ],
        'Aksen (tanda tangan)' => [
            ['accent', 'bg-accent'], ['accent-text', 'text-accent-text'], ['accent-solid', 'bg-accent-solid'],
            ['accent-solid-hover', 'hover:bg-accent-solid-hover'], ['on-accent', 'text-accent-fg'],
        ],
        'Fokus & indikator' => [
            ['ring', 'ring-ring'], ['nav-indicator', 'bg-indicator'], ['tab-active', 'text-tab-active'],
        ],
        'Kode & grid' => [
            ['code-bg', 'bg-code'], ['code-fg', 'text-code-fg'], ['grid', 'bg-grid'],
        ],
    ];
    $statuses = ['success' => 'Lunas', 'warning' => 'Menunggu', 'danger' => 'Terlambat', 'info' => 'Baru'];
    $steps = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
@endphp

<section id="token" class="scroll-mt-24 space-y-8">
    <x-ui.section-heading number="01" eyebrow="Token" title="Warna semantik"
        description="Markup hanya memakai utilitas semantik (bg-surface, text-fg-muted, bg-brand …). Mode gelap ditangani token — jangan pakai varian dark:." />

    @foreach ($groups as $groupName => $tokens)
        <div>
            <h3 class="mb-3 font-sans text-sm font-semibold tracking-normal text-fg">{{ $groupName }}</h3>
            <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($tokens as [$token, $utility])
                    <li class="overflow-hidden rounded-md border border-line bg-surface">
                        <div class="h-14 border-b border-line" style="background-color: rgb(var(--cf-{{ $token }}))"></div>
                        <div class="space-y-0.5 p-2.5">
                            <p class="truncate font-mono text-[0.6875rem] text-fg">--cf-{{ $token }}</p>
                            <p class="truncate font-mono text-[0.6875rem] text-fg-subtle">{{ $utility }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach

    <div>
        <h3 class="mb-3 font-sans text-sm font-semibold tracking-normal text-fg">Status (teks · lembut · solid)</h3>
        <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($statuses as $status => $example)
                <li class="card card-body-sm space-y-2">
                    <p class="font-mono text-xs text-fg-subtle">--cf-{{ $status }}</p>
                    <div class="flex items-center gap-2">
                        <span class="rounded-md px-2.5 py-1 text-sm font-semibold" style="background: rgb(var(--cf-{{ $status }}-soft)); color: rgb(var(--cf-{{ $status }}))">{{ $example }}</span>
                        <span class="rounded-md px-2.5 py-1 text-sm font-semibold text-white" style="background: rgb(var(--cf-{{ $status }}-solid))">{{ $example }}</span>
                    </div>
                    <p class="font-mono text-[0.6875rem] text-fg-subtle">bg-{{ $status }}-soft text-{{ $status }} · bg-{{ $status }}-solid</p>
                </li>
            @endforeach
        </ul>
    </div>

    <div>
        <h3 class="mb-3 font-sans text-sm font-semibold tracking-normal text-fg">Seri grafik</h3>
        <ul class="flex flex-wrap gap-3">
            @foreach (range(1, 6) as $n)
                <li class="flex items-center gap-2 text-sm text-fg-muted">
                    <span class="size-4 rounded-sm" style="background: rgb(var(--cf-chart-{{ $n }}))"></span>
                    <span class="font-mono text-xs">chart-{{ $n }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="space-y-3">
        <h3 class="font-sans text-sm font-semibold tracking-normal text-fg">Ramp mentah (hanya ilustrasi, grafik, sampul kursus)</h3>
        @foreach (['primary', 'accent', 'neutral'] as $ramp)
            <div>
                <p class="mb-1.5 font-mono text-xs text-fg-subtle">{{ $ramp }}-50 … {{ $ramp }}-950</p>
                <div class="grid grid-cols-11 overflow-hidden rounded-md border border-line">
                    @foreach ($steps as $step)
                        <div class="h-10" style="background: rgb(var(--cf-{{ $ramp }}-{{ $step }}))" title="{{ $ramp }}-{{ $step }}"></div>
                    @endforeach
                </div>
            </div>
        @endforeach
        <p class="text-[0.8125rem] text-fg-subtle">
            Jaring pengaman: kelas lama <code class="font-mono">blue/indigo/purple/violet/sky</code> memakai ramp primary,
            <code class="font-mono">pink/fuchsia/orange</code> ramp accent, dan <code class="font-mono">gray/slate/zinc/neutral/stone</code> ramp neutral.
        </p>
    </div>
</section>
