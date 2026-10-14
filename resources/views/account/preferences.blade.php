{{--
    Akun › Preferensi — the signed-in user's own appearance, language and email choices
    (users.preferences). Appearance changes preview live on <html> before saving.
    Data: $user, $values, $allowTheme, $allowColorMode, $emailOptions, $themeOptions.
--}}
@php
    $colorModes = [
        'light' => ['label' => 'Terang', 'icon' => 'sun'],
        'dark' => ['label' => 'Gelap', 'icon' => 'moon'],
        'system' => ['label' => 'Ikuti perangkat', 'icon' => 'computer-desktop'],
    ];
    $densities = [
        'comfortable' => ['label' => 'Lega', 'description' => 'Jarak longgar dan baris tinggi — nyaman dibaca.'],
        'compact' => ['label' => 'Rapat', 'description' => 'Lebih banyak data di satu layar. Di layar sentuh, area sentuh tetap 44px.'],
    ];
    $textSizes = \App\Support\Settings\UserPreferences::TEXT_SIZES;
    $breadcrumbs = Route::has('profile.edit')
        ? ['Akun' => route('profile.edit'), 'Preferensi' => null]
        : ['Akun' => null, 'Preferensi' => null];
@endphp

<x-app-layout title="Preferensi">
    <x-ui.page-header title="Preferensi"
        subtitle="Atur tampilan, bahasa dan email yang Anda terima. Pilihan ini hanya berlaku untuk akun Anda."
        :breadcrumbs="$breadcrumbs" />

    <form method="POST" action="{{ route('account.preferences.update') }}" novalidate
        x-data="{ dirty: false, saving: false }"
        x-on:input="dirty = true"
        x-on:change="dirty = true"
        x-on:submit="saving = true">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                {{-- Tampilan --}}
                <section aria-labelledby="pref-tampilan" class="form-section"
                    x-on:change="
                        const t = $event.target;
                        if (t.name === 'color_mode') window.CF?.colorMode?.set(t.value);
                        if (t.name === 'theme') document.documentElement.dataset.theme = t.value;
                        if (t.name === 'density') document.documentElement.dataset.density = t.value;
                        if (t.name === 'text_size') document.documentElement.dataset.text = t.value;
                        if (t.name === 'reduce_motion' && t.type === 'checkbox') document.documentElement.classList.toggle('reduce-motion', t.checked);
                    ">
                    <div class="form-section-intro">
                        <h2 id="pref-tampilan" class="text-base font-semibold text-fg">Tampilan</h2>
                        <p class="mt-1 text-sm text-fg-muted">Perubahan langsung terlihat di halaman ini. Simpan agar berlaku di semua perangkat Anda.</p>
                    </div>
                    <div class="form-section-body">
                        @if ($allowColorMode)
                            <x-ui.radio-cards name="color_mode" legend="Mode warna" :cols="3" :value="$values['color_mode']"
                                :options="$colorModes" hint="“Ikuti perangkat” mengikuti pengaturan terang/gelap ponsel atau komputer Anda." />
                        @else
                            <x-ui.alert tone="neutral" icon="swatch">Mode warna mengikuti pengaturan lembaga.</x-ui.alert>
                        @endif

                        @if ($allowTheme)
                            <fieldset class="field">
                                <legend class="label mb-1.5">Tema</legend>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    @foreach ($themeOptions as $theme => $option)
                                        <label for="theme_{{ $theme }}" class="flex cursor-pointer flex-col overflow-hidden rounded-lg border border-line bg-surface transition-colors duration-fast motion-reduce:transition-none hover:border-line-strong has-[:checked]:border-brand has-[:checked]:ring-1 has-[:checked]:ring-brand has-[:focus-visible]:outline has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-ring">
                                            <span class="block border-b border-line bg-surface-2 p-3" aria-hidden="true">
                                                @include('admin.settings.previews.theme', ['option' => $theme])
                                            </span>
                                            <span class="flex items-start gap-3 p-4">
                                                <input type="radio" id="theme_{{ $theme }}" name="theme" value="{{ $theme }}" class="check-input mt-0.5 shrink-0"
                                                    @checked(old('theme', $values['theme']) === $theme)>
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-semibold text-fg">{{ $option[0] ?? $option['label'] ?? $theme }}</span>
                                                    <span class="mt-1 block text-[0.8125rem] leading-relaxed text-fg-muted">{{ $option[1] ?? $option['description'] ?? '' }}</span>
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('theme')
                                    <p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>
                                @enderror
                            </fieldset>
                        @endif

                        <x-ui.radio-cards name="density" legend="Kerapatan tampilan" :cols="2" :value="$values['density']" :options="$densities" />

                        <fieldset class="field">
                            <legend class="label mb-1.5">Ukuran teks</legend>
                            <div class="segmented flex-wrap self-start">
                                @foreach ($textSizes as $size => $label)
                                    <label class="segmented-item min-h-11 cursor-pointer px-4 has-[:checked]:bg-surface has-[:checked]:text-fg has-[:checked]:shadow-1 has-[:checked]:ring-1 has-[:checked]:ring-line has-[:focus-visible]:outline has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-ring">
                                        <input type="radio" name="text_size" value="{{ $size }}" class="sr-only" @checked(old('text_size', $values['text_size']) === $size)>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            @error('text_size')
                                <p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>
                            @enderror
                        </fieldset>

                        <x-ui.toggle name="reduce_motion" label="Kurangi animasi"
                            description="Matikan transisi dan gerakan dekoratif. Disarankan bila animasi membuat pusing."
                            :checked="$values['reduce_motion']" unchecked-value="0" />
                    </div>
                </section>

                {{-- Bahasa --}}
                <section aria-labelledby="pref-bahasa" class="form-section">
                    <div class="form-section-intro">
                        <h2 id="pref-bahasa" class="text-base font-semibold text-fg">Bahasa</h2>
                        <p class="mt-1 text-sm text-fg-muted">Dipakai untuk pesan sistem, validasi formulir, dan format tanggal.</p>
                    </div>
                    <div class="form-section-body">
                        <x-ui.field label="Bahasa" for="locale"
                            hint="Antarmuka utama saat ini berbahasa Indonesia; pilihan English mengubah pesan sistem dan nama bulan.">
                            <div class="sm:max-w-xs">
                                <x-ui.select name="locale" :options="\App\Support\Settings\UserPreferences::LOCALES" :value="$values['locale']" />
                            </div>
                        </x-ui.field>
                    </div>
                </section>

                {{-- Notifikasi --}}
                <section aria-labelledby="pref-notifikasi" class="form-section">
                    <div class="form-section-intro">
                        <h2 id="pref-notifikasi" class="text-base font-semibold text-fg">Notifikasi email</h2>
                        <p class="mt-1 text-sm text-fg-muted">
                            Pilih email yang ingin Anda terima di <span class="font-medium text-fg">{{ $user->email }}</span>.
                            Email penting tentang akun dan keamanan tetap dikirim.
                        </p>
                    </div>
                    <div class="form-section-body">
                        @foreach ($emailOptions as $type => $option)
                            <div>
                                <x-ui.toggle name="notifications[email][{{ $type }}]" :id="'notify_'.$type"
                                    :label="$option['label']"
                                    :description="$option['available'] ? $option['description'] : 'Saat ini dinonaktifkan untuk semua pengguna oleh admin.'"
                                    :checked="$option['enabled'] && $option['available']"
                                    :unchecked-value="$option['available'] ? '0' : null"
                                    :disabled="! $option['available']" />
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>

        {{-- Sticky save bar; sits above the mobile bottom tab bar (html[data-mobile-nav=tabs], below lg). --}}
        <div class="sticky bottom-0 z-20 -mx-4 mt-6 border-t border-line bg-canvas/95 px-4 py-3 backdrop-blur-sm sm:-mx-0 sm:rounded-lg sm:border sm:bg-surface/95 sm:px-5 max-lg:[[data-mobile-nav=tabs]_&]:bottom-[calc(var(--cf-tabbar-h)+env(safe-area-inset-bottom))]">
            <div class="flex items-center justify-between gap-3">
                <p class="min-w-0 text-sm" aria-live="polite">
                    <span x-show="! dirty" class="text-fg-muted">
                        <span class="sm:hidden">Simpan agar berlaku</span>
                        <span class="hidden sm:inline">Preferensi berlaku di semua perangkat setelah disimpan.</span>
                    </span>
                    <span x-show="dirty" x-cloak class="flex items-center gap-2 font-medium text-fg">
                        <span class="size-2 shrink-0 rounded-full bg-warning-solid" aria-hidden="true"></span>
                        <span class="sm:hidden">Belum disimpan</span>
                        <span class="hidden sm:inline">Ada perubahan yang belum disimpan.</span>
                    </span>
                </p>
                <x-ui.button type="submit" icon="check" loading-when="saving" loading-text="Menyimpan…" class="shrink-0">
                    Simpan<span class="hidden sm:inline">&nbsp;preferensi</span>
                </x-ui.button>
            </div>
        </div>
    </form>
</x-app-layout>
