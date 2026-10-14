{{--
    Pilih peran for users whose roles open different dashboards.
    Variables: $user, $options (ActiveRole::options), $active (current/default role).
--}}
<x-guest-layout title="Pilih peran" heading="Masuk sebagai siapa hari ini?"
    :subheading="'Halo, '.$user->name.'. Akun Anda memiliki beberapa peran. Anda bisa menggantinya kapan saja lewat menu akun.'">

    <form method="POST" action="{{ route('role.set') }}" class="space-y-6"
        x-data="{ submitting: false }"
        x-on:submit="submitting = true"
        x-on:pageshow.window="submitting = false">
        @csrf

        <x-ui.radio-cards name="role" legend="Peran untuk sesi ini" :options="$options" :value="$active" required />

        <x-ui.button type="submit" size="lg" block icon-right="arrow-right" loading-when="submitting" loading-text="Membuka dasbor…">
            Lanjutkan
        </x-ui.button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center" data-cf-logout>
        @csrf
        <button type="submit" class="link inline-flex min-h-11 items-center text-sm">Bukan Anda? Keluar</button>
    </form>
</x-guest-layout>
