<?php

namespace App\Support\Settings\Http;

use App\Support\Settings\RuntimeConfig;
use App\Support\Settings\UserPreferences;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the signed-in user's language preference (users.preferences.locale)
 * on top of the site default set by general.locale. Appended to the web group.
 */
class ApplyUserLocale
{
    /**
     * Handle an incoming request.
     *
     * Args:
     *     request (Request): The request.
     *     next (Closure): Next middleware.
     *
     * Returns:
     *     Response: The response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = UserPreferences::locale($request->user());

        if ($locale !== null && $locale !== app()->getLocale()) {
            RuntimeConfig::setLocale(app(), $locale);
        }

        return $next($request);
    }
}
