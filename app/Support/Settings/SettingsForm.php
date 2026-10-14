<?php

namespace App\Support\Settings;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Turns a submitted Admin › Pengaturan form into typed values and persists them:
 * only changed keys are written (so settings.updated_by stays meaningful), image
 * uploads go to the public disk under settings/, and every write runs in one
 * transaction. Resetting removes stored rows so the configured default applies.
 */
class SettingsForm
{
    /** Disk and folder of uploaded setting images (logos). */
    public const DISK = 'public';

    public const FOLDER = 'settings';

    public function __construct(
        private SettingsManager $settings,
        private SettingsRegistry $registry,
    ) {}

    /**
     * Clean raw input before validation: drop empty repeater rows, split list
     * fields into lines and default missing multi-value fields to an empty list.
     *
     * Args:
     *     group (string): Group being saved.
     *     input (array): The raw "s" input array.
     *
     * Returns:
     *     array: The cleaned "s" input array.
     */
    public function prepare(string $group, array $input): array
    {
        foreach ($this->registry->fields($group) as $key => $field) {
            $value = Arr::get($input, $key);

            $clean = match ($field['type']) {
                'repeater' => $this->cleanRows($value, array_keys($field['fields'])),
                'list' => $this->cleanLines($value),
                'multiselect' => array_values(array_filter((array) ($value ?? []), fn ($v) => is_string($v) && $v !== '')),
                'boolean' => $value ?? '0',
                default => $value,
            };

            if ($field['type'] !== 'image') {
                Arr::set($input, $key, $clean);
            }
        }

        return $input;
    }

    /**
     * Persist the validated values of a group.
     *
     * Args:
     *     group (string): Group being saved.
     *     request (Request): The validated request (files are read from it).
     *     user (User|null): Editor recorded in settings.updated_by.
     *
     * Returns:
     *     array<int, string>: Keys whose value actually changed.
     */
    public function save(string $group, Request $request, ?User $user): array
    {
        $changes = [];
        $newFiles = [];
        $obsoleteFiles = [];

        try {
            foreach ($this->registry->fields($group) as $key => $field) {
                $current = $this->settings->saved($key);

                if ($field['type'] === 'image') {
                    $file = $request->file(SettingsRegistry::INPUT.'.'.$key);
                    $remove = $request->boolean(SettingsRegistry::REMOVE.'.'.$key);

                    if ($file instanceof UploadedFile) {
                        $path = $this->storeImage($file);
                        $newFiles[] = $path;
                        $changes[$key] = $path;
                        $obsoleteFiles[] = $current;
                    } elseif ($remove && filled($current)) {
                        $changes[$key] = null;
                        $obsoleteFiles[] = $current;
                    }

                    continue;
                }

                $value = $this->cast($field, $request->input(SettingsRegistry::INPUT.'.'.$key));

                if (! $this->same($value, $current)) {
                    $changes[$key] = $value;
                }
            }

            if ($changes !== []) {
                DB::transaction(fn () => $this->settings->setMany($changes, $user?->getKey()));
            }
        } catch (Throwable $e) {
            // Reason: a failed save must not leave orphaned uploads on the public disk.
            $this->deleteImages($newFiles);

            throw $e;
        }

        $this->deleteImages($obsoleteFiles);

        return array_keys($changes);
    }

    /**
     * Reset one field or every stored field of a group to its default.
     *
     * Args:
     *     group (string): Group to reset.
     *     key (string|null): Single field key; null resets the whole group.
     *
     * Returns:
     *     array<int, string>: Keys that were reset (had a stored value).
     */
    public function reset(string $group, ?string $key = null): array
    {
        $fields = $this->registry->fields($group);
        $keys = $key !== null ? array_intersect([$key], array_keys($fields)) : array_keys($fields);
        $keys = array_values(array_filter($keys, fn (string $k) => $this->settings->isStored($k)));

        if ($keys === []) {
            return [];
        }

        $images = collect($keys)
            ->filter(fn (string $k) => $fields[$k]['type'] === 'image')
            ->map(fn (string $k) => $this->settings->saved($k))
            ->all();

        DB::transaction(fn () => $this->settings->forgetMany($keys));
        $this->deleteImages($images);

        return $keys;
    }

