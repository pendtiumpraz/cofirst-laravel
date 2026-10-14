<?php

namespace App\Support\Auth;

use App\Models\User;
use App\Support\Navigation\Navigation;
use Illuminate\Http\Request;

/**
 * Which dashboard role a signed-in user is acting as (session('active_role')).
 *
 * Shared by the login redirect, the role-selection page, the shell's "Ganti peran"
 * menu and the /dashboard entry point so they all agree.
 */
final class ActiveRole
{
    /** Short description and icon per role, shown on the role-selection cards. */
    private const DESCRIPTIONS = [
        'superadmin' => ['Kelola seluruh sistem, pengaturan, dan kepatuhan PDP.', 'shield-check'],
        'admin' => ['Kelola pengguna, kelas, jadwal, dan pendaftaran.', 'building-office-2'],
        'teacher' => ['Lihat kelas yang Anda ajar, catat absensi, dan tulis laporan.', 'academic-cap'],
        'parent' => ['Pantau jadwal, laporan belajar, dan tagihan anak Anda.', 'user-group'],
        'student' => ['Ikuti kelas, lihat materi, dan kumpulkan pencapaian.', 'rocket-launch'],
        'finance' => ['Kelola tagihan, pembayaran, dan laporan keuangan.', 'banknotes'],
    ];

    /**
     * Dashboard roles the user holds (in Navigation::ROLES order).
     *
     * @return array<int, string>
     */
    public static function rolesOf(User $user): array
    {
        return Navigation::rolesOf($user);
    }

    /**
     * Whether the user must pick a role: they hold roles that open different dashboards.
     *
     * Reason: superadmin and admin share one dashboard, so asking them to choose
     * would be a pointless extra step.
     */
    public static function needsSelection(User $user): bool
    {
        $dashboards = array_unique(array_map(
            fn (string $role) => Navigation::dashboardRoute($role),
            self::rolesOf($user)
        ));

        return count($dashboards) > 1;
    }

    /**
     * Make $role the active role for this session.
     *
     * The session id is regenerated so a privilege change never reuses an id that
     * may have been exposed before (session fixation).
     */
    public static function activate(Request $request, string $role): void
    {
        $request->session()->regenerate();
        $request->session()->put('active_role', $role);
    }

    /**
     * Route name of the dashboard for a role.
     */
    public static function dashboardRoute(string $role): string
    {
        return Navigation::dashboardRoute($role);
    }

    /**
     * Indonesian label of a role.
     */
    public static function label(string $role): string
    {
        return Navigation::ROLE_LABELS[$role] ?? ucfirst($role);
    }

    /**
     * Options for x-ui.radio-cards on the role-selection page.
     *
     * @return array<string, array{label:string, description:string, icon:string}>
     */
    public static function options(User $user): array
    {
        $options = [];

        foreach (self::rolesOf($user) as $role) {
            [$description, $icon] = self::DESCRIPTIONS[$role] ?? ['', 'user'];
            $options[$role] = ['label' => self::label($role), 'description' => $description, 'icon' => $icon];
        }

        return $options;
    }
}
