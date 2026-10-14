{{--
    Cookie preferences (works without JavaScript). Variables: $state (CookieConsentService::state),
    $purposes (category => ConsentPurpose|null), $history (latest cookie consent rows of the user).
--}}
@php
    use App\Services\Privacy\PolicyService;

    $decidedAt = $state['decided_at'] ? \Illuminate\Support\Carbon::createFromTimestampMs($state['decided_at']) : null;
    $describe = fn ($category, $fallback) => $purposes[$category]?->description ?? $fallback;
@endphp

<x-public-layout title="Pengaturan cookie" description="Atur cookie fungsional dan analitik di situs Coding First kapan saja.">
    <div class="mx-auto max-w-2xl px-4 pb-16 pt-10 sm:px-6 lg:pb-24 lg:pt-14">
        <header>
            <p class="eyebrow">Privasi & hukum</p>
            <h1 class="mt-3 font-display text-[2.25rem] font-title leading-tight tracking-display text-fg sm:text-5xl">Pengaturan cookie</h1>
            <p class="mt-4 text-lg leading-relaxed text-fg-muted">
                Cookie esensial selalu aktif agar situs berfungsi. Cookie lain hanya aktif bila Anda mengizinkan, dan pilihan dapat diubah kapan saja.
            </p>
            <p class="mt-3 text-sm text-fg-subtle">
                @if ($state['decided'])
                    Pilihan Anda tersimpan{{ $decidedAt ? ' sejak '.PolicyService::date($decidedAt, true) : '' }}.
                @else
                    Anda belum memilih. Sampai Anda memilih, hanya cookie esensial yang aktif.
                @endif
                @if (Route::has('privacy.cookies'))
                    <a href="{{ route('privacy.cookies') }}" class="link font-medium">Baca Kebijakan Cookie</a>
                @endif
            </p>
        </header>

        <form method="POST" action="{{ route('privacy.cookies.update') }}" class="mt-10">
            @csrf
            <input type="hidden" name="source" value="page">

            <x-ui.card padding="none">
                <div class="divide-y divide-line">
                    <div class="flex items-start justify-between gap-4 p-card">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-fg">Esensial</p>
                            <p class="mt-1 text-[0.8125rem] leading-relaxed text-fg-muted">Sesi masuk, perlindungan formulir, dan penyimpanan pilihan cookie ini. Tidak dapat dimatikan karena situs tidak berfungsi tanpanya.</p>
                        </div>
                        <x-ui.badge tone="neutral" class="mt-0.5 shrink-0">Selalu aktif</x-ui.badge>
                    </div>

                    <div class="p-card">
                        <x-ui.toggle name="functional" id="cookie-functional" value="1" unchecked-value="0"
                            label="Fungsional" :checked="$state['choices']['functional']"
                            :description="$describe('functional', 'Konten pihak ketiga yang Anda minta, misalnya peta lokasi kelas.')" />
                    </div>

                    <div class="p-card">
                        @if ($state['minor'])
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-fg">Analitik</p>
                                    <p class="mt-1 text-[0.8125rem] leading-relaxed text-fg-muted">Tidak tersedia untuk akun anak. Kami tidak mengumpulkan statistik perilaku dari anak.</p>
                                </div>
                                <x-ui.badge tone="neutral" class="mt-0.5 shrink-0">Selalu nonaktif</x-ui.badge>
                            </div>
                            <input type="hidden" name="analytics" value="0">
                        @else
                            <x-ui.toggle name="analytics" id="cookie-analytics" value="1" unchecked-value="0"
                                label="Analitik" :checked="$state['choices']['analytics']"
                                :description="$describe('analytics', 'Statistik kunjungan agregat untuk memperbaiki situs.')" />
                        @endif
                    </div>
                </div>

                <x-slot:footer>
                    <div class="grid w-full gap-2 sm:flex sm:justify-end">
                        <x-ui.button type="submit" name="action" value="reject_optional" variant="secondary">Tolak opsional</x-ui.button>
                        <x-ui.button type="submit" name="action" value="accept_all" variant="secondary">Terima semua</x-ui.button>
                        <x-ui.button type="submit" name="action" value="custom" icon="check">Simpan pilihan</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.card>
        </form>

        @if ($history->isNotEmpty())
            <section class="mt-10" aria-labelledby="riwayat-cookie">
                <h2 id="riwayat-cookie" class="font-display text-card-title font-title text-fg">Riwayat pilihan akun Anda</h2>
                <p class="mt-1 text-sm text-fg-muted">Tercatat sebagai bukti persetujuan (UU PDP Pasal 24).</p>
                <ul role="list" class="mt-4 divide-y divide-line rounded-lg border border-line bg-surface">
                    @foreach ($history as $record)
                        <li class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm">
                            <span class="text-fg">{{ $purposes->firstWhere('key', $record->purpose_key)?->name ?? $record->purpose_key }}</span>
                            <span class="flex items-center gap-3">
                                <x-ui.badge :tone="$record->decision_tone" dot>{{ $record->decision_label }}</x-ui.badge>
                                <span class="text-fg-subtle">{{ PolicyService::date($record->created_at, true) }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-public-layout>
