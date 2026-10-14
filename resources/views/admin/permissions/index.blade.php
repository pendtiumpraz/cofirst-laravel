@php use App\Support\Admin\AccessControl; @endphp

<x-app-layout title="Izin akses">
    <x-ui.page-header title="Izin akses" subtitle="Daftar tindakan yang dapat diberikan ke peran. Izin bawaan dipakai aplikasi dan tidak dapat dihapus."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Peran' => route('admin.roles.index'), 'Izin akses' => null]">
        <x-slot:actions>
            <x-ui.button icon="plus" :href="route('admin.permissions.create')">Izin kustom</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="mt-6" padding="sm">
        <x-ui.filter-bar placeholder="Cari nama izin…" />
    </x-ui.card>

    <div class="mt-6 space-y-gutter">
        @forelse ($groups as $key => $group)
            <x-ui.table :caption="$group['label']" mobile="stack">
                <x-slot:toolbar>
                    <div class="flex w-full items-center justify-between gap-3">
                        <h2 class="card-title">{{ $group['label'] }}</h2>
                        <span class="count-pill">{{ count($group['permissions']) }}</span>
                    </div>
                </x-slot:toolbar>
                <x-slot:head>
                    <x-ui.th>Izin</x-ui.th>
                    <x-ui.th>Dimiliki peran</x-ui.th>
                    <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
                </x-slot:head>
                @foreach ($group['permissions'] as $item)
                    @php $permission = $item['model']; @endphp
                    <tr>
                        <td class="cell-primary" data-label="Izin">
                            <a href="{{ route('admin.permissions.show', $permission) }}" class="font-medium text-fg hover:text-brand-text">{{ $item['label'] }}</a>
                            @if ($item['label'] !== $item['name'])
                                <p class="cell-secondary font-mono">{{ $item['name'] }}</p>
                            @endif
                        </td>
                        <td data-label="Dimiliki peran">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($permission->roles as $role)
                                    <x-ui.badge outline :tone="AccessControl::roleTone($role->name)">{{ AccessControl::roleLabel($role->name) }}</x-ui.badge>
                                @empty
                                    <span class="text-sm text-fg-subtle">Belum diberikan</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="cell-actions">
                            <x-ui.button variant="ghost" size="sm" icon="pencil-square" :href="route('admin.permissions.edit', $permission)" :label="'Ubah '.$item['name']" />
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @empty
            <x-ui.empty-state size="page" icon="lock-closed" title="Tidak ada izin yang cocok." description="Coba kata kunci lain." />
        @endforelse
    </div>
</x-app-layout>
