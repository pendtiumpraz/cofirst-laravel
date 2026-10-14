{{--
    Re-acceptance of new policy versions (EnsurePolicyAccepted → privacy.accept).
    Variables: $documents (PolicyDocument[] to accept), $isChild, $privacyCentreUrl.
--}}
@php
    use App\Services\Privacy\PolicyService;
@endphp

<x-guest-layout title="Pembaruan kebijakan" heading="Kami memperbarui kebijakan kami"
    subheading="Mohon baca ringkasan perubahannya. Setelah menyetujui, Anda langsung kembali ke halaman yang dituju.">

    @if (session('status'))
        <x-ui.alert tone="info" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <div class="space-y-4">
        @foreach ($documents as $document)
            <x-ui.card padding="sm" as="article">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="font-display text-card-title font-title text-fg">{{ $document->title }}</h2>
                    <x-ui.badge mono>Versi {{ $document->version }}</x-ui.badge>
                </div>
                <p class="mt-1 text-[0.8125rem] text-fg-subtle">Berlaku sejak {{ PolicyService::date($document->effective_at ?? $document->published_at) }}</p>

                @if (filled($document->changelog))
                    <div class="mt-3 rounded-md bg-surface-2 p-3">
                        <p class="text-xs font-semibold uppercase tracking-eyebrow text-fg-subtle">Yang berubah</p>
                        <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-fg-muted">{{ $document->changelog }}</p>
                    </div>
                @elseif (filled($document->summary))
                    <p class="mt-3 text-sm leading-relaxed text-fg-muted">{{ $document->summary }}</p>
                @endif

                @if ($url = $document->publicUrl())
                    <a href="{{ Route::has('privacy.history.show') ? route('privacy.history.show', $document->id) : $url }}" target="_blank" rel="noopener"
                        class="link mt-3 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium sm:min-h-0">
                        Baca selengkapnya
                        <x-ui.icon name="m-arrow-top-right-on-square" class="size-4" />
                        <span class="sr-only">(tab baru)</span>
                    </a>
                @endif
            </x-ui.card>
        @endforeach
    </div>

    @if ($isChild && Route::has('privacy.children'))
        <x-ui.alert tone="neutral" icon="sparkles" class="mt-6">
            Ada penjelasan yang lebih mudah dipahami di
            <a href="{{ route('privacy.children') }}" target="_blank" rel="noopener" class="link font-medium">Privasi untuk Anak</a>.
            Ajak orang tua/walimu membacanya bersama.
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('privacy.accept.store') }}" class="mt-8 space-y-5" x-data="{ busy: false }" x-on:submit="busy = true">
        @csrf
        @foreach ($documents as $document)
            <input type="hidden" name="documents[]" value="{{ $document->id }}">
        @endforeach

        <x-ui.field for="accept" error="accept">
            <x-ui.checkbox name="accept" value="1" required
                label="Saya telah membaca dan menyetujui versi terbaru dokumen di atas." />
        </x-ui.field>

        <x-ui.button type="submit" size="lg" block icon="check" loading-when="busy" loading-text="Menyimpan…">Setujui dan lanjutkan</x-ui.button>
    </form>

    <div class="mt-8 space-y-3 border-t border-line pt-6 text-center text-sm text-fg-muted">
        <p>Tidak setuju? Anda tetap dapat mengunduh atau meminta penghapusan data Anda.</p>
        <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-1">
            @if ($privacyCentreUrl)
                <a href="{{ $privacyCentreUrl }}" class="link inline-flex min-h-11 items-center font-medium">Buka Pusat Privasi</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" data-cf-logout>
                @csrf
                <button type="submit" class="link inline-flex min-h-11 items-center font-medium">Keluar</button>
            </form>
        </div>
    </div>
</x-guest-layout>
