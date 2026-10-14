{{--
    Informasi pribadi (PATCH profile.update).
    Variables: $user, $profile (nullable), $canChangeEmail, $canChangeBirthDate, $privacyUrl.
--}}
@php
    $emailHint = $canChangeEmail
        ? 'Dipakai untuk masuk dan menerima pemberitahuan.'
        : 'Email login siswa dikelola oleh orang tua/wali atau admin.';
    $birthHint = $canChangeBirthDate
        ? null
        : 'Tanggal lahir siswa menentukan perlunya persetujuan orang tua/wali, sehingga hanya dapat dikoreksi oleh admin.';
@endphp

<section id="informasi" class="form-section scroll-mt-24" aria-labelledby="informasi-title">
    <div class="form-section-intro">
        <h2 id="informasi-title" class="section-title">Informasi pribadi</h2>
        <p class="mt-1.5 text-sm leading-6 text-fg-muted">
            Dipakai untuk administrasi kelas, sertifikat, dan komunikasi dengan Anda. Isi seperlunya — kolom bertanda
            <span class="whitespace-nowrap">(opsional)</span> boleh dikosongkan.
        </p>
        <p class="mt-3 text-sm leading-6 text-fg-muted">
            Anda berhak memperbaiki data yang tidak akurat (UU PDP Pasal 6).
            @if ($privacyUrl && (! $canChangeEmail || ! $canChangeBirthDate))
                Untuk data yang terkunci, ajukan koreksi lewat <a href="{{ $privacyUrl }}" class="link">Pusat Privasi</a>.
            @endif
        </p>
    </div>

    <div class="form-section-body">
        <x-ui.card>
            <form method="POST" action="{{ route('profile.update') }}"
                x-data="{ email: @js(old('email', $user->email)), original: @js($user->email), submitting: false }"
                x-on:submit="submitting = true"
                x-on:pageshow.window="submitting = false">
                @csrf
                @method('patch')

                <div class="grid gap-x-5 gap-y-5 sm:grid-cols-2">
                    <x-ui.field label="Nama lengkap" for="name" class="sm:col-span-2" hint="Sesuai identitas — dipakai pada sertifikat.">
                        <x-ui.input name="name" :value="$user->name" autocomplete="name" maxlength="120" required />
                    </x-ui.field>

                    <x-ui.field label="Email" for="email" :hint="$emailHint">
                        @if ($canChangeEmail)
                            <x-ui.input name="email" type="email" icon="envelope" inputmode="email" :value="$user->email"
                                autocomplete="email" maxlength="255" required x-model="email" />
                        @else
                            <x-ui.input id="email" type="email" icon="lock-closed" :value="$user->email" :error="false" readonly />
                        @endif
                    </x-ui.field>

                    <x-ui.field label="Nomor telepon / WhatsApp" for="phone_number" optional hint="Untuk pengingat jadwal dan konfirmasi pembayaran.">
                        <x-ui.input name="phone_number" type="tel" icon="phone" inputmode="tel" :value="$profile?->phone_number"
                            autocomplete="tel" maxlength="20" placeholder="0812 3456 7890" />
                    </x-ui.field>

                    @if ($canChangeEmail)
                        <div class="sm:col-span-2" x-show="email.trim().toLowerCase() !== original.toLowerCase()" x-cloak>
                            @include('auth.partials.password-input', [
                                'name' => 'current_password',
                                'id' => 'profile_current_password',
                                'label' => 'Kata sandi saat ini',
                                'autocomplete' => 'current-password',
                                'required' => false,
                                'hint' => 'Wajib diisi untuk mengganti email login.',
                            ])
                        </div>
                    @endif

                    <x-ui.field label="Tanggal lahir" for="birth_date" optional :hint="$birthHint">
                        @if ($canChangeBirthDate)
                            <x-ui.input name="birth_date" type="date" :value="$profile?->birth_date?->format('Y-m-d')"
                                autocomplete="bday" max="{{ now()->subDay()->format('Y-m-d') }}" />
                        @else
                            <x-ui.input id="birth_date" type="text" icon="lock-closed" :error="false" readonly
                                :value="$profile?->birth_date ? $profile->birth_date->locale('id')->translatedFormat('j F Y') : 'Belum diisi'" />
                        @endif
                    </x-ui.field>

                    <x-ui.field label="Jenis kelamin" for="gender" optional>
                        <x-ui.select name="gender" :value="$profile?->gender" placeholder="Tidak ingin menyebutkan"
                            :options="['male' => 'Laki-laki', 'female' => 'Perempuan']" />
                    </x-ui.field>

                    <x-ui.field label="Alamat" for="address" optional class="sm:col-span-2" hint="Diperlukan bila Anda mengikuti kelas privat di rumah.">
                        <x-ui.textarea name="address" rows="3" :value="$profile?->address" autocomplete="street-address" maxlength="500" />
                    </x-ui.field>

                    <x-ui.field label="Bio singkat" for="bio" optional class="sm:col-span-2" hint="Ceritakan sedikit tentang diri Anda. Maksimal 1.000 karakter.">
                        <x-ui.textarea name="bio" rows="4" :value="$profile?->bio" maxlength="1000" />
                    </x-ui.field>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-end">
                    <x-ui.button type="submit" icon="check" loading-when="submitting" loading-text="Menyimpan…" class="w-full sm:w-auto">
                        Simpan perubahan
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</section>
