<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Settings\RuntimeConfig;
use App\Support\Settings\SettingsForm;
use App\Support\Settings\SettingsManager;
use App\Support\Settings\SettingsRegistry;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Settings module: registers the settings services, applies saved settings to
 * the framework config at boot (app name, timezone, locale, mail sender,
 * session lifetime, password policy) and defines the settings gates.
 *
 * Works before migrations: SettingsManager falls back to the defaults of
 * config/settings.php when the settings table (or the cache table) is missing.
 */
class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register the settings services.
     */
    public function register(): void
    {
        // Reason: AppServiceProvider may already bind the manager; never replace a live binding.
        $this->app->singletonIf(SettingsManager::class, fn ($app) => new SettingsManager($app['cache']->store()));
        $this->app->singleton(SettingsRegistry::class);
        $this->app->singleton(SettingsForm::class);
    }

    /**
     * Apply runtime configuration and define authorization gates.
     */
    public function boot(): void
    {
        RuntimeConfig::apply($this->app, $this->app->make(SettingsManager::class));

        Gate::define('settings.view', fn (User $user) => $this->app->make(SettingsRegistry::class)->canView($user));
        Gate::define('settings.update', fn (User $user, string $group) => $this->app->make(SettingsRegistry::class)->canEdit($user, $group));
    }
}
