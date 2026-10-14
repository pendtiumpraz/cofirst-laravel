<?php

namespace App\Support\Navigation;

use App\Models\User;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * Answers "may this user open this named route?" from the route's own
 * spatie middleware (role:, permission:, role_or_permission:), so menus never
 * show a link that would answer 403 and never drift from routes/web/*.php.
 */
final class RouteAccess
{
    /**
     * Whether the named GET route exists and the user passes its role/permission middleware.
     *
     * Args:
     *     user (User): The user to check.
     *     name (string): Route name, e.g. "admin.users.index".
     *
     * Returns:
     *     bool: True when the route exists and is allowed for the user.
     */
    public static function allows(User $user, string $name): bool
    {
        $route = Route::getRoutes()->getByName($name);

        if (! $route instanceof RoutingRoute || ! in_array('GET', $route->methods(), true)) {
            return false;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && ! self::passes($user, $middleware)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether one middleware definition lets the user through.
     */
    private static function passes(User $user, string $middleware): bool
    {
        [$alias, $arguments] = array_pad(explode(':', $middleware, 2), 2, '');
        $alias = class_basename($alias);
        // Reason: spatie accepts "role:a|b,guard"; the guard part is irrelevant here.
        $values = array_filter(explode('|', explode(',', $arguments)[0]));

        try {
            return match ($alias) {
                'role', 'RoleMiddleware' => $user->hasAnyRole($values),
                'permission', 'PermissionMiddleware' => $user->hasAnyPermission($values),
                'role_or_permission', 'RoleOrPermissionMiddleware' => $user->hasAnyRole($values) || $user->hasAnyPermission($values),
                default => true,
            };
        } catch (Throwable) {
            return false;
        }
    }
}
