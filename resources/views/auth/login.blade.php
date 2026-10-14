{{--
    Masuk. Rendered on <x-guest-layout> (split or centered per setting appearance.auth_layout).
    Variables: $canResetPassword, $canRegister (AuthenticatedSessionController::create).
--}}
@php
    $privacy = \App\Support\Navigation\LegalLinks::all()['privacy'] ?? null;
@endphp

<x-guest-layout title="Masuk" heading="Masuk ke akun Anda"
    subheading="Lihat jadwal, laporan belajar, dan tagihan dalam satu tempat.">

    @if (session('status'))
        <x-ui.alert tone="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5"
        x-data="{ submitting: false }"
        x-on:submit="submitting = true"
        x-on:pageshow.window="submitting = false">
        @csrf

        <x-ui.field label="Email" for="email">
            <x-ui.input name="email" type="email" icon="envelope" inputmode="email"
                autocomplete="username" placeholder="nama@email.com" required autofocus />
        </x-ui.field>

        @include('auth.partials.password-input', [
            'name' => 'password',
            'label' => 'Kata sandi',
            'autocomplete' => 'current-password',
            'asideUrl' => ($canResetPassword ?? false) ? route('password.request') : null,
            'asideLabel' => 'Lupa kata sandi?',
        ])

        <x-ui.checkbox name="remember" label="Ingat saya di perangkat ini"
            description="Jangan dicentang di komputer umum atau milik bersama." />

        <x-ui.button type="submit" size="lg" block loading-when="submitting" loading-text="Memproses…">
            Masuk
        </x-ui.button>
    </form>

    @if ($canRegister ?? false)
        <p class="mt-8 text-center text-sm text-fg-muted">
            Belum punya akun?
            <a href="{{ route('register') }}" class="link font-medium">Daftar sekarang</a>
        </p>
    @endif

    <p class="mt-8 flex items-start gap-2 border-t border-line pt-6 text-xs leading-5 text-fg-subtle">
        <x-ui.icon name="lock-closed" class="mt-0.5 size-4" />
        <span>
            Data Anda diproses sesuai
            @if ($privacy)
                <a href="{{ $privacy['url'] }}" class="link">Kebijakan Privasi</a>
            @else
                Kebijakan Privasi
            @endif
            Coding First dan UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi.
        </span>
    </p>
</x-guest-layout>
