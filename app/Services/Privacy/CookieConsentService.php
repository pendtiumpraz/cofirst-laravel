<?php

namespace App\Services\Privacy;

use App\Models\Privacy\ConsentPurpose;
use App\Models\User;
use DomainException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Cookie / local-storage consent (UU 27/2022 Pasal 20 ayat (2) huruf a, 22; PP 33/2026 Pasal 32-35).
 *
 * Essential storage needs no opt-in; `functional` and `analytics` are off until the visitor
 * opts in. The choice lives in the first-party cookie `cf_consent` (JSON, readable by the
 * banner script, excluded from cookie encryption) and, for signed-in accounts, in the
 * consent ledger (purposes cookies_functional / cookies_analytics, method=banner).
 * Children never get analytics, whatever they choose (no profiling of children).
 */
class CookieConsentService
{
    public const COOKIE = 'cf_consent';

    public const LIFETIME_DAYS = 180;

    /** Optional cookie category => consent purpose key. */
    public const PURPOSES = [
        'functional' => 'cookies_functional',
        'analytics' => 'cookies_analytics',
    ];

    public function __construct(private readonly ConsentService $consents)
    {
    }

    /**
     * Consent version for cookies: the highest version of the cookie purposes.
     *
     * Reason: bumping a cookie purpose's version (material change) asks everyone again.
     */
    public function version(): int
    {
        $versions = collect(self::PURPOSES)
            ->map(fn ($key) => $this->consents->purpose($key)?->version)
            ->filter();

        return (int) ($versions->max() ?? 1);
    }

    /**
     * The visitor's current choice.
     *
     * Args:
     *     request (Request): Current request (reads the cookie).
     *     user (User|null): Signed-in account, used when the cookie is missing.
     *
     * Returns:
     *     array{decided: bool, version: int, minor: bool, choices: array{functional: bool, analytics: bool}, decided_at: int|null}
     */
    public function state(Request $request, ?User $user = null): array
    {
        $version = $this->version();
        $minor = $user ? $this->consents->isChild($user) : false;
        $cookie = $this->read($request);
        $choices = ['functional' => false, 'analytics' => false];
        $decided = false;
        $decidedAt = null;

        if ($cookie && ($cookie['v'] ?? null) === $version) {
            $decided = true;
            $choices = ['functional' => (bool) ($cookie['functional'] ?? false), 'analytics' => (bool) ($cookie['analytics'] ?? false)];
            $decidedAt = isset($cookie['ts']) ? (int) $cookie['ts'] : null;
        } elseif ($user) {
            // Reason: a signed-in user who chose on another device should not be asked again.
            $states = $this->consents->currentStates($user);
            $known = collect(self::PURPOSES)->every(fn ($key) => $states->has($key));
            if ($known) {
                $decided = true;
                foreach (self::PURPOSES as $category => $key) {
                    $choices[$category] = $this->consents->hasConsent($user, $key);
                }
            }
        }

        if ($minor) {
            $choices['analytics'] = false;
        }

        return compact('decided', 'version', 'minor', 'choices') + ['decided_at' => $decidedAt];
    }

    /**
     * Normalise submitted choices.
     *
     * Args:
     *     action (string|null): accept_all|reject_optional|custom.
     *     functional (bool): Custom choice.
     *     analytics (bool): Custom choice.
     *     minor (bool): Subject is a child (analytics forced off).
     *
     * Returns:
     *     array{functional: bool, analytics: bool}
     */
    public function choices(?string $action, bool $functional, bool $analytics, bool $minor = false): array
    {
        $choices = match ($action) {
            'accept_all' => ['functional' => true, 'analytics' => true],
            'reject_optional' => ['functional' => false, 'analytics' => false],
            default => ['functional' => $functional, 'analytics' => $analytics],
        };

        if ($minor) {
            $choices['analytics'] = false;
        }

        return $choices;
    }

    /**
     * The consent cookie for a choice.
     *
     * Args:
     *     choices (array{functional: bool, analytics: bool}): Normalised choices.
     *     secure (bool): Mark the cookie Secure (HTTPS requests).
     *
     * Returns:
     *     Cookie: First-party, not HttpOnly (the banner script reads it), SameSite=Lax.
     */
    public function makeCookie(array $choices, bool $secure = false): Cookie
    {
        $value = json_encode([
            'v' => $this->version(),
            'functional' => (bool) $choices['functional'],
            'analytics' => (bool) $choices['analytics'],
            'ts' => now()->getTimestampMs(),
        ]);

        return cookie(self::COOKIE, $value, self::LIFETIME_DAYS * 24 * 60, '/', null, $secure, false, false, 'lax');
    }

    /**
     * Record a signed-in user's choice in the consent ledger (only changes are written).
     *
     * Args:
     *     user (User): Signed-in account.
     *     choices (array{functional: bool, analytics: bool}): Normalised choices.
     *     request (Request): Evidence (IP / user agent).
     *     method (string): banner|privacy_centre.
     *     source (string): Where the choice was made (banner|page), kept as evidence.
     */
    public function record(User $user, array $choices, Request $request, string $method = 'banner', string $source = 'banner'): void
    {
        $evidence = ['source' => 'cookie_'.$source];

        foreach (self::PURPOSES as $category => $key) {
            $purpose = $this->consents->purpose($key);
            if (! $purpose instanceof ConsentPurpose || ! $purpose->is_active) {
                continue;
            }

            $wanted = (bool) ($choices[$category] ?? false);
            $current = $this->consents->hasConsent($user, $key);

            try {
                if ($wanted && ! $current) {
                    $this->consents->grant($user, $key, $method, $user, $evidence, $request);
                } elseif (! $wanted && $current) {
                    $this->consents->withdraw($user, $key, $method, $user, $evidence, $request);
                } elseif (! $wanted && ! $this->hasAnyRecord($user, $key)) {
                    $this->consents->deny($user, $key, $method, $user, $evidence, $request);
                }
            } catch (DomainException) {
                // Reason: a child cannot opt in alone; the cookie stays "off" for that category.
                continue;
            }
        }
    }

    /** Parse the consent cookie. */
    private function read(Request $request): ?array
    {
        $raw = $request->cookie(self::COOKIE);

        if (! is_string($raw) || $raw === '' || strlen($raw) > 500) {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : null;
    }

    /** Whether the user has any ledger row for a purpose (first "reject" is recorded too). */
    private function hasAnyRecord(User $user, string $key): bool
    {
        return $this->consents->currentStates($user)->has($key);
    }
}
