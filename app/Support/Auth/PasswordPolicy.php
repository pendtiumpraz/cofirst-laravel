<?php

namespace App\Support\Auth;

use Illuminate\Validation\Rules\Password;

/**
 * Password rules driven by Admin › Pengaturan › Keamanan (setting('security.password_*')).
 *
 * The same requirements feed the server-side validation rule and the client-side
 * strength meter, so what the form promises is exactly what the server enforces.
 */
final class PasswordPolicy
{
    /** Hard floor, whatever the setting says (NIST SP 800-63B minimum). */
    public const ABSOLUTE_MIN = 8;

    public const ABSOLUTE_MAX = 64;

    /**
     * Validation rule for a new password.
     *
     * Reason: Password::uncompromised() is deliberately not used — it sends a hash
     * prefix of the password to a third-party API, which needs a processor agreement
     * under UU PDP before it may be enabled.
     *
     * Returns:
     *     Password: The configured rule object.
     */
    public static function rule(): Password
    {
        $rule = Password::min(self::minLength())->max(255);

        if (self::flag('security.password_require_mixed_case', true)) {
            $rule->mixedCase();
        } else {
            $rule->letters();
        }

        if (self::flag('security.password_require_numbers', true)) {
            $rule->numbers();
        }

        if (self::flag('security.password_require_symbols', false)) {
            $rule->symbols();
        }

        return $rule;
    }

    /**
     * Minimum length from settings, clamped to a safe range.
     */
    public static function minLength(): int
    {
        $value = (int) self::setting('security.password_min_length', self::ABSOLUTE_MIN);

        return max(self::ABSOLUTE_MIN, min(self::ABSOLUTE_MAX, $value));
    }

    /**
     * Requirements for the strength meter. Patterns use JavaScript Unicode syntax
     * (flag "u") and mirror Laravel's Password rule checks.
     *
     * Returns:
     *     array<int, array{key:string, label:string, pattern:string, flags:string}>
     */
    public static function requirements(): array
    {
        $min = self::minLength();

        $items = [
            ['key' => 'length', 'label' => "Minimal {$min} karakter", 'pattern' => '^[\s\S]{'.$min.',}$', 'flags' => 'u'],
        ];

        if (self::flag('security.password_require_mixed_case', true)) {
            $items[] = ['key' => 'mixed', 'label' => 'Huruf besar dan huruf kecil', 'pattern' => '(?=.*\p{Lu})(?=.*\p{Ll})', 'flags' => 'u'];
        } else {
            $items[] = ['key' => 'letters', 'label' => 'Mengandung huruf', 'pattern' => '\p{L}', 'flags' => 'u'];
        }

        if (self::flag('security.password_require_numbers', true)) {
            $items[] = ['key' => 'numbers', 'label' => 'Mengandung angka', 'pattern' => '\p{N}', 'flags' => 'u'];
        }

        if (self::flag('security.password_require_symbols', false)) {
            $items[] = ['key' => 'symbols', 'label' => 'Mengandung simbol (mis. ! ? # @)', 'pattern' => '[\p{Z}\p{S}\p{P}]', 'flags' => 'u'];
        }

        return $items;
    }

    /**
     * One-sentence summary of the requirements (shown without JavaScript too).
     */
    public static function summary(): string
    {
        $short = ['mixed' => 'huruf besar & kecil', 'letters' => 'huruf', 'numbers' => 'angka', 'symbols' => 'simbol'];
        $parts = [];

        foreach (self::requirements() as $item) {
            if (isset($short[$item['key']])) {
                $parts[] = $short[$item['key']];
            }
        }

        $text = 'Minimal '.self::minLength().' karakter';

        if ($parts !== []) {
            $last = array_pop($parts);
            $text .= ', memuat '.($parts !== [] ? implode(', ', $parts).' serta ' : '').$last;
        }

        return $text.'.';
    }

    /**
     * Read a boolean setting safely (settings table may be missing on a fresh install).
     */
    private static function flag(string $key, bool $default): bool
    {
        return filter_var(self::setting($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Read a setting through the global helper when it is available.
     */
    private static function setting(string $key, mixed $default): mixed
    {
        return function_exists('setting') ? setting($key, $default) : $default;
    }
}
