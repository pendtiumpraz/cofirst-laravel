{{--
    Shared user form (create/edit).
    Expects: $user (User|null), $roleOptions (name => [label, description]), $canChangeRoles, $isSelf.
--}}
@php
    $editing = $user !== null && $user->exists;
    $currentRoles = $editing ? $user->roles->pluck('name')->all() : ['student'];
    $initialRoles = old('roles', $currentRoles);
    $profile = $editing ? $user->profile : null;
    $pick = fn ($users) => collect($users)->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'hint' => ''])->values()->all();
    $children = old('children') !== null
        ? $pick(App\Models\User::whereIn('id', (array) old('children'))->get(['id', 'name']))
        : ($editing ? $pick($user->children) : []);
    $parents = old('parents') !== null
        ? $pick(App\Models\User::whereIn('id', (array) old('parents'))->get(['id', 'name']))
        : ($editing ? $pick($user->parents) : []);
@endphp

<div x-data="{ roles: @js(array_values((array) $initialRoles)), invite: @js((bool) old('send_invite', ! $editing)), familyReady: false }"
    x-init="familyReady = true">

    {{-- Akun --}}
    <section class="form-section" aria-labelledby="sec-akun">
        <div class="form-section-intro">
            <h2 id="sec-akun" class="card-title">Akun</h2>
            <p class="mt-1 text-sm text-fg-muted">Nama dan email dipakai untuk masuk serta menerima jadwal, laporan dan tagihan.</p>
        </div>
        <div class="form-section-body">
            <x-ui.field label="Nama" for="name" required>
                <x-ui.input name="name" :value="$user?->name" autocomplete="name" required maxlength="120" />
            </x-ui.field>
            <x-ui.field label="Email" for="email" required hint="Gunakan email asli pemilik akun (untuk anak: email orang tua/wali bila belum punya).">
                <x-ui.input name="email" type="email" :value="$user?->email" autocomplete="email" icon="envelope" required maxlength="190" />
            </x-ui.field>
            @unless ($isSelf ?? false)
                <x-ui.toggle name="is_active" unchecked-value="0" :checked="$editing ? $user->is_active : true"
                    label="Akun aktif" description="Akun nonaktif tidak dapat masuk; sesi yang sedang berjalan langsung diakhiri." />
            @endunless
        </div>
    </section>

    {{-- Peran --}}
    <section class="form-section" aria-labelledby="sec-peran">
        <div class="form-section-intro">
            <h2 id="sec-peran" class="card-title">Peran</h2>
            <p class="mt-1 text-sm text-fg-muted">Satu akun boleh memiliki beberapa peran, mis. orang tua yang juga siswa dewasa.</p>
        </div>
        <div class="form-section-body">
            @if (! ($canChangeRoles ?? true))
                <x-ui.alert tone="neutral" icon="lock-closed">
                    @if ($isSelf ?? false)
                        Peran akun Anda sendiri tidak dapat diubah dari sini. Minta superadmin lain bila perlu.
                    @else
                        Anda tidak dapat mengubah peran akun ini.
                    @endif
                </x-ui.alert>
                <div class="flex flex-wrap gap-2">
                    @foreach ($currentRoles as $role)
                        <x-ui.badge outline :tone="App\Support\Admin\AccessControl::roleTone($role)">{{ App\Support\Admin\AccessControl::roleLabel($role) }}</x-ui.badge>
                    @endforeach
                </div>
            @else
                <fieldset>
                    <legend class="label">Peran akun <span class="label-required" aria-hidden="true">*</span></legend>
                    <div class="mt-1 grid gap-3 sm:grid-cols-2">
                        @foreach ($roleOptions as $value => $option)
                            <x-ui.checkbox name="roles[]" :value="$value" :checked="in_array($value, $currentRoles, true)"
                                :label="$option['label']" :description="$option['description']" x-model="roles" />
                        @endforeach
                    </div>
                    @foreach (['roles', 'roles.*'] as $key)
                        @error($key)
                            <p class="error-text mt-2"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>
                        @enderror
                    @endforeach
                    @if (! array_key_exists('admin', $roleOptions))
                        <p class="hint mt-2">Peran Admin dan Superadmin hanya dapat diberikan oleh superadmin.</p>
                    @endif
                </fieldset>
            @endif
        </div>
    </section>

    {{-- Kata sandi --}}
    <section class="form-section" aria-labelledby="sec-sandi">
        <div class="form-section-intro">
            <h2 id="sec-sandi" class="card-title">Kata sandi</h2>
            <p class="mt-1 text-sm text-fg-muted">
                @if ($editing)
                    Kosongkan bila tidak ingin mengubah. Untuk akun orang lain, lebih aman mengirim tautan atur ulang dari halaman detail.
                @else
                    Sebaiknya kirim tautan agar pemilik akun membuat kata sandinya sendiri.
                @endif
            </p>
        </div>
        <div class="form-section-body">
            @unless ($editing)
                <x-ui.toggle name="send_invite" unchecked-value="0" :checked="true" x-model="invite"
                    label="Kirim tautan atur kata sandi ke email" description="Tautan berlaku 60 menit; pengguna dapat meminta ulang dari halaman masuk." />
            @endunless
            <div class="grid gap-5 sm:grid-cols-2" @unless ($editing) x-show="! invite" x-cloak @endunless>
                <x-ui.field :label="$editing ? 'Kata sandi baru' : 'Kata sandi'" for="password" :optional="$editing"
                    :hint="'Minimal '.max(8, (int) setting('security.password_min_length', 8)).' karakter'.(setting('security.password_require_mixed_case', true) ? ', huruf besar & kecil' : '').(setting('security.password_require_numbers', true) ? ', angka' : '').'.'">
                    <x-ui.input name="password" type="password" autocomplete="new-password" />
                </x-ui.field>
                <x-ui.field label="Ulangi kata sandi" for="password_confirmation" :error="false">
                    <x-ui.input name="password_confirmation" type="password" autocomplete="new-password" />
                </x-ui.field>
            </div>
        </div>
    </section>

    {{-- Profil --}}
    <section class="form-section" aria-labelledby="sec-profil">
        <div class="form-section-intro">
            <h2 id="sec-profil" class="card-title">Profil <span class="text-sm font-normal text-fg-subtle">(opsional)</span></h2>
            <p class="mt-1 text-sm text-fg-muted">Isi hanya yang diperlukan. Tanggal lahir menentukan apakah siswa termasuk anak sehingga data dan publikasinya memerlukan persetujuan orang tua/wali (UU PDP).</p>
        </div>
        <div class="form-section-body">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Nama lengkap" for="full_name" optional hint="Untuk sertifikat. Default: sama dengan nama.">
                    <x-ui.input name="full_name" :value="$profile?->full_name" maxlength="150" />
                </x-ui.field>
                <x-ui.field label="Nomor telepon/WhatsApp" for="phone_number" optional>
                    <x-ui.input name="phone_number" type="tel" inputmode="tel" :value="$profile?->phone_number" placeholder="0812 3456 7890" />
                </x-ui.field>
                <x-ui.field label="Tanggal lahir" for="birth_date" optional>
                    <x-ui.input name="birth_date" type="date" :value="$profile?->birth_date?->format('Y-m-d')" :max="now()->subDay()->format('Y-m-d')" />
                </x-ui.field>
                <x-ui.field label="Jenis kelamin" for="gender" optional>
                    <x-ui.select name="gender" :value="$profile?->gender" placeholder="Tidak diisi" :options="['male' => 'Laki-laki', 'female' => 'Perempuan']" />
                </x-ui.field>
            </div>
            <x-ui.field label="Alamat" for="address" optional hint="Hanya bila diperlukan, mis. untuk kelas kunjungan rumah.">
                <x-ui.textarea name="address" rows="2" :value="$profile?->address" maxlength="500" />
            </x-ui.field>
            <x-ui.field label="Bio singkat" for="bio" optional hint="Untuk pengajar: pengalaman dan keahlian yang tampil di profil.">
                <x-ui.textarea name="bio" rows="3" :value="$profile?->bio" maxlength="1000" />
            </x-ui.field>
        </div>
    </section>

    {{-- Foto --}}
    <section class="form-section" aria-labelledby="sec-foto">
        <div class="form-section-intro">
            <h2 id="sec-foto" class="card-title">Foto profil <span class="text-sm font-normal text-fg-subtle">(opsional)</span></h2>
            <p class="mt-1 text-sm text-fg-muted">Hanya terlihat oleh pengguna yang masuk. Foto anak tidak pernah tampil di halaman publik tanpa persetujuan orang tua.</p>
        </div>
        <div class="form-section-body">
            @if ($editing && $user->profile_photo_path)
                <div class="flex items-center gap-4">
                    <x-ui.avatar :user="$user" size="lg" />
                    <x-ui.checkbox name="remove_photo" value="1" label="Hapus foto saat ini" />
                </div>
            @endif
            <x-ui.field label="Unggah foto" for="photo">
                <x-ui.file name="photo" accept="image/jpeg,image/png,image/webp" :max-kb="2048" hint="JPG/PNG/WEBP · maks. 2 MB" preview />
            </x-ui.field>
        </div>
    </section>

    {{-- Keluarga --}}
    <section class="form-section" aria-labelledby="sec-keluarga" x-show="roles.includes('parent') || roles.includes('student')" x-cloak>
        <div class="form-section-intro">
            <h2 id="sec-keluarga" class="card-title">Keluarga</h2>
            <p class="mt-1 text-sm text-fg-muted">Orang tua/wali yang tertaut dapat melihat jadwal, laporan, kehadiran dan tagihan anak. Tautkan hanya setelah hubungannya diverifikasi.</p>
        </div>
        <div class="form-section-body">
            <input type="hidden" name="family_form" value="1" disabled x-bind:disabled="! familyReady">
            <div x-show="roles.includes('parent')">
                <x-ui.field label="Anak" for="children-picker" optional hint="Cari akun siswa berdasarkan nama atau email." :error="'children'">
                    @include('admin.users.partials.user-picker', ['name' => 'children[]', 'id' => 'children-picker', 'role' => 'student',
                        'selected' => $children, 'placeholder' => 'Cari nama siswa…', 'enableWhen' => "roles.includes('parent')"])
                </x-ui.field>
                @error('children.*')<p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>@enderror
            </div>
            <div x-show="roles.includes('student')">
                <x-ui.field label="Orang tua/wali" for="parents-picker" optional hint="Cari akun orang tua berdasarkan nama atau email." :error="'parents'">
                    @include('admin.users.partials.user-picker', ['name' => 'parents[]', 'id' => 'parents-picker', 'role' => 'parent',
                        'selected' => $parents, 'placeholder' => 'Cari nama orang tua…', 'enableWhen' => "roles.includes('student')"])
                </x-ui.field>
                @error('parents.*')<p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>@enderror
            </div>
            <x-ui.checkbox name="guardian_verified" value="1"
                label="Hubungan orang tua/wali sudah saya verifikasi"
                description="Mis. lewat kartu keluarga/akta kelahiran atau konfirmasi langsung dengan orang tua. Wajib dicentang saat menambah tautan baru; tercatat di log audit." />
            @error('guardian_verified')<p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>@enderror
        </div>
    </section>
</div>
