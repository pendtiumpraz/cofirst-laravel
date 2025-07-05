<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register CertificateService
        $this->app->singleton(\App\Services\CertificateService::class, function ($app) {
            return new \App\Services\CertificateService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Tailwind CSS pagination views by default
        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');
        
        // Manually register AppLayout component
        Blade::component('app-layout', \App\View\Components\AppLayout::class);
        Blade::component('app-layout-simple', \App\View\Components\AppLayoutSimple::class);
    }
}
