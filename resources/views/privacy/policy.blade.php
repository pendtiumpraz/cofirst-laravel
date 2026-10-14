{{--
    Public policy page (privacy notice, terms, cookie policy, children's notice) — current,
    scheduled or superseded version. Variables: $document, $html, $toc, $upcoming, $isCurrent,
    $currentUrl, $dpoEmail (PolicyController).
--}}
@php
    use App\Models\Privacy\PolicyDocument;
    use App\Services\Privacy\PolicyService;

    $isChildren = $document->type === 'children_notice';
    $since = PolicyService::date($document->effective_at ?? $document->published_at ?? now());
    $related = collect(PolicyDocument::ROUTES)
        ->except([$document->type, 'dpa_template'])
        ->filter(fn ($route) => $route && Route::has($route))
        ->map(fn ($route, $type) => ['label' => PolicyDocument::TYPES[$type], 'url' => route($route)]);
    $historyUrl = Route::has('privacy.history') ? route('privacy.history', ['jenis' => $document->type]) : null;
@endphp

<x-public-layout :title="$document->title" :description="$document->summary">
    <div class="mx-auto max-w-landing px-4 pb-16 pt-10 sm:px-6 lg:pb-24 lg:pt-14">
        <header class="max-w-measure">
            <p class="eyebrow">{{ $isChildren ? 'Privasi untuk keluarga' : 'Privasi & hukum' }}</p>
            <h1 class="mt-3 font-display text-[2.25rem] font-title leading-tight tracking-display text-fg sm:text-5xl">{{ $document->title }}</h1>
            @if (filled($document->summary))
                <p class="mt-4 text-lg leading-relaxed text-fg-muted">{{ $document->summary }}</p>
            @endif

            <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-fg-subtle">
                <x-ui.badge mono>Versi {{ $document->version }}</x-ui.badge>
                <span>{{ $document->isScheduled() ? 'Berlaku mulai' : 'Berlaku sejak' }} {{ $since }}</span>
                @if ($historyUrl)
                    <a href="{{ $historyUrl }}" class="link inline-flex min-h-11 items-center gap-1.5 font-medium sm:min-h-0">
                        <x-ui.icon name="clock" class="size-4" />Riwayat versi
                    </a>
                @endif
                <button type="button" x-data x-on:click="window.print()" class="link inline-flex min-h-11 items-center gap-1.5 font-medium print:hidden sm:min-h-0">
                    <x-ui.icon name="printer" class="size-4" />Cetak
                </button>
            </div>
        </header>

        <div class="mt-8 max-w-measure space-y-3 print:hidden">
            @if (! $isCurrent && ! $document->isScheduled())
                <x-ui.alert tone="warning" title="Ini versi lama">
                    Anda sedang membaca versi {{ $document->version }}. Versi ini sudah tidak berlaku.
                    @if ($currentUrl)
                        <a href="{{ $currentUrl }}" class="link font-medium">Baca versi yang berlaku</a>.
                    @endif
                </x-ui.alert>
            @elseif ($document->isScheduled())
                <x-ui.alert tone="info" title="Versi yang akan datang">
                    Versi ini mulai berlaku {{ PolicyService::date($document->effective_at, true) }}. Sampai saat itu versi sebelumnya tetap berlaku.
                </x-ui.alert>
            @endif

            @if ($upcoming && Route::has('privacy.history.show'))
                <x-ui.alert tone="info" title="Akan ada perubahan">
                    Versi {{ $upcoming->version }} mulai berlaku {{ PolicyService::date($upcoming->effective_at) }}.
                    <a href="{{ route('privacy.history.show', $upcoming->id) }}" class="link font-medium">Baca perubahannya</a>.
                </x-ui.alert>
            @endif
        </div>

        <div @class(['mt-10 grid gap-8 lg:gap-16', 'lg:grid-cols-[15rem_minmax(0,1fr)]' => count($toc) > 2])>
            @if (count($toc) > 2)
                <aside class="print:hidden lg:sticky lg:top-24 lg:max-h-[calc(100dvh-8rem)] lg:self-start lg:overflow-y-auto scrollbar-thin">
                    <details class="group rounded-lg border border-line bg-surface lg:hidden">
                        <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-3 px-4 text-sm font-semibold text-fg">
                            Daftar isi
                            <x-ui.icon name="chevron-down" class="size-4 text-fg-subtle transition-transform duration-fast group-open:rotate-180 motion-reduce:transition-none" />
                        </summary>
                        <ol class="border-t border-line px-2 py-2 text-sm">
                            @foreach ($toc as $item)
                                <li><a href="#{{ $item['id'] }}" class="flex min-h-11 items-center rounded-md px-2 text-fg-muted hover:bg-surface-2 hover:text-fg">{{ $item['label'] }}</a></li>
                            @endforeach
                        </ol>
                    </details>

                    <nav aria-label="Daftar isi" class="hidden lg:block">
                        <p class="eyebrow mb-3 px-2">Daftar isi</p>
                        <ol class="space-y-0.5 text-sm">
                            @foreach ($toc as $item)
                                <li><a href="#{{ $item['id'] }}" class="block rounded-md px-2 py-1.5 leading-snug text-fg-muted transition-colors duration-fast hover:bg-surface-2 hover:text-fg motion-reduce:transition-none">{{ $item['label'] }}</a></li>
                            @endforeach
                        </ol>
                    </nav>
                </aside>
            @endif

            <article class="prose-cf min-w-0">
                {!! $html !!}
            </article>
        </div>

        <aside aria-label="Bantuan dan dokumen terkait" class="mt-16 grid gap-4 border-t border-line pt-10 print:hidden md:grid-cols-2">
            <x-ui.card variant="inset" title="Ada pertanyaan tentang data pribadi?" level="2">
                <p class="text-sm text-fg-muted">
                    Petugas Pelindungan Data Pribadi kami siap membantu. Permintaan dan pengaduan ditanggapi paling lambat 3 × 24 jam.
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($dpoEmail)
                        <x-ui.button variant="secondary" size="sm" icon="envelope" href="mailto:{{ $dpoEmail }}">{{ $dpoEmail }}</x-ui.button>
                    @endif
                    @if (Route::has('privacy.cookies.preferences'))
                        <x-ui.button variant="ghost" size="sm" icon="adjustments-horizontal" :href="route('privacy.cookies.preferences')">Pengaturan cookie</x-ui.button>
                    @endif
                </div>
            </x-ui.card>

            @if ($related->isNotEmpty())
                <x-ui.card variant="inset" title="Dokumen terkait" level="2">
                    <ul role="list" class="-mx-2 space-y-0.5">
                        @foreach ($related as $link)
                            <li>
                                <a href="{{ $link['url'] }}" class="flex min-h-11 items-center justify-between gap-3 rounded-md px-2 text-sm font-medium text-fg hover:bg-surface-2">
                                    {{ $link['label'] }}
                                    <x-ui.icon name="m-arrow-right" class="size-4 text-fg-subtle" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif
        </aside>
    </div>
</x-public-layout>
