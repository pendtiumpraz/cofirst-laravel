<?php

namespace App\Support\Admin;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Role and permission metadata for the admin back-office.
 *
 * System roles and permissions are referenced by name in route middleware
 * (role:admin|superadmin, …) and in the admin policies, so they must never be
 * renamed or deleted from the Roles & Permissions screens. Labels are Indonesian.
 */
final class AccessControl
{
    /** Roles hard-coded in routes, dashboards and navigation. */
    public const SYSTEM_ROLES = ['superadmin', 'admin', 'teacher', 'student', 'parent', 'finance'];

    /** Roles that only a superadmin may grant, revoke or manage. */
    public const PRIVILEGED_ROLES = ['superadmin', 'admin'];

    public const ROLE_LABELS = [
        'superadmin' => 'Superadmin',
        'admin' => 'Admin',
        'teacher' => 'Guru',
        'student' => 'Siswa',
        'parent' => 'Orang tua',
        'finance' => 'Keuangan',
    ];

    public const ROLE_DESCRIPTIONS = [
        'superadmin' => 'Akses penuh, termasuk peran, izin dan pengaturan sistem.',
        'admin' => 'Mengelola pengguna, kursus, kelas, pendaftaran dan jadwal.',
        'teacher' => 'Mengajar kelas, mencatat absensi dan menulis laporan.',
        'student' => 'Mengikuti kelas, materi, jadwal dan pencapaian.',
        'parent' => 'Memantau anak: jadwal, laporan, kehadiran dan tagihan.',
        'finance' => 'Mengelola tagihan, pembayaran dan laporan keuangan.',
    ];

    /** Badge tone per role for consistent role chips. */
    public const ROLE_TONES = [
        'superadmin' => 'accent',
        'admin' => 'primary',
        'teacher' => 'info',
        'student' => 'success',
        'parent' => 'warning',
        'finance' => 'neutral',
    ];

    /**
     * Permissions grouped by module, with Indonesian labels.
     *
     * Reason: these names are used by the admin policies and RoleSeeder; the
     * permission matrix renders this structure so superadmins see plain language.
     */
    public const PERMISSION_GROUPS = [
        'users' => ['label' => 'Pengguna', 'permissions' => [
            'view users' => 'Melihat daftar & detail pengguna',
            'create users' => 'Menambah pengguna',
            'edit users' => 'Mengubah pengguna & tautan keluarga',
            'delete users' => 'Mengarsipkan & memulihkan pengguna',
            'activate deactivate users' => 'Mengaktifkan/menonaktifkan akun',
        ]],
        'courses' => ['label' => 'Kursus', 'permissions' => [
            'view courses' => 'Melihat kursus',
            'create courses' => 'Menambah kursus',
            'edit courses' => 'Mengubah kursus',
            'delete courses' => 'Mengarsipkan kursus',
        ]],
        'classes' => ['label' => 'Kelas & pendaftaran', 'permissions' => [
            'view classes' => 'Melihat kelas & pendaftaran',
            'create classes' => 'Membuat kelas',
            'edit classes' => 'Mengubah kelas, daftar siswa & pendaftaran',
            'delete classes' => 'Mengarsipkan kelas & menghapus pendaftaran',
        ]],
        'schedules' => ['label' => 'Jadwal', 'permissions' => [
            'view schedules' => 'Melihat jadwal',
            'create schedules' => 'Membuat jadwal',
            'edit schedules' => 'Mengubah jadwal',
            'delete schedules' => 'Menghapus jadwal',
            'manage all schedules' => 'Mengelola semua jadwal',
            'view own schedule' => 'Melihat jadwal sendiri',
            'request schedule change' => 'Mengajukan perubahan jadwal',
        ]],
        'academic' => ['label' => 'Akademik & laporan', 'permissions' => [
            'view reports' => 'Melihat laporan',
            'create reports' => 'Membuat laporan',
            'edit reports' => 'Mengubah laporan',
            'delete reports' => 'Menghapus laporan',
            'post monthly reports' => 'Menerbitkan laporan bulanan',
            'create student reports' => 'Menulis laporan siswa',
            'manage attendance' => 'Mencatat absensi',
            'handover students' => 'Serah terima siswa',
            'view curriculum' => 'Melihat kurikulum',
            'manage curriculum' => 'Mengelola kurikulum',
            'view child reports' => 'Melihat laporan anak',
            'view child progress' => 'Melihat perkembangan anak',
        ]],
        'finance' => ['label' => 'Keuangan', 'permissions' => [
            'view finances' => 'Melihat keuangan',
            'create transactions' => 'Mencatat transaksi',
            'edit transactions' => 'Mengubah transaksi',
            'delete transactions' => 'Menghapus transaksi',
            'view financial reports' => 'Melihat laporan keuangan',
        ]],
        'system' => ['label' => 'Sistem & privasi (UU PDP)', 'permissions' => [
            'system administration' => 'Administrasi sistem',
            'manage settings' => 'Mengelola pengaturan',
            'manage privacy' => 'Mengelola kepatuhan privasi',
            'handle data requests' => 'Menangani permintaan subjek data',
            'view audit log' => 'Melihat log audit',
            'manage legal documents' => 'Mengelola dokumen kebijakan',
        ]],
    ];

