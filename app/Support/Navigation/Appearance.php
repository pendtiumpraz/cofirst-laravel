<?php

namespace App\Support\Navigation;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Throwable;

/**
 * Resolves the UI state that the server renders on <html> for every layout
 * (theme, navigation variants, density, colour mode ...).
 *
 * Precedence for each value: session preview (admin "Pratinjau", nothing saved)
 * → user preference (only where the owner allows it) → setting → built-in default.
 * Every value is whitelisted so a bad stored value can never break the markup.
 */
final class Appearance
{
    /**
     * Brand colours per theme that must be known outside CSS
     * (theme-color meta, PWA manifest, static icons). Mirrors the design spec.
     */
    public const THEMES = [
        'a' => ['canvas' => '#FAF8F4', 'canvas_dark' => '#0F0E0D', 'primary' => '#1D5D80', 'mark' => '#AE4027'],
        'b' => ['canvas' => '#FAFAFA', 'canvas_dark' => '#09090B', 'primary' => '#056A81', 'mark' => '#09090B'],
    ];

    /** Allowed values per state key (first value = fallback). */
    private const ALLOWED = [
        'theme' => ['a', 'b'],
        'logo' => ['a', 'b'],
        'nav' => ['sidebar', 'topnav'],
        'mobile_nav' => ['tabs', 'drawer'],
        'density' => ['comfortable', 'compact'],
        'stat' => ['strip', 'cards'],
        'text' => ['md', 'sm', 'lg'],
        'color_mode' => ['system', 'light', 'dark'],
        'auth_layout' => ['split', 'centered'],
    ];

    /**
     * Full UI state for the given (or current) user.
     *
     * @return array{theme:string, logo:string, nav:string, mobile_nav:string, density:string, stat:string,
     *               text:string, color_mode:string, default_color_mode:string, user_color_mode:?string,
     *               allow_color_mode:bool, dark:bool, reduce_motion:bool, auth_layout:string}
     */
    public static function state(?User $user = null): array
    {
        // Reason: no memo on purpose — settings are already cached in memory by SettingsManager,
        // and a memo would go stale in long-lived contexts (queue workers, tests) after a write.
        $user ??= auth()->user();
        $preview = self::preview();
        $pref = fn (string $key) => $user?->preference($key);

        $theme = $preview['theme']
            ?? (setting('appearance.allow_user_theme') ? $pref('theme') : null)
            ?? setting('appearance.theme');

        $allowColorMode = (bool) setting('appearance.allow_user_color_mode');
        $defaultColorMode = self::pick('color_mode', setting('appearance.color_mode'));
        $userColorMode = $allowColorMode && in_array($pref('color_mode'), self::ALLOWED['color_mode'], true) ? $pref('color_mode') : null;

        $state = [
            'theme' => self::pick('theme', $theme),
            'nav' => self::pick('nav', $preview['dashboard_nav'] ?? setting('appearance.dashboard_nav')),
            'mobile_nav' => self::pick('mobile_nav', $preview['mobile_nav'] ?? setting('appearance.mobile_nav')),
            'density' => self::pick('density', $preview['density'] ?? $pref('density') ?? setting('appearance.density')),
            'stat' => self::pick('stat', $preview['stat_style'] ?? setting('appearance.stat_style')),
            'text' => self::pick('text', $pref('text_size')),
            'color_mode' => $userColorMode ?? $defaultColorMode,
            'default_color_mode' => $defaultColorMode,
            'user_color_mode' => $userColorMode,
            'allow_color_mode' => $allowColorMode,
            'reduce_motion' => (bool) $pref('reduce_motion'),
            'auth_layout' => self::pick('auth_layout', $preview['auth_layout'] ?? setting('appearance.auth_layout')),
        ];
        $state['dark'] = $state['color_mode'] === 'dark';
        $state['logo'] = self::logoVariant($preview['logo_variant'] ?? null, $state['theme']);

        return $state;
    }

