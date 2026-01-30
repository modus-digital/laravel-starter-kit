<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Enums\AuthenticationProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Outerweb\Settings\Facades\Setting;

final class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        /**
         * Array of enabled auth providers for the login view.
         * Structure: [{ id: string, name: string }, ...]
         */
        Fortify::loginView(fn (Request $request) => Inertia::render('core/auth/login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'canRegister' => Features::enabled(Features::registration()),
            'status' => $request->session()->get('status'),
            'authProviders' => $this->configureAllowedAuthProviders(),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('core/auth/reset-password', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('core/auth/forgot-password', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('core/auth/verify-email', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::registerView(fn () => Inertia::render('core/auth/register'));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('core/auth/two-factor-challenge'));

        Fortify::confirmPasswordView(fn () => Inertia::render('core/auth/confirm-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }

    /**
     * Configure the allowed auth providers for the login view.
     */
    private function configureAllowedAuthProviders(): array
    {
        $settings = Setting::get('integrations.oauth', []);
        $providers = [];

        if ($settings['google']['enabled']) {
            $providers[] = [
                'id' => AuthenticationProvider::GOOGLE,
                'name' => AuthenticationProvider::GOOGLE->value,
            ];
        }

        if ($settings['github']['enabled']) {
            $providers[] = [
                'id' => AuthenticationProvider::GITHUB,
                'name' => AuthenticationProvider::GITHUB->value,
            ];
        }

        if ($settings['microsoft']['enabled']) {
            $providers[] = [
                'id' => AuthenticationProvider::MICROSOFT,
                'name' => AuthenticationProvider::MICROSOFT->value,
            ];
        }

        return $providers;
    }
}
