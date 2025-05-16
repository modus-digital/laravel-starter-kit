<?php

namespace App\Http\Middleware;

use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\UserSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class TwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request):Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypassTwoFactor($request)) {
            return $next($request);
        }

        if ($this->isTwoFactorRoute($request)) {
            return $next($request);
        }

        if ($this->isTwoFactorEnabled() === false) {
            return $next($request);
        }

        return to_route('auth.two-factor.verify');
    }

    private function getTwoFactorSettings(User $user): ?array
    {
        $twoFactorSettings = $user->settings()->where('key', UserSettings::SECURITY)->first();

        if (! $twoFactorSettings) {
            return null;
        }

        return $twoFactorSettings->retrieve(UserSettings::SECURITY, 'two_factor');
    }

    private function isTwoFactorRoute(Request $request): bool
    {
        if ($request->routeIs('auth.two-factor.verify')) {
            return true;
        }

        return $request->routeIs('auth.two-factor.recovery.*');
    }

    private function isTwoFactorEnabled(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $settings = $this->getTwoFactorSettings(user: $user);
        if (! $settings) {
            return false;
        }

        return $settings['status'] === TwoFactor::ENABLED || $settings['status'] === TwoFactor::ENABLED->value;
    }

    private function shouldBypassTwoFactor(Request $request): bool
    {
        // Check if any bypass conditions are met
        $isPostMethod = $request->isMethod('POST');
        $isNotAuthenticated = ! auth()->check();
        $canBypassTwoFactor = session()->has('can_bypass_two_factor');
        $isImpersonating = session()->has('impersonate');
        $isTwoFactorVerified = session()->has('two_factor_verified');

        return $isPostMethod || $isNotAuthenticated || $canBypassTwoFactor || $isImpersonating || $isTwoFactorVerified;
    }
}
