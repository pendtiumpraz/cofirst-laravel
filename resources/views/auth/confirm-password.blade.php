{{-- Konfirmasi kata sandi before a sensitive page (password.confirm middleware). --}}
<x-guest-layout title="Konfirmasi kata sandi" heading="Konfirmasi kata sandi"
    subheading="Halaman berikutnya berisi data penting. Demi keamanan, masukkan kembali kata sandi Anda.">

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5"
        x-data="{ submitting: false }"
        x-on:submit="submitting = true"
        x-on:pageshow.window="submitting = false">
        @csrf

        @include('auth.partials.password-input', [
            'name' => 'password',
            'label' => 'Kata sandi',
            'autocomplete' => 'current-password',
            'autofocus' => true,
        ])

        <x-ui.button type="submit" size="lg" block icon="lock-open" loading-when="submitting" loading-text="Memeriksa…">
            Konfirmasi
        </x-ui.button>
    </form>

    <p class="mt-8 text-center text-sm">
        <a href="{{ route('dashboard') }}" class="link inline-flex min-h-11 items-center gap-1.5 font-medium">
            <x-ui.icon name="m-arrow-left" class="size-4" />
            Kembali ke dasbor
        </a>
    </p>
</x-guest-layout>
