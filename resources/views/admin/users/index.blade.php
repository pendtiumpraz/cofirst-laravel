@php
    use App\Services\Admin\AdminClock;
    use App\Support\Admin\AccessControl;
@endphp

<x-app-layout title="Pengguna">
    <x-ui.page-header title="Pengguna" subtitle="Akun siswa, orang tua, guru dan staf beserta perannya."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Pengguna' => null]">
        <x-slot:actions>
            @can('create', App\Models\User::class)
                <x-ui.button icon="user-plus" :href="route('admin.users.create')">Tambah pengguna</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.metric-strip class="mt-6" label="Ringkasan pengguna" :stats="[
        ['label' => 'Total akun', 'value' => number_format($stats['total'], 0, ',', '.'), 'icon' => 'users', 'hint' => $stats['active'].' aktif'],
        ['label' => 'Siswa', 'value' => number_format($stats['students'], 0, ',', '.'), 'icon' => 'academic-cap', 'href' => route('admin.users.index', ['role' => 'student'])],
        ['label' => 'Orang tua', 'value' => number_format($stats['parents'], 0, ',', '.'), 'icon' => 'home', 'href' => route('admin.users.index', ['role' => 'parent'])],
        ['label' => 'Guru', 'value' => number_format($stats['teachers'], 0, ',', '.'), 'icon' => 'presentation-chart-bar', 'href' => route('admin.users.index', ['role' => 'teacher'])],
    ]" />

    <x-ui.table caption="Daftar pengguna" mobile="stack" class="mt-6">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari nama atau email…" class="w-full">
                <x-ui.select name="role" :options="$roleOptions" placeholder="Semua peran" :value="request('role')" class="sm:w-44" aria-label="Peran" />
                <x-ui.select name="status" placeholder="Semua status" :value="request('status')" class="sm:w-44" aria-label="Status"
                    :options="['active' => 'Aktif', 'inactive' => 'Nonaktif', 'archived' => 'Diarsipkan ('.$stats['archived'].')']" />
            </x-ui.filter-bar>
        </x-slot:toolbar>

        <x-slot:head>
            <x-ui.th sort="name">Pengguna</x-ui.th>
            <x-ui.th>Peran</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th sort="created_at" numeric default-direction="desc">Bergabung</x-ui.th>
            <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($users as $user)
            @php $isChild = $user->profile?->isChild(); @endphp
            <tr>
                <td class="cell-primary" data-label="Pengguna">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :user="$user" size="sm" decorative />
                        <div class="min-w-0">
                            <a href="{{ route('admin.users.show', $user) }}" class="block truncate font-medium text-fg hover:text-brand-text">{{ $user->name }}</a>
                            <p class="cell-secondary truncate">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td data-label="Peran">
                    <div class="flex flex-wrap gap-1">
                        @forelse ($user->roles as $role)
                            <x-ui.badge outline :tone="AccessControl::roleTone($role->name)">{{ AccessControl::roleLabel($role->name) }}</x-ui.badge>
                        @empty
                            <span class="text-sm text-fg-subtle">Tanpa peran</span>
                        @endforelse
                        @if ($isChild)
                            <x-ui.badge tone="info" icon="shield-check">Anak</x-ui.badge>
                        @endif
                    </div>
                </td>
                <td data-label="Status">
                    @if ($user->trashed())
                        <x-ui.badge tone="neutral" dot>Diarsipkan</x-ui.badge>
                    @elseif ($user->is_active)
                        <x-ui.badge tone="success" dot>Aktif</x-ui.badge>
                    @else
                        <x-ui.badge tone="warning" dot>Nonaktif</x-ui.badge>
                    @endif
                </td>
                <td class="num" data-label="Bergabung">{{ AdminClock::format($user->created_at) }}</td>
                <td class="cell-actions">
                    <div class="inline-flex items-center gap-1">
                        @if ($user->trashed())
                            @can('restore', $user)
                                <form method="POST" action="{{ route('admin.users.restore', $user) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" size="sm" icon="arrow-uturn-left">Pulihkan</x-ui.button>
                                </form>
                            @endcan
                        @else
                            @can('update', $user)
                                <x-ui.button variant="ghost" size="sm" icon="pencil-square" :href="route('admin.users.edit', $user)" :label="'Ubah '.$user->name" />
                            @endcan
                            <x-ui.dropdown align="right" width="56">
                                <x-slot:trigger>
                                    <button type="button" class="btn btn-ghost btn-icon btn-sm" aria-label="Menu {{ $user->name }}" aria-haspopup="true" x-bind:aria-expanded="open.toString()">
                                        <x-ui.icon name="ellipsis-horizontal" />
                                    </button>
                                </x-slot:trigger>
                                <x-ui.dropdown-item icon="eye" :href="route('admin.users.show', $user)">Lihat detail</x-ui.dropdown-item>
                                @can('toggleStatus', $user)
                                    <x-ui.dropdown-item :icon="$user->is_active ? 'no-symbol' : 'check-circle'" method="post" :href="route('admin.users.toggle-status', $user)">
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </x-ui.dropdown-item>
                                @endcan
                                @can('delete', $user)
                                    <div class="dropdown-divider"></div>
                                    <x-ui.dropdown-item icon="archive-box" danger x-on:click="$dispatch('open-modal', 'arsip-user-{{ $user->id }}')">Arsipkan</x-ui.dropdown-item>
                                @endcan
                            </x-ui.dropdown>
                            @can('delete', $user)
                                <x-ui.confirm-delete :action="route('admin.users.destroy', $user)" :name="'arsip-user-'.$user->id"
                                    title="Arsipkan akun ini?" label="Arsipkan" confirm-text="Ya, arsipkan" icon="archive-box"
                                    :message="'Akun '.$user->name.' dinonaktifkan dan disembunyikan. Riwayat pembayaran, laporan dan sertifikat tetap tersimpan, dan akun dapat dipulihkan.'">
                                    <x-slot:trigger><span class="hidden"></span></x-slot:trigger>
                                </x-ui.confirm-delete>
                            @endcan
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            @if (request()->hasAny(['search', 'role', 'status']))
                <x-ui.empty-state icon="magnifying-glass" title="Tidak ada pengguna yang cocok." description="Coba kata kunci lain atau hapus filter.">
                    <x-slot:actions><x-ui.button variant="secondary" size="sm" :href="route('admin.users.index')">Hapus filter</x-ui.button></x-slot:actions>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state icon="users" title="Belum ada pengguna." description="Tambahkan akun pertama untuk siswa, orang tua atau pengajar." />
            @endif
        </x-slot:empty>

        <x-slot:footer>{{ $users->links() }}</x-slot:footer>
    </x-ui.table>
</x-app-layout>
