@php use App\Support\Admin\AccessControl; @endphp

<x-app-layout title="Peran">
    <x-ui.page-header title="Peran" subtitle="Peran menentukan dasbor dan menu; izin menentukan tindakan yang boleh dilakukan."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Peran' => null]">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="lock-closed" :href="route('admin.permissions.index')">Izin akses</x-ui.button>
            <x-ui.button icon="plus" :href="route('admin.roles.create')">Peran baru</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.alert tone="info" icon="information-circle" class="mt-6">
        Enam peran bawaan dipakai oleh menu dan pembatasan akses aplikasi, sehingga namanya tidak dapat diubah atau dihapus.
        Superadmin selalu memiliki semua izin.
    </x-ui.alert>

    <ul class="mt-6 grid grid-cols-1 gap-gutter sm:grid-cols-2 xl:grid-cols-3" role="list">
        @foreach ($roles as $role)
            @php $system = AccessControl::isSystemRole($role->name); @endphp
            <li>
                <x-ui.card :href="route('admin.roles.show', $role)" :title="AccessControl::roleLabel($role->name)" class="h-full">
                    <x-slot:actions>
                        @if ($system)
                            <x-ui.badge tone="neutral" icon="lock-closed">Bawaan</x-ui.badge>
                        @else
                            <x-ui.badge tone="accent">Kustom</x-ui.badge>
                        @endif
                    </x-slot:actions>
                    <p class="font-mono text-xs text-fg-subtle">{{ $role->name }}</p>
                    <p class="mt-2 text-sm text-fg-muted">{{ AccessControl::ROLE_DESCRIPTIONS[$role->name] ?? 'Peran kustom.' }}</p>
                    <dl class="mt-4 flex gap-6 text-sm">
                        <div>
                            <dt class="text-fg-subtle">Pengguna</dt>
                            <dd class="num font-semibold text-fg">{{ number_format($role->users_count, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-fg-subtle">Izin</dt>
                            <dd class="num font-semibold text-fg">{{ $role->name === 'superadmin' ? 'Semua' : $role->permissions_count.'/'.$permissionTotal }}</dd>
                        </div>
                    </dl>
                </x-ui.card>
            </li>
        @endforeach
    </ul>
</x-app-layout>
