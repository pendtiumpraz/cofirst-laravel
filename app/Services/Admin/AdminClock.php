<?php

namespace App\Services\Admin;

use Illuminate\Support\Carbon;

/**
 * Business-time helpers for admin screens.
 *
 * Reason: config/app.php still runs in UTC; "today" and month boundaries must
 * follow the school's timezone (setting general.timezone, default WIB) or the
 * dashboard shows yesterday's schedule between 00:00 and 07:00 WIB.
 */
final class AdminClock
{
    /** The configured business timezone. */
    public static function timezone(): string
    {
        try {
            return (string) (setting('general.timezone') ?: 'Asia/Jakarta');
        } catch (\Throwable) {
            return 'Asia/Jakarta';
        }
    }

    /** Current time in the business timezone with the Indonesian locale. */
    public static function now(): Carbon
    {
        return Carbon::now(self::timezone())->locale('id');
    }

    /** Today's ISO weekday (1 = Senin … 7 = Minggu). */
    public static function isoWeekday(): int
    {
        return self::now()->dayOfWeekIso;
    }

    /**
     * Format a date in Indonesian, e.g. "13 Okt 2026".
     *
     * Args:
     *     date (mixed): Carbon/DateTime/string or null.
     *     format (string): translatedFormat pattern.
     */
    public static function format(mixed $date, string $format = 'd M Y'): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        return Carbon::parse($date)->locale('id')->translatedFormat($format);
    }

    /**
     * Rupiah formatting: 1250000 → "Rp 1.250.000".
     */
    public static function rupiah(int|float|string|null $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }

    /**
     * Compact rupiah for KPIs: 48200000 → "Rp 48,2 jt".
     */
    public static function rupiahCompact(int|float|string|null $amount): string
    {
        $value = (float) $amount;
        $abs = abs($value);

        return match (true) {
            $abs >= 1_000_000_000 => 'Rp '.number_format($value / 1_000_000_000, 1, ',', '.').' M',
            $abs >= 1_000_000 => 'Rp '.number_format($value / 1_000_000, 1, ',', '.').' jt',
            $abs >= 1_000 => 'Rp '.number_format($value / 1_000, 0, ',', '.').' rb',
            default => 'Rp '.number_format($value, 0, ',', '.'),
        };
    }
}
