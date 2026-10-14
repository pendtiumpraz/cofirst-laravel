<?php

namespace App\Support\Navigation;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Navigation for the dashboard shell, built per active role.
 *
 * Every layout variant (sidebar, top-nav, bottom tabs, drawer, "Lainnya" sheet,
 * command palette) renders from the arrays returned here; no layout hard-codes
 * a menu. Items are resolved to {label, route, url, icon, active (patterns),
 * is_active, badge, children}.
 */
final class Navigation
{
    /** Dashboard role order; also the fallback priority when no role is chosen. */
    public const ROLES = ['superadmin', 'admin', 'teacher', 'parent', 'student', 'finance'];

    public const ROLE_LABELS = [
        'superadmin' => 'Superadmin',
        'admin' => 'Admin',
        'teacher' => 'Guru',
        'parent' => 'Orang Tua',
        'student' => 'Siswa',
        'finance' => 'Keuangan',
    ];

    public const DASHBOARDS = [
        'superadmin' => 'admin.dashboard',
        'admin' => 'admin.dashboard',
        'teacher' => 'teacher.dashboard',
        'parent' => 'parent.dashboard',
        'student' => 'student.dashboard',
        'finance' => 'finance.dashboard',
    ];

    /**
     * Grouped sections for the user's active role.
     *
     * Args:
     *     user (User): The signed-in user.
     *     activeRole (string|null): Role to build for; null uses session('active_role') or the first held role.
     *
     * Returns:
     *     array<int, array{key:string, label:?string, items:array<int, array<string, mixed>>}>:
     *         Sections with only the items whose routes exist and are allowed for the user.
     */
    public static function for(User $user, ?string $activeRole = null): array
    {
        $role = self::activeRole($user, $activeRole);

        if ($role === null) {
            return [];
        }

        $sections = [];

        foreach (Menus::forRole($role) as $section) {
            $items = self::resolveItems($user, $section['items']);

            if ($items !== []) {
                $sections[] = ['key' => $section['key'], 'label' => $section['label'], 'items' => $items];
            }
        }

        return $sections;
    }

    /**
     * Personal items (Profil, Preferensi, Privasi & Data Saya) available to the user.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function accountItems(User $user): array
    {
        return self::resolveItems($user, Menus::account());
    }

    /**
     * The four primary destinations for the mobile bottom tab bar (the fifth slot is "Lainnya").
     *
     * @return array<int, array<string, mixed>>
     */
    public static function mobileTabs(User $user, ?string $activeRole = null): array
    {
        $role = self::activeRole($user, $activeRole);
        $menu = collect(self::flatten(self::for($user, $role)))->keyBy('route');
        $tabs = [];

        foreach (Menus::tabs((string) $role) as [$label, $candidates, $icon]) {
            foreach ($candidates as $route) {
                if (isset($menu[$route]) && ! isset($tabs[$route])) {
                    $tabs[$route] = array_merge($menu[$route], ['label' => $label, 'icon' => $icon]);
                    break;
                }
            }
        }

        // Reason: when a preferred destination is missing for this user, fill the bar from the menu order.
        foreach ($menu as $route => $item) {
            if (count($tabs) >= 4) {
                break;
            }
            $tabs[$route] ??= $item;
        }

        return array_values(array_slice($tabs, 0, 4));
    }

    /**
     * The role whose menu and dashboard are shown.
     */
    public static function activeRole(User $user, ?string $requested = null): ?string
    {
        $roles = self::rolesOf($user);

        try {
            $requested ??= session('active_role');
        } catch (Throwable) {
            // No session (console, tests without a request): fall back to the first role.
        }

        return in_array($requested, $roles, true) ? $requested : ($roles[0] ?? null);
    }

    /**
     * Dashboard roles the user holds, in ROLES order.
     *
     * @return array<int, string>
     */
    public static function rolesOf(User $user): array
    {
        $held = $user->getRoleNames()->all();

        return array_values(array_filter(self::ROLES, fn ($role) => in_array($role, $held, true)));
    }

    /**
     * Route name of the dashboard for a role (defaults to the generic redirect).
     */
    public static function dashboardRoute(?string $role): string
    {
        return self::DASHBOARDS[$role] ?? 'dashboard';
    }

    /**
     * All items of the sections in one list (children included), each tagged with its section label.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function flatten(array $sections): array
    {
        $flat = [];

        foreach ($sections as $section) {
            foreach ($section['items'] as $item) {
                $flat[] = $item + ['section' => $section['label']];
                foreach ($item['children'] ?? [] as $child) {
                    $flat[] = $child + ['section' => $section['label']];
                }
            }
        }

        return $flat;
    }

    /**
     * The first active item of the sections (children win over their parent), or null.
     *
     * @return array<string, mixed>|null
     */
    public static function current(array $sections): ?array
    {
        $active = array_filter(self::flatten($sections), fn ($item) => $item['is_active']);

        return $active === [] ? null : end($active);
    }

    /**
     * Whether the current request matches one of the item's routeIs() patterns.
     */
    public static function isActive(array $item): bool
    {
        try {
            return request()->routeIs(...(array) ($item['active'] ?? [$item['route']]));
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Unread chat messages for the badge (cached briefly; 0 on any error).
     */
    public static function unreadMessages(User $user): int
    {
        try {
            return (int) Cache::remember('nav.unread.'.$user->getKey(), 30, fn () => (int) $user->conversationParticipants()->sum('unread_count'));
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Keep items whose route exists and is allowed; resolve url, active state and badge.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function resolveItems(User $user, array $items): array
    {
        $resolved = [];

        foreach ($items as $item) {
            if (! empty($item['roles']) && ! $user->hasAnyRole($item['roles'])) {
                continue;
            }

            $children = self::resolveItems($user, $item['children'] ?? []);

            if (! RouteAccess::allows($user, $item['route'])) {
                continue;
            }

            $item['url'] = route($item['route']);
            $item['children'] = $children;
            $item['is_active'] = self::isActive($item) || collect($children)->contains('is_active', true);
            $item['badge'] = match ($item['badge'] ?? null) {
                'chat' => self::unreadMessages($user) ?: null,
                default => is_int($item['badge'] ?? null) ? $item['badge'] : null,
            };
            $resolved[] = $item;
        }

        return $resolved;
    }
}
