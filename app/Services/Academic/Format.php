<?php

namespace App\Services\Academic;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Indonesian presentation helpers for the academic views (dates in the school's
 * timezone with Indonesian month names, percentages, safe Markdown).
 */
class Format
{
    /** The school's timezone (setting general.timezone, default Asia/Jakarta). */
    public static function timezone(): string
    {
        try {
            return (string) (setting('general.timezone', 'Asia/Jakarta') ?: 'Asia/Jakarta');
        } catch (\Throwable) {
            return 'Asia/Jakarta';
        }
    }

    /**
     * Format a date, e.g. "13 Okt 2026".
     *
     * Args:
     *     value (mixed): Carbon, date string or null.
     *     format (string): translatedFormat pattern.
     *
     * Returns:
     *     string: The formatted date, or '—' when empty.
     */
    public static function date(mixed $value, string $format = 'd M Y'): string
    {
        $date = self::carbon($value, false);

        return $date ? $date->translatedFormat($format) : '—';
    }

    /**
     * Format a date-time in the school's timezone, e.g. "13 Okt 2026, 19.30".
     *
     * Returns:
     *     string: The formatted value, or '—' when empty.
     */
    public static function dateTime(mixed $value, string $format = 'd M Y, H.i'): string
    {
        $date = self::carbon($value, true);

        return $date ? $date->translatedFormat($format) : '—';
    }

    /** Format a time of day as "14.00" (Indonesian style). */
    public static function time(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('H.i');
        }

        return filled($value) ? str_replace(':', '.', substr((string) $value, 0, 5)) : '—';
    }

    /** Format a percentage, e.g. 72.5 → "72,5%" and 100 → "100%". */
    public static function percent(float|int|string|null $value): string
    {
        $number = (float) $value;
        $decimals = fmod($number, 1.0) === 0.0 ? 0 : 1;

        return number_format($number, $decimals, ',', '.').'%';
    }

    /**
     * Render Markdown safely: raw HTML is stripped and unsafe links removed.
     *
     * Reason: material content is written by admins and shown to every role; rendering
     * it as raw HTML would allow stored XSS (admin → superadmin escalation).
     */
    public static function markdown(?string $text): HtmlString
    {
        if (blank($text)) {
            return new HtmlString('');
        }

        return new HtmlString(Str::markdown((string) $text, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]));
    }

    /**
     * Normalise a value to Carbon with the Indonesian locale.
     *
     * Args:
     *     value (mixed): Carbon, string or null.
     *     shiftTimezone (bool): Convert to the school's timezone (date-times only).
     */
    private static function carbon(mixed $value, bool $shiftTimezone): ?CarbonInterface
    {
        if (blank($value)) {
            return null;
        }

        try {
            $date = $value instanceof CarbonInterface ? $value->copy() : Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }

        if ($shiftTimezone) {
            $date = $date->setTimezone(self::timezone());
        }

        return $date->locale('id');
    }
}
