{{--
    "Pratinjau" of appearance.auth_layout: a sample sign-in page in the guest layout
    (split or centered, following the admin's session preview). Nothing is submitted.
--}}
<x-guest-layout title="Pratinjau halaman masuk" heading="Masuk ke akun Anda"
    subheading="Gunakan email yang terdaftar di {{ setting('general.site_name') }}.">
    <x-ui.alert tone="info" icon="eye" title="Mode pratinjau" class="mb-6">
        Contoh halaman masuk dengan tata letak <strong>{{ $option ?? $layout }}</strong>. Formulir ini tidak mengirim data, dan hanya Anda yang melihat pratinjau ini.
        <x-slot:actions>
            <x-ui.button size="sm" variant="secondary" icon="arrow-left" :href="route('admin.settings.edit', 'appearance')">
                Kembali ke Pengaturan
            </x-ui.button>
        </x-slot:actions>
    </x-ui.alert>

    <div class="space-y-5" aria-label="Contoh formulir masuk">
        <x-ui.field label="Email" for="preview_email">
            <x-ui.input name="preview_email" type="email" icon="envelope" placeholder="nama@domain.id" disabled />
        </x-ui.field>
        <x-ui.field label="Kata sandi" for="preview_password">
            <x-ui.input name="preview_password" type="password" icon="lock-closed" placeholder="••••••••" disabled />
        </x-ui.field>
        <div class="flex items-center justify-between gap-3">
            <x-ui.checkbox name="preview_remember" label="Ingat saya" disabled />
            <span class="text-sm text-fg-subtle">Lupa kata sandi?</span>
        </div>
        <x-ui.button block disabled>Masuk</x-ui.button>
    </div>
</x-guest-layout>
