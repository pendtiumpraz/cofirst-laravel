{{--
    Keamanan › Ganti kata sandi (PUT password.update, error bag "updatePassword").
    Variables: $passwordRequirements, $passwordSummary.
--}}
@php
    $passwordErrors = $errors->getBag('updatePassword');
    // Reason: after a failed submit, focus the first invalid field so the page scrolls back to this card.
    $firstInvalid = collect(['current_password', 'password', 'password_confirmation'])
        ->first(fn ($field) => $passwordErrors->has($field));
@endphp

<section id="keamanan" class="form-section scroll-mt-24" aria-labelledby="keamanan-title">
    <div class="form-section-intro">
        <h2 id="keamanan-title" class="section-title">Keamanan</h2>
        <p class="mt-1.5 text-sm leading-6 text-fg-muted">
            Gunakan kata sandi yang panjang dan tidak dipakai di layanan lain. {{ $passwordSummary }}
        </p>
        <p class="mt-3 flex items-start gap-2 text-sm leading-6 text-fg-muted">
            <x-ui.icon name="information-circle" class="mt-0.5 size-[1.125rem] text-info" />
            <span>Setelah kata sandi diganti, semua perangkat lain otomatis dikeluarkan dari akun Anda.</span>
        </p>
    </div>

    <div class="form-section-body">
        <x-ui.card title="Ganti kata sandi">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5"
                x-data="{ submitting: false }"
                x-on:submit="submitting = true"
                x-on:pageshow.window="submitting = false">
                @csrf
                @method('put')

                {{-- Helps password managers link the new password to this account. --}}
                <input type="text" name="username" value="{{ auth()->user()->email }}" autocomplete="username" class="sr-only" tabindex="-1" aria-hidden="true" readonly>

                @include('auth.partials.password-input', [
                    'name' => 'current_password',
                    'id' => 'update_password_current_password',
                    'label' => 'Kata sandi saat ini',
                    'autocomplete' => 'current-password',
                    'bag' => 'updatePassword',
                    'autofocus' => $firstInvalid === 'current_password',
                ])

                @include('auth.partials.password-input', [
                    'name' => 'password',
                    'id' => 'update_password_password',
                    'label' => 'Kata sandi baru',
                    'autocomplete' => 'new-password',
                    'bag' => 'updatePassword',
                    'meter' => true,
                    'requirements' => $passwordRequirements,
                    'autofocus' => $firstInvalid === 'password',
                ])

                @include('auth.partials.password-input', [
                    'name' => 'password_confirmation',
                    'id' => 'update_password_password_confirmation',
                    'label' => 'Ulangi kata sandi baru',
                    'autocomplete' => 'new-password',
                    'bag' => 'updatePassword',
                    'autofocus' => $firstInvalid === 'password_confirmation',
                ])

                <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-end">
                    <x-ui.button type="submit" icon="key" loading-when="submitting" loading-text="Menyimpan…" class="w-full sm:w-auto">
                        Perbarui kata sandi
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</section>
