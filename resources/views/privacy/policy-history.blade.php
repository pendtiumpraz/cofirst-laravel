{{--
    Version history of a public policy document (UU PDP Pasal 21 ayat (2): changes are
    announced and traceable). Variables: $type, $types, $versions, $current (PolicyController).
--}}
@php
    use App\Services\Privacy\PolicyService;

    $tabs = collect($types)->map(fn ($label, $key) => ['label' => $label, 'href' => route('privacy.history', ['jenis' => $key])])->all();
@endphp

<x-public-layout title="Riwayat versi {{ $types[$type] }}" description="Semua versi {{ $types[$type] }} beserta tanggal berlaku dan ringkasan perubahannya.">
    <div class="mx-auto max-w-landing px-4 pb-16 pt-10 sm:px-6 lg:pb-24 lg:pt-14">
        <header class="max-w-measure">
            <p class="eyebrow">Privasi & hukum</p>
            <h1 class="mt-3 font-display text-[2.25rem] font-title leading-tight tracking-display text-fg sm:text-5xl">Riwayat versi</h1>
            <p class="mt-4 text-lg leading-relaxed text-fg-muted">
                Setiap perubahan kebijakan kami umumkan sebelum berlaku. Di sini Anda dapat membaca semua versi yang pernah terbit beserta catatan perubahannya.
            </p>
        </header>

        <x-ui.tabs class="mt-8 overflow-x-auto scrollbar-none" label="Jenis dokumen" :tabs="$tabs" :active="$type" />

        <div class="mt-8 max-w-3xl">
            @forelse ($versions as $version)
                @php
                    $isCurrent = $current && $current->id === $version->id;
                    $scheduled = $version->isScheduled();
                @endphp
                <article class="relative border-l border-line pb-10 pl-6 last:pb-0 sm:pl-8">
                    <span @class([
                        'absolute -left-[5px] top-1.5 size-[9px] rounded-full ring-4 ring-canvas',
                        'bg-brand' => $isCurrent,
                        'bg-info-solid' => $scheduled,
                        'bg-line-strong' => ! $isCurrent && ! $scheduled,
                    ]) aria-hidden="true"></span>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                        <h2 class="font-display text-card-title font-title text-fg">Versi {{ $version->version }}</h2>
                        @if ($isCurrent)
                            <x-ui.badge tone="success" dot>Berlaku</x-ui.badge>
                        @elseif ($scheduled)
                            <x-ui.badge tone="info" dot>Terjadwal</x-ui.badge>
                        @else
                            <x-ui.badge>Versi lama</x-ui.badge>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-fg-subtle">
                        {{ $scheduled ? 'Berlaku mulai' : 'Berlaku sejak' }} {{ PolicyService::date($version->effective_at ?? $version->published_at) }}
                        · diterbitkan {{ PolicyService::date($version->published_at) }}
                    </p>

                    @if (filled($version->changelog))
                        <div class="mt-3 max-w-measure whitespace-pre-line text-[0.9375rem] leading-relaxed text-fg-muted">{{ $version->changelog }}</div>
                    @endif

                    <x-ui.button variant="link" size="sm" arrow class="mt-3" :href="route('privacy.history.show', $version->id)">
                        Baca versi {{ $version->version }}
                    </x-ui.button>
                </article>
            @empty
                <x-ui.empty-state size="page" icon="document-text" title="Belum ada versi yang diterbitkan."
                    description="Dokumen ini sedang disiapkan. Untuk sementara, teks bawaan kami tetap berlaku dan dapat dibaca di halaman dokumennya." />
            @endforelse
        </div>
    </div>
</x-public-layout>