    /**
     * Indonesian label of a role (falls back to the raw name for custom roles).
     */
    public static function roleLabel(string $role): string
    {
        return self::ROLE_LABELS[$role] ?? ucfirst(str_replace(['_', '-'], ' ', $role));
    }

    /**
     * Badge tone of a role.
     */
    public static function roleTone(string $role): string
    {
        return self::ROLE_TONES[$role] ?? 'neutral';
    }

    /**
     * Whether the role is a built-in system role (cannot be renamed or deleted).
     */
    public static function isSystemRole(string $role): bool
    {
        return in_array($role, self::SYSTEM_ROLES, true);
    }

    /**
     * All permission names the application relies on.
     *
     * @return array<int, string>
     */
    public static function systemPermissions(): array
    {
        return collect(self::PERMISSION_GROUPS)->flatMap(fn ($group) => array_keys($group['permissions']))->values()->all();
    }

    /**
     * Whether the permission is a built-in permission (cannot be renamed or deleted).
     */
    public static function isSystemPermission(string $permission): bool
    {
        return in_array($permission, self::systemPermissions(), true);
    }

    /**
     * Indonesian description of a permission (the raw name for custom permissions).
     */
    public static function permissionLabel(string $permission): string
    {
        foreach (self::PERMISSION_GROUPS as $group) {
            if (isset($group['permissions'][$permission])) {
                return $group['permissions'][$permission];
            }
        }

        return $permission;
    }

    /**
     * Group permission models/names by module for the permission matrix.
     *
     * Args:
     *     permissions (Collection): Permission models (with a `name`) or names.
     *
     * Returns:
     *     array<string, array{label: string, permissions: array<int, array{name: string, label: string, model: mixed}>}>
     */
    public static function group(Collection $permissions): array
    {
        $byName = $permissions->keyBy(fn ($p) => is_string($p) ? $p : $p->name);
        $groups = [];
        $seen = [];

        foreach (self::PERMISSION_GROUPS as $key => $group) {
            $items = [];
            foreach ($group['permissions'] as $name => $label) {
                if ($byName->has($name)) {
                    $items[] = ['name' => $name, 'label' => $label, 'model' => $byName->get($name)];
                    $seen[] = $name;
                }
            }
            if ($items !== []) {
                $groups[$key] = ['label' => $group['label'], 'permissions' => $items];
            }
        }

        $custom = $byName->except($seen)->map(fn ($p, $name) => ['name' => $name, 'label' => $name, 'model' => $p])->values()->all();
        if ($custom !== []) {
            $groups['custom'] = ['label' => 'Izin kustom', 'permissions' => $custom];
        }

        return $groups;
    }

    /**
     * Roles the actor is allowed to grant or revoke.
     *
     * Only a superadmin may grant superadmin/admin; admins can assign the
     * remaining roles (including custom roles).
     *
     * Args:
     *     actor (User): The signed-in staff member.
     *     available (array<int, string>): Role names that exist.
     *
     * Returns:
     *     array<int, string>
     */
    public static function assignableRoles(User $actor, array $available): array
    {
        if ($actor->hasRole('superadmin')) {
            return array_values($available);
        }

        return array_values(array_diff($available, self::PRIVILEGED_ROLES));
    }
}
