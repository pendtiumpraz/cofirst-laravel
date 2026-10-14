<?php

namespace App\Support\Settings;

use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Validation\Rules\Password;
use Throwable;

/**
 * Applies saved settings to the framework configuration on every boot, so the
 * owner's choices in Admin › Pengaturan take effect without editing .env:
 * app name, timezone, locale (+ Carbon), mail sender, session lifetime and the
 * default password rule.
 */
final class RuntimeConfig
{
    /** Locales the UI supports (general.locale and the user preference). */
    public const LOCALES = ['id', 'en'];

    /**
     * Apply the settings to the running application.
     *
     * Args:
     *     app (Application): The application instance.
     *     settings (SettingsManager): Settings source.
     */
    public static function apply(Application $app, SettingsManager $settings): void
    {
        try {
            self::applyIdentity($settings);
            self::applyRegional($app, $settings);
            self::applyMail($settings);
            self::applySession($settings);
        } catch (Throwable $e) {
            // Reason: a broken stored value must never take the whole site down; config defaults stay.
            report($e);
        }

        // Reason: resolved lazily, so a password-policy change applies to the very next validation.
        Password::defaults(fn () => self::passwordRule($settings));
    }

    /**
     * The password rule built from security.password_* settings.
     *
     * Args:
     *     settings (SettingsManager): Settings source.
     *
     * Returns:
     *     Password: Rule for new passwords (registration, reset, change).
     */
    public static function passwordRule(SettingsManager $settings): Password
    {
        $rule = Password::min(max(8, min(64, (int) $settings->get('security.password_min_length', 8))))->max(255);

        if ($settings->enabled('security.password_require_mixed_case')) {
            $rule->mixedCase();
        }
        if ($settings->enabled('security.password_require_numbers')) {
            $rule->numbers();
        }
        if ($settings->enabled('security.password_require_symbols')) {
            $rule->symbols();
        }

        return $rule;
    }

    /**
     * Set the locale of the translator, the config and Carbon (Indonesian month names).
     */
    public static function setLocale(Application $app, string $locale): void
    {
        if (! in_array($locale, self::LOCALES, true)) {
            return;
        }

        $app->setLocale($locale);
        Carbon::setLocale($locale);
    }

    private static function applyIdentity(SettingsManager $settings): void
    {
        $name = trim((string) $settings->saved('general.site_name'));

        if ($name !== '') {
            config(['app.name' => $name]);
        }
    }

    private static function applyRegional(Application $app, SettingsManager $settings): void
    {
        $timezone = (string) $settings->saved('general.timezone');

        if (in_array($timezone, timezone_identifiers_list(), true)) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }

        self::setLocale($app, (string) $settings->saved('general.locale'));
    }

    private static function applyMail(SettingsManager $settings): void
    {
        $address = $settings->saved('notifications.from_address');
        $name = trim((string) $settings->saved('notifications.from_name'));

        if (is_string($address) && filter_var($address, FILTER_VALIDATE_EMAIL)) {
            config(['mail.from.address' => $address]);
        }
        if ($name !== '') {
            config(['mail.from.name' => $name]);
        }
    }

    private static function applySession(SettingsManager $settings): void
    {
        $minutes = (int) $settings->saved('security.session_lifetime_minutes');

        if ($minutes >= 5) {
            config(['session.lifetime' => $minutes]);
        }
    }
}
