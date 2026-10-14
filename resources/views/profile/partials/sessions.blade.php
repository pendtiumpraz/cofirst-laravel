{{--
    Sesi aktif: devices signed in to this account (sessions table) + "Keluar dari perangkat lain".
    Variables: $sessions (UserSessionService::forUser), $sessionsSupported.
--}}
@php
    $sessionErrors = $errors->getBag('logoutOtherDevices');
    $others = $sessions->where('is_current', false)->count();
@endphp

<section id="sesi" class="form-section scroll-mt-24" aria-labelledby="sesi-title">
    <div class="form-section-intro">
        <h2 id="sesi-title" class="section-title">Sesi aktif</h2>
        <p class="mt-1.5 text-sm leading-6 text-fg-muted">
            Perangkat yang sedang masuk ke akun Anda. Jika ada yang tidak Anda kenali, keluarkan semua perangkat lain
            lalu ganti kata sandi.
        </p>
    </div>

    <div class="form-section-body">
        <x-ui.card padding="none">
            @if ($sessionsSupported && $sessions->isNotEmpty())
                <ul role="list" class="divide-y divide-line">
                    @foreach ($sessions as $session)
                        <li class="flex items-start gap-4 px-5 py-4 sm:px-6">
                            <span class="grid size-10 shrink-0 place-items-center rounded-md bg-surface-2 text-fg-muted">
                                <x-ui.icon :name="$session->icon" class="size-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-fg">
                                    <span>{{ $session->browser }} di {{ $session->platform }}</span>
                                    @if ($session->is_current)
                                        <x-ui.badge tone="success" dot>Perangkat ini</x-ui.badge>
                                    @endif
                                </p>
                                <p class="mt-0.5 flex flex-wrap gap-x-2 text-[0.8125rem] text-fg-muted">
                                    @if ($session->ip_address)
                                        <span class="font-mono">{{ $session->ip_address }}</span>
                                        <span aria-hidden="true">·</span>
                                    @endif
                                    @if ($session->is_current)
                                        <span>Aktif sekarang</span>
                                    @else
                                        <span title="{{ $session->last_active_at->translatedFormat('j F Y, H.i') }}">
                                            Terakhir aktif {{ $session->last_active_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-5 sm:p-6">
                    <x-ui.empty-state icon="computer-desktop" title="Daftar perangkat belum tersedia"
                        description="Riwayat perangkat muncul setelah sesi disimpan di basis data. Anda tetap dapat mengeluarkan semua perangkat lain." />
                </div>
            @endif

            <x-slot:footer class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[0.8125rem] text-fg-muted">
                    @if ($sessionsSupported)
                        {{ $others > 0 ? "{$others} perangkat lain sedang masuk." : 'Tidak ada perangkat lain yang sedang masuk.' }}
                    @else
                        Sesi “ingat saya” di perangkat lain juga akan diakhiri.
                    @endif
                </p>
                <x-ui.button variant="secondary" icon="arrow-right-start-on-rectangle" class="w-full sm:w-auto"
                    x-data x-on:click="$dispatch('open-modal', 'keluar-perangkat-lain')">
                    Keluar dari perangkat lain
                </x-ui.button>
            </x-slot:footer>
        </x-ui.card>

        <x-ui.modal name="keluar-perangkat-lain" title="Keluar dari perangkat lain?" max-width="md"
            description="Semua perangkat selain yang sedang Anda pakai akan dikeluarkan dari akun ini."
            :show="$sessionErrors->isNotEmpty()">
            <form id="form-keluar-perangkat-lain" method="POST" action="{{ route('profile.sessions.destroy') }}">
                @csrf
                @method('delete')

                @include('auth.partials.password-input', [
                    'name' => 'password',
                    'id' => 'logout_other_devices_password',
                    'label' => 'Kata sandi Anda',
                    'autocomplete' => 'current-password',
                    'bag' => 'logoutOtherDevices',
                    'hint' => 'Masukkan kata sandi untuk mengonfirmasi.',
                ])
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" x-on:click="hide()">Batal</x-ui.button>
                <x-ui.button type="submit" form="form-keluar-perangkat-lain" icon="arrow-right-start-on-rectangle">
                    Keluarkan perangkat lain
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    </div>
</section>
