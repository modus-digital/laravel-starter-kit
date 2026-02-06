<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\Security\TwoFactorStatusChanged;
use App\Http\Responses\FortifyLoginResponse;
use App\Http\Responses\FortifyLogoutResponse;
use App\Http\Responses\FortifyRegisterResponse;
use App\Traits\ConfiguresScribeDocumentation;
use App\Translation\NestedJsonLoader;
use App\View\Composers\AppViewComposer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\Http\Responses\LoginResponse as FortifyLoginResponseContract;
use Laravel\Fortify\Http\Responses\LogoutResponse as FortifyLogoutResponseContract;
use Laravel\Fortify\Http\Responses\RegisterResponse as FortifyRegisterResponseContract;

final class AppServiceProvider extends ServiceProvider
{
    use ConfiguresScribeDocumentation;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configureCustomLoaders();
        $this->configureResponses();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('app', AppViewComposer::class);
        $this->configureScribe();
        $this->configureEventListeners();
    }

    /**
     * Configure event listeners for security events.
     */
    private function configureEventListeners(): void
    {
        // Fortify 2FA events - these need manual registration
        Event::listen(TwoFactorAuthenticationEnabled::class, function (TwoFactorAuthenticationEnabled $event): void {
            Event::dispatch(new TwoFactorStatusChanged(
                user: $event->user,
                enabled: true,
                ipAddress: request()->ip(),
                userAgent: request()->userAgent(),
            ));
        });

        Event::listen(TwoFactorAuthenticationDisabled::class, function (TwoFactorAuthenticationDisabled $event): void {
            Event::dispatch(new TwoFactorStatusChanged(
                user: $event->user,
                enabled: false,
                ipAddress: request()->ip(),
                userAgent: request()->userAgent(),
            ));
        });

        // Note: Task, Comment, and Security event listeners are auto-discovered by Laravel
        // based on their naming convention (e.g., SendTaskAssignedNotification handles TaskAssigned)
    }

    /**
     * Configure the responses of Fortify.
     */
    private function configureResponses(): void
    {
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
