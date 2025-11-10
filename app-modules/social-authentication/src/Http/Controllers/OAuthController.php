<?php

declare(strict_types=1);

namespace ModusDigital\SocialAuthentication\Http\Controllers;

use App\Enums\RBAC\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use ModusDigital\SocialAuthentication\Models\SocialiteProvider;

final class OAuthController
{
    /**
     * Redirect to OAuth provider
     */
    public function redirect(string $provider): RedirectResponse
    {
        $socialiteProvider = SocialiteProvider::where('provider', $provider)
            ->where('is_enabled', true)
            ->whereNotNull('client_id')
            ->whereNotNull('client_secret')
            ->whereNotNull('redirect_uri')
            ->firstOrFail();

        // Dynamically configure the provider
        $this->configureProvider($socialiteProvider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth callback
     */
    public function callback(string $provider, Request $request): RedirectResponse
    {
        $socialiteProvider = SocialiteProvider::where('provider', $provider)
            ->where('is_enabled', true)
            ->whereNotNull('client_id')
            ->whereNotNull('client_secret')
            ->whereNotNull('redirect_uri')
            ->firstOrFail();

        // Dynamically configure the provider
        $this->configureProvider($socialiteProvider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect()->route('login')
                ->with('error', __('social-authentication::social-authentication.auth.failed'));
        }

        // Find or create user
        $user = $this->findOrCreateUser($socialUser, $provider);

        // Log the user in
        Auth::login($user, true);

        return redirect()->intended(route('app.home'));
    }

    /**
     * Configure Socialite provider dynamically
     */
    private function configureProvider(SocialiteProvider $socialiteProvider): void
    {
        $providerKey = is_object($socialiteProvider->provider) && property_exists($socialiteProvider->provider, 'value')
            ? $socialiteProvider->provider->value
            : $socialiteProvider->provider;

        $configKey = "services.{$providerKey}";

        Config::set($configKey, [
            'client_id' => $socialiteProvider->client_id,
            'client_secret' => $socialiteProvider->client_secret,
            'redirect' => $socialiteProvider->redirect_uri,
        ]);
    }

    /**
     * Find or create user from social provider
     */
    private function findOrCreateUser($socialUser, string $provider, array $data = []): User
    {
        // Check if user exists by email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link the provider to existing user if not already set
            if (! $user->provider) {
                $user->update(['provider' => $provider, ...$data]);
            }

            return $user;
        }

        // Create new user with provider information
        $user = User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt(Str::random(32)), // Random password since they use OAuth
            'provider' => $provider,
            'status' => \App\Enums\ActivityStatus::ACTIVE,
        ]);

        $user->assignRole(Role::USER);

        return $user;
    }
}
