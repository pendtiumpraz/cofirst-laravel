{{-- Lupa kata sandi: request a reset link (the confirmation is the same whether or not the email exists). --}}
@php
    $contactEmail = function_exists('setting') ? setting('general.contact_email') : null;
@endphp

<x-guest-layout title="Lupa kata sandi" heading="Lupa kata sandi?"
    subheading="Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.">

    @if (session('status'))
        <x-ui.alert tone="success" title="Periksa email Anda" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5"
        x-data="{ submitting: false }"
        x-on:submit="submitting = true"
        x-on:pageshow.window="submitting = false">
        @csrf

        <x-ui.field label="Email" for="email" hint="Gunakan email yang Anda pakai untuk masuk.">
            <x-ui.input name="email" type="email" icon="envelope" inputmode="email"
                autocomplete="email" placeholder="nama@email.com" required autofocus />
        </x-ui.field>

        <x-ui.button type="submit" size="lg" block icon="paper-airplane" loading-when="submitting" loading-text="Mengirim…">
            Kirim tautan
        </x-ui.button>
    </form>

    @if (filled($contactEmail))
        <p class="mt-6 text-sm text-fg-muted">
            Email akun anak dikelola orang tua/wali. Jika tidak lagi memiliki akses ke email Anda, hubungi
            <a href="mailto:{{ $contactEmail }}" class="link">{{ $contactEmail }}</a>.
        </p>
    @endif

    <p class="mt-8 text-center text-sm">
        <a href="{{ route('login') }}" class="link inline-flex min-h-11 items-center gap-1.5 font-medium">
            <x-ui.icon name="m-arrow-left" class="size-4" />
            Kembali ke halaman masuk
        </a>
    </p>
</x-guest-layout>