    /**
     * Resolve a logo variant ('a'|'b'|'auto'|null) to a concrete 'a' or 'b'.
     *
     * Args:
     *     variant (string|null): Requested variant; null reads appearance.logo_variant.
     *     theme (string|null): Active theme used for 'auto'; null reads the current state.
     *
     * Returns:
     *     string: 'a' or 'b'.
     */
    public static function logoVariant(?string $variant = null, ?string $theme = null): string
    {
        $variant ??= setting('appearance.logo_variant', 'auto');

        if (in_array($variant, self::ALLOWED['logo'], true)) {
            return $variant;
        }

        return self::pick('theme', $theme ?? self::state()['theme']);
    }

    /**
     * Attributes for the <html> element, already escaped.
     */
    public static function htmlAttributes(?User $user = null): string
    {
        $s = self::state($user);
        $classes = array_filter([$s['dark'] ? 'dark' : null, $s['reduce_motion'] ? 'reduce-motion' : null]);

        $attributes = [
            'lang' => 'id',
            'data-theme' => $s['theme'],
            'data-nav' => $s['nav'],
            'data-mobile-nav' => $s['mobile_nav'],
            'data-density' => $s['density'],
            'data-text' => $s['text'],
            'data-stat' => $s['stat'],
            // Colour-mode contract shared with resources/js/color-mode.js and <x-ui.color-mode-script/>.
            'data-color-mode' => $s['color_mode'],
            'data-default-color-mode' => $s['default_color_mode'],
            'data-allow-color-mode' => $s['allow_color_mode'] ? 'true' : 'false',
            'data-user-color-mode' => $s['user_color_mode'],
            'data-color-mode-endpoint' => $user || auth()->check() ? self::colorModeEndpoint() : null,
            'class' => implode(' ', $classes) ?: null,
        ];

        return collect($attributes)
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value, $key) => $key.'="'.e($value).'"')
            ->implode(' ');
    }

    /**
     * URL that persists a signed-in user's colour mode (POST {color_mode}), when the account module has one.
     */
    public static function colorModeEndpoint(): ?string
    {
        foreach (['account.color-mode', 'account.preferences.color-mode', 'preferences.color-mode'] as $name) {
            if (Route::has($name)) {
                return route($name);
            }
        }

        return null;
    }

    /**
     * Theme colours (hex) for a theme key.
     *
     * @return array{canvas:string, canvas_dark:string, primary:string, mark:string}
     */
    public static function colors(?string $theme = null): array
    {
        return self::THEMES[self::pick('theme', $theme ?? self::state()['theme'])];
    }

    /**
     * Public URL of the custom logo upload (light or dark), or null.
     */
    public static function customLogoUrl(bool $dark = false): ?string
    {
        $path = setting($dark ? 'appearance.custom_logo_dark' : 'appearance.custom_logo');

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (preg_match('#^(https?:)?//#', $path) || str_starts_with($path, '/')) {
            return $path;
        }

        try {
            return Storage::disk('public')->url($path);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * ` nonce="…"` (with a leading space) for inline scripts/styles when a CSP nonce is active, else ''.
     * Usage: <script{!! Appearance::nonceAttribute() !!}>
     */
    public static function nonceAttribute(): string
    {
        $nonce = Vite::cspNonce();

        return $nonce ? ' nonce="'.e($nonce).'"' : '';
    }

    /**
     * Session-only preview overrides set by the settings page ("Pratinjau").
     *
     * @return array<string, string>
     */
    private static function preview(): array
    {
        try {
            $preview = session('appearance_preview', []);
        } catch (Throwable) {
            return [];
        }

        return is_array($preview) ? array_filter($preview, 'is_string') : [];
    }

    /**
     * Whitelist a value for a state key, falling back to the first allowed value.
     */
    private static function pick(string $key, mixed $value): string
    {
        return in_array($value, self::ALLOWED[$key], true) ? $value : self::ALLOWED[$key][0];
    }
}