    /**
     * Cast a submitted value to the type of its field.
     *
     * @param  array<string, mixed>  $field
     */
    public function cast(array $field, mixed $value): mixed
    {
        return match ($field['type']) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => $this->castNumber($field, $value),
            'multiselect' => array_values(array_intersect(array_keys($field['options']), array_map('strval', (array) ($value ?? [])))),
            'repeater' => $this->castRows($value, array_keys($field['fields'])),
            'list' => array_values(array_map('strval', (array) ($value ?? []))),
            'select', 'choice' => $value === null ? null : (string) $value,
            default => $value === null ? '' : trim((string) $value),
        };
    }

    /**
     * Store an uploaded image under settings/ with a random name and an extension
     * derived from its content (never from the client file name).
     */
    private function storeImage(UploadedFile $file): string
    {
        $path = $file->store(self::FOLDER, self::DISK);

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('Gagal menyimpan gambar pengaturan.');
        }

        return $path;
    }

    /**
     * Delete images this module stored (never paths outside settings/).
     *
     * @param  array<int, mixed>  $paths
     */
    private function deleteImages(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_string($path) && str_starts_with($path, self::FOLDER.'/') && ! str_contains($path, '..')) {
                try {
                    Storage::disk(self::DISK)->delete($path);
                } catch (Throwable) {
                    // A missing file is fine — the setting no longer points to it.
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function castNumber(array $field, mixed $value): int|float|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        return in_array('integer', $field['rules'], true) ? (int) $value : $value + 0;
    }

    /**
     * Keep only the defined sub-fields of each row, as trimmed strings.
     *
     * @param  array<int, string>  $names
     * @return array<int, array<string, string>>
     */
    private function castRows(mixed $rows, array $names): array
    {
        return collect(is_array($rows) ? $rows : [])
            ->filter(fn ($row) => is_array($row))
            ->map(fn (array $row) => collect($names)->mapWithKeys(fn ($name) => [$name => trim((string) ($row[$name] ?? ''))])->all())
            ->values()
            ->all();
    }

    /**
     * Drop repeater rows where every sub-field is empty and re-index the rest.
     *
     * @param  array<int, string>  $names
     * @return array<int, array<string, mixed>>
     */
    private function cleanRows(mixed $rows, array $names): array
    {
        return collect(is_array($rows) ? $rows : [])
            ->filter(fn ($row) => is_array($row) && collect($names)->contains(fn ($name) => filled($row[$name] ?? null)))
            ->map(fn (array $row) => Arr::only($row, $names))
            ->values()
            ->all();
    }

    /**
     * Split a textarea into trimmed, non-empty lines (arrays are kept).
     *
     * @return array<int, string>
     */
    private function cleanLines(mixed $value): array
    {
        $lines = is_array($value) ? $value : preg_split('/\R/u', (string) $value);

        return array_values(array_filter(array_map(fn ($line) => trim((string) $line), $lines), fn ($line) => $line !== ''));
    }

    /**
     * Whether a submitted (cast) value equals the saved one, so unchanged fields are not rewritten.
     * Reason: stored JSON may hold "1" where the form now casts 1, and null where the form sends ''.
     */
    private function same(mixed $new, mixed $current): bool
    {
        if (is_array($new) || is_array($current)) {
            return json_encode($new) === json_encode($current);
        }

        if (is_bool($new)) {
            return $new === filter_var($current, FILTER_VALIDATE_BOOLEAN);
        }

        if (is_numeric($new) && is_numeric($current)) {
            return $new + 0 == $current + 0;
        }

        return (string) ($new ?? '') === (string) ($current ?? '');
    }
}
