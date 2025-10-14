<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\UserSettings;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypassTwoFactor($request)) {
            return $next($request);
        }

        if ($this->isTwoFactorRoute($request)) {
            return $next($request);
        }

        if ($this->isTwoFactorEnabled($request) === false) {
            return $next($request);
        }

        return redirect()->route('two-factor.verify');
    }

    /* ------------------------------------------------------------- */
    /* Two Factor Checks */
    /* ------------------------------------------------------------- */

    private function shouldBypassTwoFactor(Request $request): bool
    {
        // Allow specific POST routes (logout, 2FA verification)
        $allowedPostRoutes = ['auth.logout', 'two-factor.verify', 'two-factor.recover', 'impersonate.leave'];
        $isAllowedPostRoute = $request->isMethod('POST') && $request->routeIs($allowedPostRoutes);

        $isNotAuthenticated = ! $request->user();
        $canBypassTwoFactor = $request->session()->has('can_bypass_two_factor');
        $isImpersonating = $request->session()->has('impersonate');
        $isTwoFactorVerified = $request->session()->has('two_factor_verified');

        return $isAllowedPostRoute || $isNotAuthenticated || $canBypassTwoFactor || $isImpersonating || $isTwoFactorVerified;
    }

    private function isTwoFactorRoute(Request $request): bool
    {
        return $request->routeIs(
            'two-factor.verify',
            'two-factor.recover',
            'verification.notice',
            'verification.verify'
        );
    }

    private function isTwoFactorEnabled(Request $request): bool
    {
        if (! $request->user()) {
            return false;
        }

        $settings = $this->getTwoFactorSettings($request->user());

        if ($settings === null) {
            return false;
        }

        return $settings['status'] === TwoFactor::ENABLED->value || $settings['status'] === TwoFactor::ENABLED;
    }

    /* ------------------------------------------------------------- */
    /* Helper methods */
    /* ------------------------------------------------------------- */
    /**
     * @return array<string, mixed>|null
     */
    private function getTwoFactorSettings(User $user): ?array
    {
        $twoFactorSettings = $user->settings()->where('key', UserSettings::SECURITY)->first();

        if ($twoFactorSettings === null) {
            return null;
        }

        return $twoFactorSettings->retrieve(UserSettings::SECURITY, 'two_factor');
    }
}
