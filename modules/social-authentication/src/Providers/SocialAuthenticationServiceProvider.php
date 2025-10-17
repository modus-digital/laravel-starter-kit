<?php

declare(strict_types=1);

namespace ModusDigital\SocialAuthentication\Providers;

use App\Enums\Hooks;
use App\Services\HookManager;
use Filament\Panel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use ModusDigital\SocialAuthentication\Livewire\SocialAuthButtons;
use ModusDigital\SocialAuthentication\SocialAuthenticationPlugin;

final class SocialAuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(
            modifyUsing: fn (Panel $panel) => (
                $panel->getId() !== 'control' || $panel->plugin(new SocialAuthenticationPlugin())
            )
        );
    }

    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'social-authentication');

        // Register routes
        $this->registerRoutes();

        // Register Livewire component
        Livewire::component('social-auth-buttons', SocialAuthButtons::class);

        // Register hook for social auth buttons
        $this->registerHooks();

        // Publish and register seeders
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../database/seeders/' => database_path('seeders'),
            ], 'social-authentication-seeders');
        }
    }

    private function registerRoutes(): void
    {
        Route::middleware(['web', 'guest'])->group(function () {
            Route::get('/auth/{provider}/redirect', [
                \ModusDigital\SocialAuthentication\Http\Controllers\OAuthController::class,
                'redirect',
            ])->name('auth.socialite.redirect');

            Route::get('/auth/{provider}/callback', [
                \ModusDigital\SocialAuthentication\Http\Controllers\OAuthController::class,
                'callback',
            ])->name('auth.socialite.callback');
        });
    }

    private function registerHooks(): void
    {
        $hookManager = app(HookManager::class);

        $hookManager->register(
            Hooks::AUTH_LOGIN_FORM_BEFORE,
            fn () => view('social-authentication::livewire.social-auth-buttons-wrapper')
        );
    }
}
