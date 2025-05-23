<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Folio\Folio;
use Override;

class FolioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Authentication Pages (for middleware reasons separated from application pages)
        Folio::path(resource_path('views/pages/authentication'))
            ->uri('/auth')
            ->middleware(['*' => []]);

        // Application Pages
        Folio::path(resource_path('views/pages/application'))
            ->middleware(['*' => ['auth', 'verified']]);
    }
}
