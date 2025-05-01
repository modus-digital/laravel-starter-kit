<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dangerous commands are prohibited in production
        DB::prohibitDestructiveCommands(prohibit: app()->isProduction());

        // Models
        Model::unguard(); // Disable model guards
        Model::shouldBeStrict(); // Throw exceptions when accessing non-existent properties
        Model::preventLazyLoading(); // Throw exceptions when lazy loading is used
        Model::automaticallyEagerLoadRelationships(); // Resolve n+1 issues

        // Auto HTTPS Scheme + Vite prefetch strategy
        URL::forceScheme('https');
        Vite::usePrefetchStrategy('aggressive');
    }
}
