<?php

namespace Tests\Feature\Settings\Concerns;

use App\Models\User;
use App\Support\Settings\SettingsManager;
use App\Support\Settings\SettingsRegistry;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Arr;

/**
 * Helpers for the settings feature tests: seeded roles, users per role and a
 * form payload that mirrors what the settings page submits for a group.
 */
trait InteractsWithSettings
{
    protected function seedRoles(): void
    {
        $this->seed(RoleSeeder::class);
    }

    protected function userWithRole(string $role): User
    {
        return User::factory()->{$role}()->create();
    }

    /**
     * The request body the settings form sends for a group with its current values,
     * merged with overrides keyed by setting key (e.g. ['general.site_name' => 'X']).
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function groupPayload(string $group, array $overrides = []): array
    {
        $settings = app(SettingsManager::class);
        $input = [];

        foreach (app(SettingsRegistry::class)->fields($group) as $key => $field) {
            if ($field['type'] === 'image') {
                continue;
            }

            $value = array_key_exists($key, $overrides) ? $overrides[$key] : $settings->saved($key);

            $value = match ($field['type']) {
                'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
                'multiselect', 'repeater', 'list' => $value ?? [],
                default => $value === null ? '' : (string) $value,
            };

            Arr::set($input, $key, $value);
        }

        return [SettingsRegistry::INPUT => $input];
    }

    /**
     * Session data that marks the password as just confirmed ("sudo mode").
     *
     * @return array<string, int>
     */
    protected function passwordConfirmed(): array
    {
        return ['auth.password_confirmed_at' => time()];
    }
}
