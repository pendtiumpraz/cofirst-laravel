{{--
    After registering a child: the account waits for parent/guardian consent.
    Variables: $registration (flash: state, first_name, guardian_email (masked), email, sent, class, expires) or null,
    $dpoEmail.
--}}
@php
    $data = is_array($registration ?? null) ? $registration : null;
@endphp

<x-guest-layout title="Menunggu persetujuan orang tua" heading="{{ $data ? 'Satu langkah lagi, '.$data['first_name'].'!' : 'Menunggu persetujuan orang tua/wali' }}"
    subheading="Akunmu sudah dibuat, tetapi baru bisa dipakai setelah orang tua atau walimu menyetujui.">

    @if (session('status'))
        <x-ui.alert tone="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($data)
        <ol class="space-y-5" role="list">
            <li class="flex gap-4">
                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-success-soft text-success" aria-hidden="true">
                    <x-ui.icon name="m-check" class="size-4" />
                </span>
                <div>
                    <p class="text-sm font-semibold text-fg">Akun dibuat</p>
                    <p class="mt-0.5 text-sm text-fg-muted">
                        Email masuk: <span class="font-mono text-fg">{{ $data['email'] }}</span>
                        @if (! empty($data['class']))
                            <br>Kursi di kelas <span class="font-medium text-fg">{{ $data['class'] }}</span> kami tahan untukmu.
                        @endif
                    </p>
                </div>
            </li>
            <li class="flex gap-4">
                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-soft text-brand-soft-fg" aria-hidden="true">
                    <x-ui.icon name="m-envelope" class="size-4" />
                </span>
                <div>
                    <p class="text-sm font-semibold text-fg">
                        {{ $data['sent'] ? 'Email terkirim ke orang tua/wali' : 'Email ke orang tua/wali belum terkirim' }}
                    </p>
                    <p class="mt-0.5 text-sm text-fg-muted">
                        @if ($data['sent'])
                            Kami mengirim tautan persetujuan ke <span class="font-mono text-fg">{{ $data['guardian_email'] }}</span>.
                            Tautan berlaku sampai {{ $data['expires'] }}.
                        @else
                            Terjadi kendala saat mengirim email ke <span class="font-mono text-fg">{{ $data['guardian_email'] }}</span>. Gunakan tombol kirim ulang di bawah.
                        @endif
                    </p>
                </div>
            </li>
            <li class="flex gap-4">
                <span class="grid size-8 shrink-0 place-items-center rounded-full border border-line-strong text-fg-subtle" aria-hidden="true">
                    <span class="text-xs font-semibold">3</span>
                </span>
                <div>
                    <p class="text-sm font-semibold text-fg">Orang tua/wali menyetujui</p>
                    <p class="mt-0.5 text-sm text-fg-muted">Setelah disetujui, kamu bisa langsung masuk. Kami juga mengirim email konfirmasi kepadamu.</p>
                </div>
            </li>
        </ol>
    @else
        <p class="text-sm leading-relaxed text-fg-muted">
            Bila kamu baru saja mendaftar, minta orang tua/walimu memeriksa email dari kami (juga folder spam) dan membuka tautan persetujuan. Tautan berlaku 7 hari.
        </p>
    @endif

    <x-ui.card variant="inset" class="mt-8" padding="sm">
        <p class="text-sm font-semibold text-fg">Email belum sampai?</p>
        <p class="mt-1 text-[0.8125rem] text-fg-muted">Kirim ulang tautan ke email orang tua/wali yang kamu isi saat mendaftar. Tautan lama otomatis tidak berlaku.</p>
        <form method="POST" action="{{ route('privacy.parental-consent.resend') }}" class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end"
            x-data="{ sending: false }" x-on:submit="sending = true">
            @csrf
            <x-ui.field label="Email akunmu" for="resend_email" class="min-w-0" error="email">
                <x-ui.input name="email" id="resend_email" type="email" inputmode="email" autocomplete="email" :value="$data['email'] ?? old('email')" required />
            </x-ui.field>
            <x-ui.button type="submit" variant="secondary" icon="arrow-path" loading-when="sending" loading-text="Mengirim…">Kirim ulang</x-ui.button>
        </form>
    </x-ui.card>

    <p class="mt-6 text-[0.8125rem] leading-relaxed text-fg-subtle">
        Bila orang tua/wali menolak atau tidak menanggapi dalam 7 hari, data pendaftaranmu kami hapus sesuai UU PDP.
        @if ($dpoEmail)
            Pertanyaan? Hubungi <a href="mailto:{{ $dpoEmail }}" class="link">{{ $dpoEmail }}</a>.
        @endif
    </p>

    <p class="mt-8 text-center text-sm">
        <a href="{{ route('login') }}" class="link inline-flex min-h-11 items-center gap-1.5 font-medium">
            <x-ui.icon name="m-arrow-left" class="size-4" />
            Ke halaman masuk
        </a>
    </p>
</x-guest-layout>
