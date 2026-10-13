<?php

namespace App\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast for list-like TEXT columns (curriculum objectives, syllabus learning
 * objectives and activities).
 *
 * The value is stored as plain text with one item per line. It always reads back
 * as a string (safe to echo in Blade and to put in a <textarea>), and accepts
 * either a string or an array when written. Legacy JSON-encoded arrays/strings are
 * decoded transparently. Use the models' `*_list` accessors for an array of items.
 */
class MultilineText implements CastsAttributes
{
    /**
     * Read the stored value as newline-separated text.
     *
     * Returns:
     *     string|null: Text with one item per line.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        if (is_array($decoded)) {
            return implode("\n", self::toList($decoded));
        }

        return is_string($decoded) ? $decoded : (string) $value;
    }

    /**
     * Store arrays as newline-separated text; strings are stored as-is.
     *
     * Returns:
     *     string|null: The text to store.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_array($value)) {
            $value = implode("\n", self::toList($value));
        }

        return $value === null ? null : (string) $value;
    }

    /**
     * Split text (or a list) into trimmed, non-empty items.
     *
     * Args:
     *     value (mixed): Newline-separated text, a JSON list or an array.
     *
     * Returns:
     *     array<int, string>: The items.
     */
    public static function toList(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : preg_split('/\r\n|\r|\n/', $value);
        }

        // Reason: users often type "- item" or "• item" in the textarea; strip the bullet.
        $items = array_map(
            fn ($item) => trim((string) preg_replace('/^\s*[-*\x{2022}]\s*/u', '', (string) $item)),
            array_filter((array) $value, 'is_scalar')
        );

        return array_values(array_filter($items, fn (string $item) => $item !== ''));
    }
}
