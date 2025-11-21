<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Role;
use App\Http\Controllers\Controller;
use App\Models\Modules\SocialiteProvider;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

final class OAuthController extends Controller
{
    public function redirect(string $providerName): RedirectResponse|SymfonyRedirectResponse
    {
        $provider = $this->findProvider($providerName);

        if (! $provider instanceof SocialiteProvider) {
            return redirect()->route('login')
                ->with('error', __('auth.oauth.provider_unavailable', ['provider' => $providerName]));
        }

        $this->configureProvider($provider);

        return Socialite::driver($provider->name)->redirect();
    }

    public function callback(string $providerName): RedirectResponse
    {
        $provider = $this->findProvider($providerName);

        if (! $provider instanceof SocialiteProvider) {
            return redirect()->route('login')
                ->with('error', __('auth.oauth.provider_unavailable', ['provider' => $providerName]));
        }

        $this->configureProvider($provider);

        try {
            $socialiteUser = Socialite::driver($provider->name)->user();
        } catch (Exception) {
            return redirect()->route('login')
                ->with('error', __('auth.oauth.authentication_failed', ['provider' => ucfirst($provider->name)]));
        }

        $user = $this->findOrCreateUser($socialiteUser, $provider->name);

        if (! $user instanceof User) {
            return redirect()->route('login')
                ->with('error', __('auth.oauth.registration_disabled'));
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    private function findProvider(string $name): ?SocialiteProvider
    {
        return SocialiteProvider::where('name', $name)
            ->enabled()
            ->whereNotNull('client_id')
            ->whereNotNull('client_secret')
            ->first();
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

    private function configureProvider(SocialiteProvider $provider): void
    {
        Config::set("services.{$provider->name}", [
            'client_id' => $provider->client_id,
            'client_secret' => $provider->client_secret,
            'redirect' => $provider->redirect_uri,
        ]);
    }
}
