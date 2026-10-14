<?php

namespace App\Support\Navigation;

use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * Public legal / privacy links for footers. The pages belong to the privacy module, so
 * each link is resolved from a list of candidate route names and only shown when the
 * route exists (UU PDP: the notice, terms, cookie policy and data-subject rights must be
 * reachable from every public page).
 */
final class LegalLinks
{
    /** key => [label, candidate route names in order of preference] */
    private const CANDIDATES = [
        'privacy' => ['Kebijakan Privasi', ['privacy.notice', 'privacy.policy', 'legal.privacy']],
        'terms' => ['Syarat & Ketentuan', ['legal.terms', 'privacy.terms', 'terms']],
        'cookies' => ['Kebijakan Cookie', ['legal.cookies', 'privacy.cookies', 'legal.cookie-policy', 'privacy.cookie-policy']],
        'children' => ['Privasi Anak', ['privacy.children', 'legal.children']],
        'rights' => ['Hak Subjek Data', ['privacy.dsr.create', 'privacy.request.create', 'privacy.requests.create', 'privacy.rights', 'privacy.dsr']],
        'centre' => ['Pusat Privasi', ['privacy.index', 'privacy.center', 'privacy.centre']],
    ];

    /**
     * Existing links keyed by privacy|terms|cookies|children|rights|centre.
     *
     * @return array<string, array{label:string, route:string, url:string}>
     */
    public static function all(): array
    {
        $links = [];

        foreach (self::CANDIDATES as $key => [$label, $routes]) {
            foreach ($routes as $route) {
                if (! Route::has($route)) {
                    continue;
                }

                try {
                    $links[$key] = ['label' => $label, 'route' => $route, 'url' => route($route)];
                    break;
                } catch (Throwable) {
                    // Route needs parameters: not a landing page, try the next candidate.
                }
            }
        }

        return $links;
    }
}
