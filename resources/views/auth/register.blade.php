{{--
    Self-registration (siswa / orang tua). Variables from RegisteredUserController::create:
    $closed, $roles, $role, $classes, $optional (ConsentPurpose[]), $threshold, $parentalRequired,
    $notice, $terms (current PolicyDocument meta or null), $prefillEmail, $invitation.
--}}
@php
    use App\Services\Privacy\PolicyService;

    $closed = $closed ?? true;
@endphp

@if ($closed)
    <x-guest-layout title="Pendaftaran ditutup" heading="Pendaftaran sedang ditutup"
        subheading="Saat ini akun baru hanya dibuat oleh admin sekolah.">
        <x-ui.alert tone="info" icon="information-circle">
            Ingin mendaftar kelas? Hubungi kami di
            <a href="mailto:{{ setting('general.contact_email') }}" class="link font-medium">{{ setting('general.contact_email') }}</a>
            @if ($wa = preg_replace('/\D+/', '', (string) setting('general.whatsapp', '')))
                atau <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener noreferrer" class="link font-medium">WhatsApp</a>
            @endif
            dan tim kami akan membantu.
        </x-ui.alert>
        <p class="mt-8 text-center text-sm text-fg-muted">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="link inline-flex min-h-11 items-center font-medium">Masuk</a>
        </p>
    </x-guest-layout>
