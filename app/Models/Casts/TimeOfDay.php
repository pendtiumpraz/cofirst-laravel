<?php

namespace App\Models\Casts;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast for SQL TIME columns (schedules/class_reports start_time, end_time).
 *
 * Stores a plain "H:i:s" string, so a TIME column never receives a full datetime
 * (the old 'datetime' cast wrote "YYYY-MM-DD 09:00:00", which broke ordering and
 * comparisons on SQLite). Reading returns a Carbon instance on today's date so
 * existing code like `$schedule->start_time->format('H:i')` keeps working, and
 * arrays/JSON serialise the value as "H:i".
 */
class TimeOfDay implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * Convert the stored value to a Carbon time on today's date.
     *
     * Args:
     *     model (Model): The model.
     *     key (string): Attribute name.
     *     value (mixed): Raw value from the database.
     *     attributes (array): All raw attributes.
     *
     * Returns:
     *     Carbon|null: The time of day, or null.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        $time = self::normalize($value);

        return $time === null ? null : Carbon::today()->setTimeFromTimeString($time);
    }

    /**
     * Normalise the given value to "H:i:s" for storage.
     *
     * Returns:
     *     string|null: The time string, or null.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return self::normalize($value);
    }

    /**
     * Serialise the attribute as "H:i" for arrays and JSON.
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        // Reason: Eloquent has already turned the Carbon into a UTC ISO string at this
        // point, so the raw stored time is used to avoid a timezone shift.
        $time = self::normalize($attributes[$key] ?? $value);

        return $time === null ? null : substr($time, 0, 5);
    }

    /**
     * Extract an "H:i:s" string from a time string, datetime string or DateTime.
     *
     * Args:
     *     value (mixed): "9:00", "09:00:00", "2026-01-01 09:00:00" or a DateTimeInterface.
     *
     * Returns:
     *     string|null: Normalised "H:i:s", or null for empty input.
     */
    public static function normalize(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('H:i:s');
        }

        // Reason: legacy rows may hold a full datetime; keep only the time part.
        if (preg_match('/(\d{1,2}):(\d{2})(?::(\d{2}))?/', (string) $value, $m)) {
            return sprintf('%02d:%02d:%02d', (int) $m[1], (int) $m[2], (int) ($m[3] ?? 0));
        }

        return Carbon::parse((string) $value)->format('H:i:s');
    }
}
