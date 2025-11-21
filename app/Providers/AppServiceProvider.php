<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Responses\FilamentLogoutResponse;
use App\Http\Responses\FortifyLoginResponse;
use App\Http\Responses\FortifyLogoutResponse;
use App\Http\Responses\FortifyRegisterResponse;
use App\Translation\NestedJsonLoader;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as FilamentLogoutResponseContract;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Http\Responses\LoginResponse as FortifyLoginResponseContract;
use Laravel\Fortify\Http\Responses\LogoutResponse as FortifyLogoutResponseContract;
use Laravel\Fortify\Http\Responses\RegisterResponse as FortifyRegisterResponseContract;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configureCustomLoaders();
        $this->configureResponses();
    }

    /**
     * Configure the responses of Filament and Fortify.
     */
    private function configureResponses(): void
    {
        // Filament
        $this->app->singleton(abstract: FilamentLogoutResponseContract::class, concrete: FilamentLogoutResponse::class);

        // Fortify
        $this->app->singleton(abstract: FortifyLoginResponseContract::class, concrete: FortifyLoginResponse::class);
        $this->app->singleton(abstract: FortifyRegisterResponseContract::class, concrete: FortifyRegisterResponse::class);
        $this->app->singleton(abstract: FortifyLogoutResponseContract::class, concrete: FortifyLogoutResponse::class);
    }

    /**
     * Configure the custom loaders for the application.
     */
    private function configureCustomLoaders(): void
    {
        $this->app->extend(
            abstract: 'translation.loader',
            closure: fn ($loader, $app): NestedJsonLoader => new NestedJsonLoader(
                files: $app['files'],
                path: $app['path.lang']
            )
        );
    }
}
