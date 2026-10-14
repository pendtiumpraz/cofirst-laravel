<?php

namespace App\Support\Settings\Http;

use App\Support\Settings\SettingsRegistry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Sudo mode" for sensitive settings groups (config: confirm_password => true,
 * e.g. Keamanan and Privasi & PDP): editors must have confirmed their password
 * recently (auth.password_timeout) before they open or change the group.
 * Viewers who may not edit the group see it read-only without confirming.
 */
class ConfirmPasswordForSensitiveGroups
{
    public function __construct(private SettingsRegistry $registry) {}

    /**
     * Handle an incoming request.
     *
     * Args:
     *     request (Request): The request (route parameter {group}).
     *     next (Closure): Next middleware.
     *
     * Returns:
     *     Response: The response, or a redirect to the password confirmation page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $group = (string) $request->route('group');

        if (! $this->registry->hasGroup($group)
            || ! $this->registry->needsPasswordConfirmation($group)
            || ! $this->registry->canEdit($request->user(), $group)
            || ! Route::has('password.confirm')
            || $this->recentlyConfirmed($request)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(423, 'Konfirmasi kata sandi diperlukan.');
        }

        // Reason: always come back to the GET page — redirecting "intended" to a PUT URL would 405.
        $request->session()->put('url.intended', route('admin.settings.edit', $group));

        return redirect()->route('password.confirm')->with(
            'status',
            'Demi keamanan, konfirmasi kata sandi Anda sebelum mengubah pengaturan '.($this->registry->group($group)['label'] ?? $group).'.'
        );
    }

    private function recentlyConfirmed(Request $request): bool
    {
        $confirmedAt = (int) $request->session()->get('auth.password_confirmed_at', 0);

        return (time() - $confirmedAt) < (int) config('auth.password_timeout', 10800);
    }
}