@else
    @php
        $roleOptions = array_intersect_key([
            'student' => ['label' => 'Siswa', 'description' => 'Untuk belajar di kelas. Di bawah '.$threshold.' tahun perlu persetujuan orang tua/wali.', 'icon' => 'academic-cap'],
            'parent' => ['label' => 'Orang tua / wali', 'description' => 'Untuk memantau jadwal, laporan, dan tagihan anak.', 'icon' => 'users'],
        ], array_flip($roles));
        $classOptions = $classes->mapWithKeys(fn ($c) => [$c->id => trim(($c->course?->name ? $c->course->name.' — ' : '').$c->name)
            .' · mulai '.PolicyService::date($c->start_date).' · sisa '.$c->availableSeats().' kursi'])->all();
        $policyLink = fn ($route, $label, $doc) => Route::has($route)
            ? '<a href="'.e(route($route)).'" target="_blank" rel="noopener" class="link font-medium">'.e($label).($doc ? ' (versi '.e($doc->version).')' : '').'</a>'
            : e($label);
    @endphp

    <x-guest-layout title="Daftar" heading="Buat akun {{ setting('general.site_name', 'Coding First') }}"
        subheading="Untuk siswa dan orang tua. Akun pengajar dan staf dibuat oleh admin sekolah.">

        @if ($errors->any())
            <x-ui.alert tone="danger" title="Periksa kembali isian Anda" class="mb-6">
                Ada {{ $errors->count() }} isian yang perlu diperbaiki. Pesan ada di bawah setiap kolom.
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-8" novalidate
            x-data="{
                role: @js(old('role', $role)),
                birth: @js(old('birth_date', '')),
                threshold: {{ (int) $threshold }},
                parental: {{ $parentalRequired ? 'true' : 'false' }},
                submitting: false,
                get age() {
                    if (! this.birth) return null;
                    const b = new Date(this.birth + 'T00:00:00'), t = new Date();
                    if (isNaN(b)) return null;
                    let a = t.getFullYear() - b.getFullYear();
                    if (t.getMonth() < b.getMonth() || (t.getMonth() === b.getMonth() && t.getDate() < b.getDate())) a--;
                    return a;
                },
                get isChild() { return this.role === 'student' && this.parental && this.age !== null && this.age < this.threshold; },
            }"
            x-on:submit="submitting = true"
            x-on:pageshow.window="submitting = false">
            @csrf
            @if ($invitation)
                <input type="hidden" name="undangan" value="{{ $invitation }}">
            @endif

            {{-- Honeypot: hidden from people and assistive technology, filled by bots. --}}
            <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                <label for="website">Situs web</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            {{-- 1. Jenis akun --}}
            @if (count($roleOptions) > 1)
                <x-ui.radio-cards name="role" legend="Saya mendaftar sebagai" :options="$roleOptions" :value="$role" :cols="2" required
                    x-on:change="if ($event.target.name === 'role') role = $event.target.value" />
            @else
                <input type="hidden" name="role" value="{{ array_key_first($roleOptions) }}">
            @endif

            {{-- 2. Data diri --}}
            <fieldset class="space-y-5">
                <legend class="eyebrow mb-4">Data diri</legend>

                <x-ui.field label="Nama lengkap" for="name" required>
                    <x-ui.input name="name" autocomplete="name" maxlength="120" required autofocus />
                </x-ui.field>

                <x-ui.field label="Email" for="email" hint="Dipakai untuk masuk dan menerima laporan. Kami tidak membagikannya." required>
                    <x-ui.input name="email" type="email" icon="envelope" inputmode="email" autocomplete="email" maxlength="191" :value="old('email', $prefillEmail)" required />
                </x-ui.field>

                <x-ui.field label="Nomor WhatsApp" for="phone" optional hint="Hanya untuk pengingat jadwal dan tagihan.">
                    <x-ui.input name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="0812 3456 7890" maxlength="20" />
                </x-ui.field>

                @if (in_array('student', $roles, true))
                    <div x-show="role === 'student'" class="space-y-5">
                        <x-ui.field label="Tanggal lahir" for="birth_date" required
                            hint="Kami memerlukannya untuk menentukan kelas yang sesuai dan apakah perlu persetujuan orang tua/wali.">
                            <x-ui.input name="birth_date" type="date" max="{{ now()->subDay()->toDateString() }}" min="1920-01-01" x-model="birth" />
                        </x-ui.field>

                        @if ($classes->isNotEmpty())
                            <x-ui.field label="Kelas yang ingin diikuti" for="class_id" optional
                                hint="Kursi ditahan sampai pembayaran dikonfirmasi admin. Bisa juga dipilih nanti.">
                                <x-ui.select name="class_id" :options="$classOptions" placeholder="Pilih nanti" />
                            </x-ui.field>
                        @endif
                    </div>
                @endif
            </fieldset>

            {{-- 3. Orang tua / wali (children only) --}}
            @if (in_array('student', $roles, true) && $parentalRequired)
                <fieldset x-show="isChild" x-cloak class="space-y-5 rounded-lg border border-line bg-surface-2 p-4 sm:p-5">
                    <legend class="sr-only">Orang tua atau wali</legend>
                    <div class="flex items-start gap-3">
                        <x-ui.icon name="hand-raised" class="mt-0.5 size-5 shrink-0 text-brand-text" />
                        <div>
                            <p class="text-sm font-semibold text-fg">Persetujuan orang tua/wali diperlukan</p>
                            <p class="mt-1 text-[0.8125rem] leading-relaxed text-fg-muted">
                                Karena usiamu di bawah {{ $threshold }} tahun, kami akan mengirim email kepada orang tua/walimu.
                                Akunmu aktif setelah mereka menyetujui (UU PDP Pasal 25).
                            </p>
                        </div>
                    </div>

                    <x-ui.field label="Nama orang tua/wali" for="guardian_name" required>
                        <x-ui.input name="guardian_name" autocomplete="off" maxlength="120" />
                    </x-ui.field>

                    <x-ui.field label="Email orang tua/wali" for="guardian_email" required hint="Harus berbeda dari emailmu.">
                        <x-ui.input name="guardian_email" type="email" icon="envelope" inputmode="email" autocomplete="off" maxlength="191" />
                    </x-ui.field>

                    <x-ui.field label="Hubungan" for="guardian_relationship" optional>
                        <x-ui.select name="guardian_relationship" :options="\App\Models\Privacy\ParentalConsentRequest::RELATIONSHIPS" placeholder="Pilih" />
                    </x-ui.field>
                </fieldset>
            @endif

            {{-- 4. Kata sandi --}}
            <fieldset class="space-y-5">
                <legend class="eyebrow mb-4">Keamanan akun</legend>
                @include('auth.partials.password-input', [
                    'name' => 'password',
                    'label' => 'Kata sandi',
                    'autocomplete' => 'new-password',
                    'meter' => true,
                    'requirements' => \App\Support\Auth\PasswordPolicy::requirements(),
                ])
                @include('auth.partials.password-input', [
                    'name' => 'password_confirmation',
                    'label' => 'Ulangi kata sandi',
                    'autocomplete' => 'new-password',
                ])
            </fieldset>

            {{-- 5. Pemberitahuan & persetujuan (UU PDP Pasal 21-22) --}}
            <fieldset class="space-y-4">
                <legend class="eyebrow mb-4">Privasi & persetujuan</legend>

                <x-ui.field for="accept_policies" error="accept_policies">
                    <x-ui.checkbox name="accept_policies" value="1" required>
                        Saya telah membaca dan menyetujui {!! $policyLink('privacy.notice', 'Kebijakan Privasi', $notice) !!}
                        serta {!! $policyLink('legal.terms', 'Syarat & Ketentuan', $terms) !!}.
                    </x-ui.checkbox>
                </x-ui.field>

                @if ($optional->isNotEmpty())
                    <div x-show="! isChild" class="space-y-3">
                        <p class="text-sm font-medium text-fg">Persetujuan opsional</p>
                        <p class="-mt-2 text-[0.8125rem] text-fg-subtle">Tidak wajib dan tidak memengaruhi layanan. Dapat diubah kapan saja di Pusat Privasi.</p>
                        @foreach ($optional as $purpose)
                            @php $targets = array_values(array_intersect($purpose->applies_to ?: $roles, $roles)); @endphp
                            @continue($targets === [])
                            <div x-show="@js($targets).includes(role)">
                                <x-ui.checkbox name="consents[]" :value="$purpose->key" :label="$purpose->name" :description="$purpose->description" />
                            </div>
                        @endforeach
                    </div>
                @endif

                <p x-show="isChild" x-cloak class="text-[0.8125rem] leading-relaxed text-fg-subtle">
                    Persetujuan tambahan, misalnya publikasi karya atau nama di papan peringkat, dipilih oleh orang tua/walimu.
                    @if (Route::has('privacy.children'))
                        <a href="{{ route('privacy.children') }}" target="_blank" rel="noopener" class="link font-medium">Baca penjelasan untuk anak</a>.
                    @endif
                </p>
            </fieldset>

            <x-ui.button type="submit" size="lg" block icon="user-plus" loading-when="submitting" loading-text="Membuat akun…">
                <span x-text="isChild ? 'Daftar & minta persetujuan' : 'Buat akun'">Buat akun</span>
            </x-ui.button>
        </form>

        <p class="mt-8 text-center text-sm text-fg-muted">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="link inline-flex min-h-11 items-center font-medium">Masuk</a>
        </p>
    </x-guest-layout>
@endif
