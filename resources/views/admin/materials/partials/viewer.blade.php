{{--
    Shared material viewer (admin, teacher, student).
    Params: $material, $downloadUrl (string|null), $hasFile (bool), $canInline (bool), $fileSize (?string)

    Content is Markdown rendered without raw HTML (Format::markdown). YouTube links load
    only after a click (privacy: no third-party request without the user's action).
--}}
@php
    $url = $material->external_url;
    $host = $url ? strtolower((string) parse_url($url, PHP_URL_HOST)) : null;
    $youtubeId = null;
    if ($url && preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
        $youtubeId = $m[1];
    }
    $ext = $hasFile ? strtolower(pathinfo((string) $material->file_path, PATHINFO_EXTENSION)) : null;
    $inlineUrl = $downloadUrl ? $downloadUrl.(str_contains($downloadUrl, '?') ? '&' : '?').'inline=1' : null;
@endphp

<div class="space-y-6">
    @if ($youtubeId)
        <x-ui.card padding="none" class="overflow-hidden">
            <div x-data="{ play: false }" class="aspect-video w-full bg-surface-2">
                <template x-if="play">
                    <iframe class="h-full w-full" src="https://www.youtube-nocookie.com/embed/{{ $youtubeId }}?rel=0"
                        title="Video: {{ $material->title }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe>
                </template>
                <div x-show="! play" class="flex h-full flex-col items-center justify-center gap-3 p-6 text-center">
                    <span class="flex size-14 items-center justify-center rounded-full bg-brand-soft text-brand-soft-fg">
                        <x-ui.icon name="play" size="24" />
                    </span>
                    <p class="max-w-measure text-sm text-fg-muted">Video diputar dari YouTube. Kami baru memuatnya setelah Anda menekan tombol putar.</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <x-ui.button icon="play" x-on:click="play = true">Putar video</x-ui.button>
                        <x-ui.button variant="secondary" icon="arrow-top-right-on-square" :href="$url" target="_blank" rel="noopener noreferrer">Buka di YouTube</x-ui.button>
                    </div>
                </div>
            </div>
        </x-ui.card>
    @elseif ($url)
        <x-ui.card variant="inset">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-brand-soft text-brand-soft-fg">
                        <x-ui.icon name="link" />
                    </span>
                    <div class="min-w-0">
                        <p class="font-medium text-fg">Tautan materi</p>
                        <p class="truncate font-mono text-sm text-fg-subtle">{{ $host }}</p>
                    </div>
                </div>
                <x-ui.button variant="secondary" icon-right="arrow-top-right-on-square" :href="$url" target="_blank" rel="noopener noreferrer">Buka tautan</x-ui.button>
            </div>
        </x-ui.card>
    @endif

    @if ($hasFile && $downloadUrl)
        <x-ui.card padding="none" class="overflow-hidden">
            @if ($canInline && $ext === 'pdf')
                <iframe src="{{ $inlineUrl }}" title="Pratinjau {{ $material->title }}" class="hidden h-[70vh] w-full border-b border-line bg-surface-2 md:block" loading="lazy"></iframe>
            @elseif ($canInline && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true))
                <img src="{{ $inlineUrl }}" alt="{{ $material->title }}" class="max-h-[70vh] w-full border-b border-line bg-surface-2 object-contain" loading="lazy">
            @elseif ($canInline && $ext === 'mp4')
                <video src="{{ $inlineUrl }}" controls preload="metadata" class="max-h-[70vh] w-full border-b border-line bg-surface-2"></video>
            @elseif ($canInline && $ext === 'mp3')
                <div class="border-b border-line p-card"><audio src="{{ $inlineUrl }}" controls preload="none" class="w-full"></audio></div>
            @endif
            <div class="flex flex-col gap-3 p-card sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-surface-2 text-fg-muted">
                        <x-ui.icon name="paper-clip" />
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-medium text-fg">Berkas {{ strtoupper($ext) }}</p>
                        <p class="text-sm text-fg-subtle">{{ $fileSize ?? '' }} · disimpan privat, hanya untuk peserta kelas</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($canInline)
                        <x-ui.button variant="secondary" icon="eye" :href="$inlineUrl" target="_blank" rel="noopener">Buka</x-ui.button>
                    @endif
                    <x-ui.button icon="arrow-down-tray" :href="$downloadUrl">Unduh</x-ui.button>
                </div>
            </div>
        </x-ui.card>
    @elseif (filled($material->file_path) && ! $hasFile)
        <x-ui.alert tone="warning" title="Berkas tidak ditemukan">Berkas materi ini belum tersedia di server. Hubungi admin bila Anda membutuhkannya.</x-ui.alert>
    @endif

    @if (filled($material->content))
        <x-ui.card>
            <div class="prose-cf max-w-measure">{{ \App\Services\Academic\Format::markdown($material->content) }}</div>
        </x-ui.card>
    @elseif (! $url && ! $hasFile)
        <x-ui.empty-state icon="document-text" title="Materi ini belum berisi konten." description="Isi materi akan muncul di sini setelah admin melengkapinya." />
    @endif
</div>
