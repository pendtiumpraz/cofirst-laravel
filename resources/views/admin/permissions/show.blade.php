@php use App\Support\Admin\AccessControl; @endphp

<x-app-layout :title="'Izin '.$permission->name">
    <x-ui.page-header :title="AccessControl::permissionLabel($permission->name)"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Izin akses' => route('admin.permissions.index'), $permission->name => null]">
        <x-slot:badge>
            <x-ui.badge :tone="$isSystem ? 'neutral' : 'accent'" :icon="$isSystem ? 'lock-closed' : null">{{ $isSystem ? 'Bawaan' : 'Kustom' }}</x-ui.badge>
        </x-slot:badge>
        <x-slot:meta><span>{{ $permission->name }}</span></x-slot:meta>
        <x-slot:actions>
            <x-ui.button icon="pencil-square" :href="route('admin.permissions.edit', $permission)">Ubah</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mt-8 grid grid-cols-1 gap-gutter lg:grid-cols-3">
        <x-ui.card title="Dimiliki peran" padding="none" class="lg:col-span-2">
            @if ($permission->roles->isEmpty())
                <x-ui.empty-state icon="key" title="Belum diberikan ke peran mana pun." />
            @else
                <ul class="divide-y divide-line" role="list">
                    @foreach ($permission->roles as $role)
                        <li class="flex items-center justify-between gap-3 px-card py-3">
                            <a href="{{ route('admin.roles.show', $role) }}" class="font-medium text-fg hover:text-brand-text">{{ AccessControl::roleLabel($role->name) }}</a>
                            <span class="font-mono text-xs text-fg-subtle">{{ $role->name }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        @unless ($isSystem)
            <x-ui.card variant="callout" title="Hapus izin" style="--callout-c: var(--cf-danger)" subtitle="Izin dicabut dari semua peran.">
                <x-ui.confirm-delete :action="route('admin.permissions.destroy', $permission)" variant="danger" label="Hapus izin"
                    title="Hapus izin ini?" :message="'Izin '.$permission->name.' dihapus dari semua peran.'" />
            </x-ui.card>
        @endunless
    </div>
</x-app-layout>
