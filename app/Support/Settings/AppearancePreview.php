<?php

namespace App\Support\Settings;

use App\Support\Navigation\RouteAccess;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

/**
 * "Pratinjau" for the A/B appearance choices: an admin tries an option in their
 * own session without saving it, so nobody else is affected.
 *
 * The preview lives in session('appearance_preview') under the setting name
 * without the "appearance." prefix (theme, logo_variant, dashboard_nav, mobile_nav,
 * stat_style, density, auth_layout, landing_version, student_home) — the contract
 * read by App\Support\Navigation\Appearance. SettingsManager::get() returns the
 * previewed value for these keys, so every consumer of setting() follows it.
 */
final class AppearancePreview
{
    public const SESSION_KEY = 'appearance_preview';

    /** Previewable setting key => page that shows the effect. */
    public const TARGETS = [
        'appearance.theme' => 'dashboard',
        'appearance.logo_variant' => 'dashboard',
        'appearance.dashboard_nav' => 'dashboard',
        'appearance.mobile_nav' => 'dashboard',
        'appearance.stat_style' => 'dashboard',
        'appearance.density' => 'dashboard',
        'appearance.student_home' => 'student',
        'appearance.landing_version' => 'landing',
        'appearance.auth_layout' => 'auth',
    ];

    /** How long a "Pratinjau" link stays valid. */
    private const LINK_TTL_MINUTES = 120;

    /**
     * Whether the key can be previewed.
     */
    public static function supports(string $key): bool
    {
        return array_key_exists($key, self::TARGETS);
    }

    /**
     * Previewed values of the current session, keyed by setting key (only valid options).
     *
     * @return array<string, string>
     */
    public static function current(): array
    {
        try {
            $raw = session(self::SESSION_KEY, []);
        } catch (Throwable) {
            // Reason: no session in console commands / before StartSession — no preview.
            return [];
        }

        if (! is_array($raw) || $raw === []) {
            return [];
        }

        $values = [];
        foreach (self::TARGETS as $key => $target) {
            $value = $raw[self::sessionKey($key)] ?? null;

            if (is_string($value) && self::isValidOption($key, $value)) {
                $values[$key] = $value;
            }
        }

        return $values;
    }

    /**
     * Previewed value of one key, or null when none is active.
     */
    public static function value(string $key): ?string
    {
        if (! self::supports($key)) {
            return null;
        }

        return self::current()[$key] ?? null;
    }

    /**
     * Start (or change) the preview of one option in the current session.
     */
    public static function start(string $key, string $value): void
    {
        $preview = session(self::SESSION_KEY, []);
        $preview = is_array($preview) ? $preview : [];
        $preview[self::sessionKey($key)] = $value;

        session()->put(self::SESSION_KEY, $preview);
    }

    /**
     * End every preview of the current session.
     */
    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Signed "Pratinjau" link for an option, or null when the user cannot open the target page.
     */
    public static function url(string $key, string $value): ?string
    {
        if (! self::supports($key) || self::targetUrl($key) === null || ! Route::has('admin.settings.preview')) {
            return null;
        }

        return URL::temporarySignedRoute(
            'admin.settings.preview',
            now()->addMinutes(self::LINK_TTL_MINUTES),
            ['key' => $key, 'value' => $value],
        );
    }

    /**
     * The page that shows the effect of a previewable key for the current user (null = none).
     */
    public static function targetUrl(string $key): ?string
    {
        $route = match (self::TARGETS[$key] ?? null) {
            'dashboard' => 'admin.dashboard',
            'student' => 'student.dashboard',
            'landing' => 'landing',
            'auth' => 'admin.settings.preview.auth',
            default => null,
        };

        if ($route === null || ! Route::has($route)) {
            return null;
        }

        $user = auth()->user();
        // Reason: only link pages the admin may open (e.g. not the student home for a pure admin).
        if ($route !== 'landing' && (! $user || ! RouteAccess::allows($user, $route))) {
            return null;
        }

        return route($route);
    }

    /**
     * Whether the value is one of the field's configured options.
     */
    public static function isValidOption(string $key, string $value): bool
    {
        // Reason: field keys contain dots, so config("settings.fields.{$key}") would walk the wrong path.
        $options = config('settings.fields', [])[$key]['options'] ?? null;

        return is_array($options) && array_key_exists($value, $options);
    }

    /**
     * Session key of a setting key ("appearance.theme" → "theme").
     */
    public static function sessionKey(string $key): string
    {
        return Str::after($key, 'appearance.');
    }
}
