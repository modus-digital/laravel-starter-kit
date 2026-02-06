<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Outerweb\Settings\Facades\Setting;

final class OAuthController extends Controller
{
    public function redirect(string $providerName): RedirectResponse
    {
        if (! $this->isProviderAvailable($providerName)) {
            return redirect()->route('login')
                ->with('error', __('auth.oauth.provider_unavailable', ['provider' => $providerName]));
        }

        $this->configureProvider($providerName);

        return Socialite::driver($providerName)->redirect();
    }

    public function callback(string $providerName): RedirectResponse
    {
        if (! $this->isProviderAvailable($providerName)) {
            return redirect()->route('login')
                ->with('error', __('auth.oauth.provider_unavailable', ['provider' => $providerName]));
        }

        $this->configureProvider($providerName);

        try {
            $socialiteUser = Socialite::driver($providerName)->user();
        } catch (Exception) {
            return redirect()->route('login')
                ->with('error', __('auth.oauth.authentication_failed', ['provider' => ucfirst($providerName)]));
        }

        $user = $this->findOrCreateUser($socialiteUser, $providerName);

        if (! $user instanceof User) {
            return redirect()->route('login')
                ->with('error', __('auth.oauth.registration_disabled'));
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    private function isProviderAvailable(string $providerName): bool
    {
        // Check if provider is enabled in config
        $availableProviders = config('modules.socialite.providers', []);
        if (! isset($availableProviders[$providerName]) || ! $availableProviders[$providerName]) {
            return false;
        }

        // Check if provider is enabled in settings
        $enabled = Setting::get("integrations.oauth.{$providerName}.enabled", false);
        if (! $enabled) {
            return false;
        }

        // Check if provider has required credentials
        $clientId = Setting::get("integrations.oauth.{$providerName}.client_id");
        $clientSecret = Setting::get("integrations.oauth.{$providerName}.client_secret");

        return ! empty($clientId) && ! empty($clientSecret);
    }

    private function findOrCreateUser(SocialiteUser $socialiteUser, string $providerName): ?User
    {
        $user = User::where('email', $socialiteUser->getEmail())->first();

        if ($user) {
            $this->linkProviderToUser($user, $providerName);

            return $user;
        }

        return $this->createUserFromSocialite($socialiteUser, $providerName);
    }

    private function createUserFromSocialite(SocialiteUser $socialiteUser, string $providerName): ?User
    {
        if (! config('modules.registration.enabled', false)) {
            return null;
        }

        $user = User::create([
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'password' => Hash::make(Str::random(32)),
            'provider' => $providerName,
            'status' => ActivityStatus::ACTIVE,
        ]);

        $user->assignRole(Role::USER);
        $user->markEmailAsVerified();

        return $user;
    }

    private function linkProviderToUser(User $user, string $providerName): void
    {
        if ($user->provider !== $providerName) {
            $user->update(['provider' => $providerName]);
        }
    }

    private function configureProvider(string $providerName): void
    {
        $clientId = Setting::get("integrations.oauth.{$providerName}.client_id");
        $clientSecret = Setting::get("integrations.oauth.{$providerName}.client_secret");

        // Decrypt the client secret
        try {
            $clientSecret = decrypt($clientSecret);
        } catch (Exception) {
            // If decryption fails, use the value as-is (backward compatibility)
        }

        Config::set("services.{$providerName}", [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect' => route('oauth.callback', $providerName),
        ]);
    }
}
