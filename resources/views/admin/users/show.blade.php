@php
    use App\Services\Admin\AdminClock;
    use App\Support\Admin\AccessControl;

    $isStudent = $user->hasRole('student');
    $isParent = $user->hasRole('parent');
    $isTeacher = $user->hasRole('teacher');

    $tabs = ['ringkasan' => ['label' => 'Ringkasan', 'icon' => 'user']];
    if ($isStudent || $isTeacher) {
        $tabs['belajar'] = ['label' => $isTeacher && ! $isStudent ? 'Kelas diajar' : 'Pembelajaran', 'icon' => 'academic-cap',
            'count' => $isTeacher && ! $isStudent ? $taughtClasses->count() : $enrollments->count()];
    }
    if ($isStudent || $isParent) {
        $tabs['keluarga'] = ['label' => 'Keluarga', 'icon' => 'home', 'count' => $isParent ? $user->children->count() : $user->parents->count()];
        $tabs['pembayaran'] = ['label' => 'Pembayaran', 'icon' => 'banknotes'];
    }
    $tabs['privasi'] = ['label' => 'Privasi', 'icon' => 'finger-print'];
    $activeTab = isset($tabs['keluarga']) && $errors->hasAny(['related_id', 'guardian_verified', 'relationship']) ? 'keluarga' : 'ringkasan';
@endphp

<x-app-layout :title="$user->name">
    <x-ui.page-header :breadcrumbs="['Admin' => route('admin.dashboard'), 'Pengguna' => route('admin.users.index'), $user->name => null]">
        <x-slot:title>
            <span class="inline-flex items-center gap-4">
                <x-ui.avatar :user="$user" size="lg" decorative />
                <span>{{ $user->name }}</span>
            </span>
        </x-slot:title>
        <x-slot:badge>
            @if ($user->trashed())
                <x-ui.badge tone="neutral" dot>Diarsipkan</x-ui.badge>
            @elseif ($user->is_active)
                <x-ui.badge tone="success" dot>Aktif</x-ui.badge>
            @else
                <x-ui.badge tone="warning" dot>Nonaktif</x-ui.badge>
            @endif
        </x-slot:badge>
        <x-slot:meta>
            <span>{{ $user->email }}</span>
            <span>Bergabung {{ AdminClock::format($user->created_at) }}</span>
            @foreach ($user->roles as $role)
                <x-ui.badge outline :tone="AccessControl::roleTone($role->name)">{{ AccessControl::roleLabel($role->name) }}</x-ui.badge>
            @endforeach
            @if ($user->profile?->isChild())
                <x-ui.badge tone="info" icon="shield-check">Anak · {{ $user->profile->age }} th</x-ui.badge>
            @endif
        </x-slot:meta>
        <x-slot:actions>
            @if ($user->trashed())
                @can('restore', $user)
                    <form method="POST" action="{{ route('admin.users.restore', $user) }}">
                        @csrf
                        <x-ui.button type="submit" icon="arrow-uturn-left">Pulihkan akun</x-ui.button>
                    </form>
                @endcan
            @else
                @can('update', $user)
                    <x-ui.button variant="secondary" icon="pencil-square" :href="route('admin.users.edit', $user)">Ubah</x-ui.button>
                @endcan
                <x-ui.dropdown label="Tindakan" icon="ellipsis-horizontal" align="right" width="64">
                    @can('sendPasswordLink', $user)
                        <x-ui.dropdown-item icon="key" method="post" :href="route('admin.users.password-link', $user)">Kirim tautan atur ulang sandi</x-ui.dropdown-item>
                    @endcan
                    @can('toggleStatus', $user)
                        <x-ui.dropdown-item :icon="$user->is_active ? 'no-symbol' : 'check-circle'" method="post" :href="route('admin.users.toggle-status', $user)">
                            {{ $user->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}
                        </x-ui.dropdown-item>
                    @endcan
                    @can('create', App\Models\Enrollment::class)
                        @if ($isStudent)
                            <x-ui.dropdown-item icon="clipboard-document-check" :href="route('admin.enrollments.create', ['student' => $user->id])">Daftarkan ke kelas</x-ui.dropdown-item>
                        @endif
                    @endcan
                    @can('delete', $user)
                        <div class="dropdown-divider"></div>
                        <x-ui.dropdown-item icon="archive-box" danger x-on:click="$dispatch('open-modal', 'arsip-user')">Arsipkan akun</x-ui.dropdown-item>
                    @endcan
                </x-ui.dropdown>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if ($user->trashed())
        <x-ui.alert tone="warning" icon="archive-box" class="mt-6" title="Akun ini diarsipkan">
            Diarsipkan {{ AdminClock::format($user->deleted_at, 'j F Y') }}. Pemilik akun tidak dapat masuk. Data keuangan tetap disimpan sesuai kewajiban retensi.
        </x-ui.alert>
    @endif

    <x-ui.tabs class="mt-8" group="pengguna" :active="$activeTab" :tabs="$tabs" label="Bagian detail pengguna">
        <x-ui.tab-panel name="ringkasan">
            @include('admin.users.partials.show-summary')
        </x-ui.tab-panel>

        @isset($tabs['belajar'])
            <x-ui.tab-panel name="belajar">
                @include('admin.users.partials.show-learning')
            </x-ui.tab-panel>
        @endisset

        @isset($tabs['keluarga'])
            <x-ui.tab-panel name="keluarga">
                @include('admin.users.partials.show-family')
            </x-ui.tab-panel>
            <x-ui.tab-panel name="pembayaran">
                @include('admin.users.partials.show-payments')
            </x-ui.tab-panel>
        @endisset

        <x-ui.tab-panel name="privasi">
            @include('admin.users.partials.show-privacy')
        </x-ui.tab-panel>
    </x-ui.tabs>

    @can('delete', $user)
        @unless ($user->trashed())
            <x-ui.confirm-delete :action="route('admin.users.destroy', $user)" name="arsip-user" title="Arsipkan akun ini?"
                label="Arsipkan" confirm-text="Ya, arsipkan" icon="archive-box" confirm-phrase="ARSIPKAN"
                :message="'Akun '.$user->name.' dinonaktifkan, sesinya diakhiri dan disembunyikan dari daftar. Riwayat pembayaran, laporan dan sertifikat tetap tersimpan; akun dapat dipulihkan.'">
                <x-slot:trigger><span class="hidden"></span></x-slot:trigger>
            </x-ui.confirm-delete>
        @endunless
    @endcan
</x-app-layout>
