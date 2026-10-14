{{--
    Buat kata sandi baru (from the emailed link).
    Variables: $token, $email, $requirements, $summary (NewPasswordController::create).
--}}
<x-guest-layout title="Buat kata sandi baru" heading="Buat kata sandi baru"
    subheading="Setelah disimpan, semua sesi lama akun ini diakhiri dan Anda masuk kembali dengan kata sandi baru.">

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5"
        x-data="{ submitting: false }"
        x-on:submit="submitting = true"
        x-on:pageshow.window="submitting = false">
        @csrf
        <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">

        <x-ui.field label="Email" for="email">
            <x-ui.input name="email" type="email" icon="envelope" inputmode="email"
                :value="$email ?? request('email')" autocomplete="username" required />
        </x-ui.field>

        @include('auth.partials.password-input', [
            'name' => 'password',
            'label' => 'Kata sandi baru',
            'autocomplete' => 'new-password',
            'meter' => true,
            'requirements' => $requirements ?? \App\Support\Auth\PasswordPolicy::requirements(),
            'autofocus' => true,
        ])

        @include('auth.partials.password-input', [
            'name' => 'password_confirmation',
            'label' => 'Ulangi kata sandi baru',
            'autocomplete' => 'new-password',
        ])

        <x-ui.button type="submit" size="lg" block icon="check" loading-when="submitting" loading-text="Menyimpan…">
            Simpan kata sandi baru
        </x-ui.button>
    </form>

    <p class="mt-8 text-center text-sm">
        <a href="{{ route('login') }}" class="link inline-flex min-h-11 items-center gap-1.5 font-medium">
            <x-ui.icon name="m-arrow-left" class="size-4" />
            Kembali ke halaman masuk
        </a>
    </p>
</x-guest-layout>
