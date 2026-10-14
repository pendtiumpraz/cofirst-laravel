{{--
    Profil saya (ProfileController::edit).
    Sections: Informasi pribadi · Foto profil · Keamanan · Sesi aktif · Hapus akun (link to the privacy centre).
--}}
<x-app-layout title="Profil saya">
    <x-ui.page-header title="Profil saya"
        subtitle="Perbarui data pribadi, foto, dan keamanan akun Anda. Perubahan langsung berlaku di seluruh aplikasi.">
        @if ($preferencesUrl || $privacyUrl)
            <x-slot:actions>
                @if ($preferencesUrl)
                    <x-ui.button variant="secondary" icon="adjustments-horizontal" :href="$preferencesUrl">Preferensi</x-ui.button>
                @endif
                @if ($privacyUrl)
                    <x-ui.button variant="secondary" icon="finger-print" :href="$privacyUrl">Privasi &amp; Data Saya</x-ui.button>
                @endif
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @include('profile.partials.section-nav')

    <div class="mt-6">
        @include('profile.partials.summary')
    </div>

    <div class="mt-10">
        @include('profile.partials.personal-information')
        @include('profile.partials.photo')
        @include('profile.partials.password')
        @include('profile.partials.sessions')
        @include('profile.partials.delete-account')
    </div>
</x-app-layout>
