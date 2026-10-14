<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out a user whose account has been deactivated (users.is_active = false).
 *
 * Runs on every web request, so it covers existing sessions and "remember me"
 * cookies (the recaller logs the user in silently, without passing LoginRequest).
 */
class EnsureUserIsActive
{
    public const MESSAGE = 'Akun Anda sedang tidak aktif. Hubungi admin Coding First untuk mengaktifkannya kembali.';

    /**
     * Handle an incoming request.
     *
     * Args:
     *     request (Request): Incoming request.
     *     next (Closure): Next middleware.
     *
     * Returns:
     *     Response: The response, or a redirect to the login page for inactive users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Reason: compare strictly with false — a partially loaded model (null) is not a deactivation.
        if ($user instanceof User && $user->is_active === false) {
            // logout() also rotates the remember token, which kills "remember me" on every device.
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => self::MESSAGE], 401);
            }

            return redirect()->route('login')->withErrors(['email' => self::MESSAGE]);
        }

        return $next($request);
    }
}
