@php
    use App\Support\Admin\AccessControl;

    $label = AccessControl::roleLabel($role->name);
    $system = AccessControl::isSystemRole($role->name);
@endphp

<x-app-layout :title="'Peran '.$label">
    <x-ui.page-header :title="$label" :subtitle="AccessControl::ROLE_DESCRIPTIONS[$role->name] ?? 'Peran kustom.'"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Peran' => route('admin.roles.index'), $label => null]">
        <x-slot:badge>
            <x-ui.badge :tone="$system ? 'neutral' : 'accent'" :icon="$system ? 'lock-closed' : null">{{ $system ? 'Bawaan' : 'Kustom' }}</x-ui.badge>
        </x-slot:badge>
        <x-slot:meta><span>{{ $role->name }}</span><span>{{ $userCount }} pengguna</span></x-slot:meta>
        <x-slot:actions>
            <x-ui.button icon="pencil-square" :href="route('admin.roles.edit', $role)">Ubah izin</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mt-8 grid grid-cols-1 gap-gutter lg:grid-cols-3">
        <div class="space-y-gutter lg:col-span-2">
            @if ($role->name === 'superadmin')
                <x-ui.alert tone="info" icon="shield-check">Superadmin memiliki semua izin.</x-ui.alert>
            @endif
            @forelse ($groups as $group)
                <x-ui.card :title="$group['label']">
                    <ul class="grid gap-2 sm:grid-cols-2" role="list">
                        @foreach ($group['permissions'] as $permission)
                            <li class="flex items-start gap-2 text-sm">
                                <x-ui.icon name="check-circle" size="16" class="mt-0.5 text-success" />
                                <span>
                                    <span class="text-fg">{{ $permission['label'] }}</span>
                                    <span class="block font-mono text-xs text-fg-subtle">{{ $permission['name'] }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @empty
                <x-ui.card>
                    <x-ui.empty-state icon="lock-closed" title="Peran ini belum memiliki izin." description="Pengguna dengan peran ini hanya mendapat akses dari peran lain yang dimilikinya.">
                        <x-slot:actions><x-ui.button size="sm" :href="route('admin.roles.edit', $role)">Pilih izin</x-ui.button></x-slot:actions>
                    </x-ui.empty-state>
                </x-ui.card>
            @endforelse
        </div>

        <div class="space-y-gutter">
            <x-ui.card title="Pengguna" :subtitle="$userCount.' akun'" padding="none">
                <x-slot:actions>
                    <x-ui.button variant="link" size="sm" arrow :href="route('admin.users.index', ['role' => $role->name])">Lihat semua</x-ui.button>
                </x-slot:actions>
                @if ($users->isEmpty())
                    <x-ui.empty-state icon="users" title="Belum ada pengguna dengan peran ini." />
                @else
                    <ul class="divide-y divide-line" role="list">
                        @foreach ($users as $member)
                            <li class="flex items-center gap-3 px-card py-2.5">
                                <x-ui.avatar :user="$member" size="xs" decorative />
                                <a href="{{ route('admin.users.show', $member) }}" class="min-w-0 flex-1 truncate text-sm text-fg hover:text-brand-text">{{ $member->name }}</a>
                                @unless ($member->is_active)<x-ui.badge tone="neutral">Nonaktif</x-ui.badge>@endunless
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            @unless ($system)
                <x-ui.card variant="callout" title="Hapus peran" style="--callout-c: var(--cf-danger)"
                    subtitle="Hanya peran tanpa pengguna yang dapat dihapus.">
                    <x-ui.confirm-delete :action="route('admin.roles.destroy', $role)" variant="danger" label="Hapus peran"
                        title="Hapus peran ini?" :message="'Peran '.$role->name.' dan semua izinnya dihapus.'" :confirm-phrase="$role->name" />
                </x-ui.card>
            @endunless
        </div>
    </div>
</x-app-layout>
