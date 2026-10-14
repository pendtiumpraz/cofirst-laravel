<?php

namespace App\Http\Middleware;

use App\Services\Privacy\PolicyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Re-acceptance of changed policies (UU 27/2022 Pasal 21 ayat (2); PP 33/2026 Pasal 62 ayat (5), 67 ayat (4)).
 *
 * When a new privacy notice or terms version that requires re-acceptance is in force and
 * setting('privacy.policy_reacceptance_required') is on, signed-in users are sent to
 * privacy.accept before they continue. Only page navigations (GET, non-JSON) are redirected;
 * logout, privacy/legal pages, the privacy centre and PWA endpoints stay reachable so people
 * can read the change, object, or exercise their rights without accepting first.
 *
 * Also attaches the cookie banner to the dashboard layout (which has no include for it).
 */
class EnsurePolicyAccepted
{
    /** Route-name patterns that never redirect. */
    private const EXEMPT = [
        'privacy.*', 'legal.*', 'logout', 'login', 'register*', 'password.*', 'verification.*',
        'pwa.*', 'account.privacy.*', 'role.*',
    ];

    /** Session key holding the baseline signature the user already satisfied. */
    private const SESSION_KEY = 'privacy.accepted_baseline';

    public function __construct(private readonly PolicyService $policies)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->attachCookieBanner();

        $user = $request->user();

        if (! $user || ! $request->isMethod('GET') || $request->expectsJson() || $this->isExempt($request)
            || ! setting('privacy.policy_reacceptance_required', true)) {
            return $next($request);
        }

        try {
            $signature = $this->policies->baselineSignature();

            if ($signature === '' || ($request->hasSession() && $request->session()->get(self::SESSION_KEY) === $signature)) {
                return $next($request);
            }

            if ($this->policies->pendingAcceptances($user)->isEmpty()) {
                $request->session()->put(self::SESSION_KEY, $signature);

                return $next($request);
            }
        } catch (Throwable $e) {
            // Reason: a missing table during deploys must not lock everyone out.
            report($e);

            return $next($request);
        }

        $request->session()->put('url.intended', $request->fullUrl());

        return redirect()->route('privacy.accept');
    }

    /** Whether the current route may be visited without accepting. */
    private function isExempt(Request $request): bool
    {
        $route = $request->route();

        return ! $route || ! $route->getName() || $request->routeIs(...self::EXEMPT);
    }

    /**
     * Push the cookie banner onto the dashboard layout's scripts stack.
     *
     * Reason: layouts.app is owned by the shell and does not include the banner; a view
     * composer adds it without editing the layout. The partial is wrapped in @once, so a
     * later @include in the layout cannot render it twice.
     */
    private function attachCookieBanner(): void
    {
        $app = app();

        if ($app->bound('privacy.cookie_banner_composer')) {
            return;
        }

        $app->instance('privacy.cookie_banner_composer', true);

        View::composer('layouts.app', function ($view) {
            if (! view()->exists('privacy.partials.cookie-banner')) {
                return;
            }

            $view->getFactory()->startPush('scripts', view('privacy.partials.cookie-banner')->render());
        });
    }
}
