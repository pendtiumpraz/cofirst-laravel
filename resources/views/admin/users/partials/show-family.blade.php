@php
    $isParent = $user->hasRole('parent');
    $links = $isParent ? $user->children : $user->parents;
    $pickRole = $isParent ? 'student' : 'parent';
@endphp

<div class="grid grid-cols-1 gap-gutter lg:grid-cols-3">
    <x-ui.card :title="$isParent ? 'Anak yang tertaut' : 'Orang tua/wali yang tertaut'" padding="none" class="lg:col-span-2">
        @if ($links->isEmpty())
            <x-ui.empty-state icon="home"
                :title="$isParent ? 'Belum ada anak yang tertaut.' : 'Belum ada orang tua/wali yang tertaut.'"
                :description="$isParent ? 'Tautkan akun siswa agar orang tua dapat memantau jadwal, laporan dan tagihan.' : 'Siswa di bawah umur sebaiknya memiliki orang tua/wali tertaut (UU PDP Pasal 25).'" />
        @else
            <ul class="divide-y divide-line" role="list">
                @foreach ($links as $linked)
                    <li class="flex items-center gap-3 px-card py-3">
                        <x-ui.avatar :user="$linked" size="sm" decorative />
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('admin.users.show', $linked) }}" class="block truncate font-medium text-fg hover:text-brand-text">{{ $linked->name }}</a>
                            <p class="truncate text-xs text-fg-subtle">Tertaut sejak {{ App\Services\Admin\AdminClock::format($linked->pivot?->created_at) }}</p>
                        </div>
                        @can('update', $user)
                            <x-ui.confirm-delete :action="route('admin.users.family.detach', [$user, $linked])" icon-only size="sm" icon="link-slash"
                                label="Lepas tautan" title="Lepas tautan keluarga?" confirm-text="Ya, lepas"
                                :message="$isParent ? $user->name.' tidak lagi dapat melihat data '.$linked->name.'.' : $linked->name.' tidak lagi dapat melihat data '.$user->name.'.'" />
                        @endcan
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    @can('update', $user)
        <x-ui.card :title="$isParent ? 'Tautkan anak' : 'Tautkan orang tua/wali'">
            <form method="POST" action="{{ route('admin.users.family.attach', $user) }}" class="space-y-4">
                @csrf
                <x-ui.field :label="$isParent ? 'Akun siswa' : 'Akun orang tua'" for="family-picker" :error="'related_id'" required>
                    @include('admin.users.partials.user-picker', ['name' => 'related_id', 'id' => 'family-picker', 'role' => $pickRole,
                        'multiple' => false, 'selected' => [], 'placeholder' => $isParent ? 'Cari nama siswa…' : 'Cari nama orang tua…'])
                </x-ui.field>
                <x-ui.field label="Hubungan" for="relationship" optional>
                    <x-ui.select name="relationship" placeholder="Tidak disebutkan" :options="['ayah' => 'Ayah', 'ibu' => 'Ibu', 'wali' => 'Wali']" />
                </x-ui.field>
                <x-ui.checkbox name="guardian_verified" value="1" label="Hubungan sudah saya verifikasi"
                    description="Mis. kartu keluarga, akta kelahiran atau konfirmasi langsung. Tercatat di log audit." />
                @error('guardian_verified')<p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>@enderror
                <x-ui.button type="submit" icon="link" block>Tautkan</x-ui.button>
            </form>
        </x-ui.card>
    @endcan
</div>
