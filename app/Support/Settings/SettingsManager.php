<?php

namespace App\Support\Settings;

use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Reads and writes application settings.
 *
 * Definitions (type, default, validation, label) live in config/settings.php.
 * Stored values override the defaults and are cached forever; the cache is
 * flushed on every write. When the settings table does not exist yet (fresh
 * install before migrating) the defaults are returned so the app still boots.
 */
class SettingsManager
{
    public const CACHE_KEY = 'app.settings.values';

    /** @var array<string, mixed>|null In-request memo of stored values. */
    protected ?array $stored = null;

    public function __construct(protected CacheRepository $cache)
    {
    }

    /**
     * Get a setting value, falling back to its configured default.
     *
     * Args:
     *     key (string): Dot-notation key, e.g. "appearance.theme".
     *     default (mixed): Used when the key has no stored value and no configured default.
     *
     * Returns:
     *     mixed: The resolved value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $stored = $this->stored();

        if (array_key_exists($key, $stored)) {
            return $stored[$key];
        }

        $definition = $this->definition($key);

        if ($definition !== null && array_key_exists('default', $definition)) {
            return $definition['default'];
        }

        return $default;
    }

    /**
     * Whether a boolean-ish setting is enabled.
     */
    public function enabled(string $key): bool
    {
        return filter_var($this->get($key, false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Persist a single setting and flush the cache.
     */
    public function set(string $key, mixed $value, ?int $userId = null): void
    {
        $this->setMany([$key => $value], $userId);
    }

    /**
     * Persist several settings at once and flush the cache.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values, ?int $userId = null): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'updated_by' => $userId]
            );
        }

        $this->flush();
    }

    /**
     * Remove a stored value so the configured default applies again.
     */
    public function forget(string $key): void
    {
        Setting::where('key', $key)->delete();
        $this->flush();
    }

    /**
     * All settings (defaults merged with stored values), keyed by dot-notation key.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $defaults = collect($this->definitions())
            ->map(fn (array $definition) => $definition['default'] ?? null)
            ->all();

        return array_merge($defaults, $this->stored());
    }

    /**
     * All settings of a group, e.g. "privacy".
     *
     * @return array<string, mixed>
     */
    public function group(string $group): array
    {
        return collect($this->all())
            ->filter(fn ($value, string $key) => ($this->definition($key)['group'] ?? Str::before($key, '.')) === $group)
            ->all();
    }

    /**
     * Field definitions from config/settings.php.
     *
     * @return array<string, array<string, mixed>>
     */
    public function definitions(): array
    {
        return config('settings.fields', []);
    }

    /**
     * Definition of one field, or null when unknown.
     *
     * @return array<string, mixed>|null
     */
    public function definition(string $key): ?array
    {
        // Keys are flat strings that contain dots, so no Arr::get() traversal here.
        $definitions = $this->definitions();

        return isset($definitions[$key]) && is_array($definitions[$key]) ? $definitions[$key] : null;
    }

    /**
     * Group metadata (label, icon, description) from config/settings.php.
     *
     * @return array<string, array<string, mixed>>
     */
    public function groups(): array
    {
        return config('settings.groups', []);
    }

    /**
     * Clear the cached values (called after every write).
     */
    public function flush(): void
    {
        $this->stored = null;

        try {
            $this->cache->forget(self::CACHE_KEY);
        } catch (Throwable) {
            // Cache store unavailable (e.g. database cache before migrating) — nothing to clear.
        }
    }

    /**
     * Stored values keyed by setting key (cached).
     *
     * @return array<string, mixed>
     */
    protected function stored(): array
    {
        if ($this->stored !== null) {
            return $this->stored;
        }

        try {
            $this->stored = $this->cache->rememberForever(self::CACHE_KEY, function () {
                if (! Schema::hasTable('settings')) {
                    return [];
                }

                return Setting::query()->pluck('value', 'key')->all();
            });
        } catch (Throwable) {
            // Reason: the app must still boot before migrations have run
            // (or when the cache table is missing); defaults apply.
            $this->stored = [];
        }

        return $this->stored;
    }
}
