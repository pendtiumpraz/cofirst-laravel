{{-- Verifikasi email. Variable: $email (EmailVerificationPromptController). --}}
@php
    $address = $email ?? auth()->user()?->email;
@endphp

<x-guest-layout title="Verifikasi email" heading="Verifikasi email Anda"
    subheading="Satu langkah lagi sebelum Anda dapat memakai akun.">

    <div class="flex items-start gap-4 rounded-lg border border-line bg-surface-2 p-4">
        <span class="grid size-10 shrink-0 place-items-center rounded-md bg-brand-soft text-brand-soft-fg">
            <x-ui.icon name="envelope-open" class="size-5" />
        </span>
        <p class="text-sm leading-6 text-fg-muted">
            Kami telah mengirim tautan verifikasi ke
            <strong class="break-all font-semibold text-fg">{{ $address }}</strong>.
            Buka email tersebut lalu klik tautannya. Belum menerima? Periksa folder spam, atau kirim ulang di bawah.
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <x-ui.alert tone="success" class="mt-6">Tautan verifikasi baru telah dikirim ke email Anda.</x-ui.alert>
    @endif

    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}"
            x-data="{ submitting: false }" x-on:submit="submitting = true" x-on:pageshow.window="submitting = false">
            @csrf
            <x-ui.button type="submit" icon="arrow-path" class="w-full sm:w-auto" loading-when="submitting" loading-text="Mengirim…">
                Kirim ulang email verifikasi
            </x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}" data-cf-logout>
            @csrf
            <x-ui.button type="submit" variant="ghost" icon="arrow-right-start-on-rectangle" class="w-full sm:w-auto">
                Keluar
            </x-ui.button>
        </form>
    </div>
</x-guest-layout>
