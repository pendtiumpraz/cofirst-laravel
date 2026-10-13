<?php

use App\Support\Settings\SettingsManager;

if (! function_exists('setting')) {
    /**
     * Read an application setting (see config/settings.php for all keys).
     *
     * Args:
     *     key (string|null): Dot-notation key; null returns the SettingsManager.
     *     default (mixed): Fallback when the key has no stored or configured value.
     *
     * Returns:
     *     mixed: The setting value, or the manager when no key is given.
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        $manager = app(SettingsManager::class);

        return $key === null ? $manager : $manager->get($key, $default);
    }
}
