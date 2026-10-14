@php
    use App\Services\Admin\AdminClock;

    $profile = $user->profile;
    $rows = [
        'Nama lengkap' => $profile?->full_name ?: $user->name,
        'Email' => $user->email,
        'Telepon' => $profile?->phone_number,
        'Tanggal lahir' => $profile?->birth_date ? AdminClock::format($profile->birth_date, 'j F Y').' ('.$profile->age.' th)' : null,
        'Jenis kelamin' => $profile?->gender_label,
        'Alamat' => $profile?->address,
    ];
@endphp

<div class="grid grid-cols-1 gap-gutter lg:grid-cols-3">
    <x-ui.card title="Data pribadi" subtitle="Hanya untuk keperluan layanan belajar." class="lg:col-span-2">
        <dl class="divide-y divide-line">
            @foreach ($rows as $label => $value)
                <div class="grid gap-1 py-3 first:pt-0 last:pb-0 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm text-fg-muted">{{ $label }}</dt>
                    <dd class="text-sm text-fg sm:col-span-2">
                        @if (filled($value))
                            {{ $value }}
                        @else
                            <span class="text-fg-subtle">Belum diisi</span>
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
        @if (filled($profile?->bio))
            <div class="mt-5 border-t border-line pt-5">
                <p class="eyebrow mb-2">Bio</p>
                <p class="max-w-measure whitespace-pre-line text-sm text-fg-muted">{{ $profile->bio }}</p>
            </div>
        @endif
        @can('update', $user)
            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" icon="pencil-square" :href="route('admin.users.edit', $user)">Ubah data</x-ui.button>
            </x-slot:footer>
        @endcan
    </x-ui.card>

    <x-ui.card title="Akun">
        <dl class="space-y-4 text-sm">
            <div>
                <dt class="text-fg-muted">Status</dt>
                <dd class="mt-1 text-fg">{{ $user->trashed() ? 'Diarsipkan' : ($user->is_active ? 'Aktif — dapat masuk' : 'Nonaktif — tidak dapat masuk') }}</dd>
            </div>
            <div>
                <dt class="text-fg-muted">Email terverifikasi</dt>
                <dd class="mt-1 text-fg">{{ $user->email_verified_at ? AdminClock::format($user->email_verified_at, 'j M Y') : 'Belum' }}</dd>
            </div>
            <div>
                <dt class="text-fg-muted">Terdaftar</dt>
                <dd class="mt-1 text-fg">{{ AdminClock::format($user->created_at, 'j F Y') }}</dd>
            </div>
            <div>
                <dt class="text-fg-muted">Terakhir diperbarui</dt>
                <dd class="mt-1 text-fg">{{ AdminClock::format($user->updated_at, 'j F Y, H.i') }}</dd>
            </div>
        </dl>
    </x-ui.card>
</div>
