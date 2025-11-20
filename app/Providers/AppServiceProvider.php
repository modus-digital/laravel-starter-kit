<?php

declare(strict_types=1);

namespace App\Providers;

use App\Translation\NestedJsonLoader;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // This is a workaround to load nested JSON files and add support for nested json using __() and trans() helper functions.
        $this->app->extend(
            abstract: 'translation.loader',
            closure: fn ($loader, $app): NestedJsonLoader => new NestedJsonLoader(
                files: $app['files'],
                path: $app['path.lang']
            )
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
