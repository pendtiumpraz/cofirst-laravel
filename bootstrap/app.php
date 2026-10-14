<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Sign out deactivated accounts on their next request (sessions and remember-me cookies).
        // Reason: registered before AwardLoginPoints so a deactivated user earns no login points.
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureUserIsActive::class);

        // Add login points middleware to web group
        $middleware->appendToGroup('web', \App\Http\Middleware\AwardLoginPoints::class);

        // Settings module: the signed-in user's language preference (users.preferences.locale).
        $middleware->appendToGroup('web', \App\Support\Settings\Http\ApplyUserLocale::class);

        // PDP consent module: re-acceptance of changed policies (+ cookie banner on dashboards).
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsurePolicyAccepted::class);
        // Reason: the cookie-consent cookie is written and read by the banner script, so it stays unencrypted.
        $middleware->encryptCookies(except: ['cf_consent']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
