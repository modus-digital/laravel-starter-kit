<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\RBAC\Permission;
use App\Services\BrandingService;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $brandingService = app(BrandingService::class);
        $branding = $brandingService->getSettings();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'unreadNotificationsCount' => fn (): int => (int) ($request->user()?->unreadNotifications()->count() ?? 0),
            'locale' => app()->getLocale(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',

            // Manage global layout permissions
            'permissions' => [
                'canAccessControlPanel' => $request->user()?->hasPermissionTo(Permission::ACCESS_CONTROL_PANEL) ?? false,
                'canManageApiTokens' => $request->user()?->hasPermissionTo(Permission::HAS_API_ACCESS) ?? false,
            ],

            // Check if the user is impersonating another user
            'isImpersonating' => $request->session()->has('impersonation'),

            // Pass through the branding settings
            'branding' => [
                'logo' => $branding['logo'],
                'primaryColor' => $branding['primary_color'],
                'secondaryColor' => $branding['secondary_color'],
                'font' => $branding['font'],
            ],

            // This is used to pass data from the controller to the view after a redirect
            'data' => fn (): array => $request->session()->get('data', []),
        ];
    }
}
