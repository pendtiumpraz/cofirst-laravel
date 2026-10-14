{{--
    Result / state of a parental-consent link. Variables: $state (granted|declined|expired|cancelled|invalid|closed),
    $consentRequest (or null), $childFirstName, $inviteUrl (create a parent account), $dpoEmail.
--}}
@php
    use App\Services\Privacy\PolicyService;

    $name = $childFirstName ?: 'anak Anda';
    $view = match ($state) {
        'granted' => [
            'icon' => 'check-circle', 'tone' => 'bg-success-soft text-success',
            'title' => 'Terima kasih, persetujuan tercatat',
            'body' => "Akun {$name} sudah aktif dan dapat dipakai untuk masuk. Kami mengirim konfirmasi ke email Anda dan ke {$name}.",
        ],
        'declined' => [
            'icon' => 'x-circle', 'tone' => 'bg-surface-2 text-fg-muted',
            'title' => 'Pendaftaran ditolak',
            'body' => 'Akun tidak diaktifkan dan data pendaftaran sudah kami hapus. Tidak ada tindakan lain yang perlu Anda lakukan.',
        ],
        'expired' => [
            'icon' => 'clock', 'tone' => 'bg-warning-soft text-warning',
            'title' => 'Tautan sudah kedaluwarsa',
            'body' => 'Tautan persetujuan hanya berlaku 7 hari. Data pendaftaran yang tidak disetujui dihapus otomatis. Bila anak masih ingin belajar, minta ia mendaftar ulang.',
        ],
        'closed', 'cancelled' => [
            'icon' => 'information-circle', 'tone' => 'bg-info-soft text-info',
            'title' => 'Permintaan ini sudah ditutup',
            'body' => 'Keputusan untuk pendaftaran ini sudah tercatat sebelumnya, sehingga tautan tidak dapat dipakai lagi.',
        ],
        default => [
            'icon' => 'link-slash', 'tone' => 'bg-danger-soft text-danger',
            'title' => 'Tautan tidak valid',
            'body' => 'Tautan ini tidak dapat dipakai, mungkin karena sudah diganti dengan tautan yang lebih baru atau tidak tersalin lengkap. Buka email terbaru dari kami, atau minta anak mengirim ulang tautan dari halaman pendaftaran.',
        ],
    };
@endphp

<x-public-layout :title="$view['title']">
    <div class="mx-auto max-w-xl px-4 pb-16 pt-12 sm:px-6 lg:pb-24 lg:pt-20">
        <div class="text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-full {{ $view['tone'] }}" aria-hidden="true">
                <x-ui.icon :name="$view['icon']" class="size-7" />
            </span>
            <h1 class="mt-6 font-display text-[2rem] font-title leading-tight tracking-display text-fg">{{ $view['title'] }}</h1>
            <p class="mx-auto mt-4 max-w-measure text-[1.0625rem] leading-relaxed text-fg-muted">{{ $view['body'] }}</p>
            @if ($state === 'granted' && $consentRequest?->responded_at)
                <p class="mt-3 text-sm text-fg-subtle">Dicatat pada {{ PolicyService::date($consentRequest->responded_at, true) }}.</p>
            @endif
        </div>

        @if ($state === 'granted')
            @if ($consentRequest?->guardian_user_id)
                <x-ui.alert tone="success" class="mt-10" title="Akun orang tua sudah tertaut">
                    {{ $name }} sekarang muncul di dasbor orang tua Anda, lengkap dengan jadwal, laporan, dan tagihan.
                </x-ui.alert>
            @elseif ($inviteUrl)
                <x-ui.card class="mt-10" variant="highlight" title="Pantau perkembangan {{ $name }}"
                    subtitle="Buat akun orang tua dengan email ini untuk melihat jadwal, laporan pertemuan, kehadiran, dan tagihan.">
                    <x-ui.button :href="$inviteUrl" icon="user-plus" block>Buat akun orang tua</x-ui.button>
                </x-ui.card>
            @endif

            <div class="mt-8 flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm">
                @if (Route::has('privacy.children'))
                    <a href="{{ route('privacy.children') }}" class="link inline-flex min-h-11 items-center font-medium">Privasi untuk anak & orang tua</a>
                @endif
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="link inline-flex min-h-11 items-center font-medium">Masuk</a>
                @endif
            </div>
            <p class="mt-6 text-center text-[0.8125rem] leading-relaxed text-fg-subtle">
                Anda dapat menarik persetujuan opsional atau meminta penghapusan data kapan saja melalui Pusat Privasi atau email ke Petugas Pelindungan Data Pribadi.
            </p>
        @endif

        @if ($dpoEmail)
            <p class="mt-10 border-t border-line pt-6 text-center text-sm text-fg-muted">
                Ada pertanyaan? Hubungi Petugas Pelindungan Data Pribadi di
                <a href="mailto:{{ $dpoEmail }}" class="link font-medium">{{ $dpoEmail }}</a>.
            </p>
        @endif
    </div>
</x-public-layout>
