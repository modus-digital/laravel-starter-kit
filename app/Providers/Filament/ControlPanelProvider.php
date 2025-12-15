<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Branding;
use App\Filament\Resources\Modules\Clients\ClientResource;
use App\Filament\Resources\Modules\SocialiteProviders\SocialiteProviderResource;
use App\Filament\Widgets\ActivityLog;
use App\Filament\Widgets\ImpersonationWidget;
use App\Services\BrandingService;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Outerweb\FilamentSettings\SettingsPlugin;
use Throwable;

final class ControlPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $fontMap = [
            'inter' => 'Inter',
            'roboto' => 'Roboto',
            'poppins' => 'Poppins',
            'lato' => 'Lato',
            'inria_serif' => 'Inria Serif',
            'arvo' => 'Arvo',
        ];

        // Get font family with fallback
        try {
            $brandingService = app(BrandingService::class);
            $settings = $brandingService->getSettings();
            $fontFamily = $fontMap[$settings['font'] ?? 'inter'] ?? 'Inter';
        } catch (Throwable) {
            $fontFamily = 'Inter';
        }

        return $panel
            ->default()
            ->id(id: 'control')
            ->path(path: 'control')
            ->topbar(false)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->brandLogo(fn (): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View => view('filament.application-logo'))
            ->font($fontFamily)
            ->viteTheme('resources/css/filament/control/theme.css')
            ->maxContentWidth(Width::Full)
            ->colors(colors: fn (): array => [
                'primary' => $this->getPrimaryColorPalette(),
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_START,
                fn (): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View => view('filament.branding-styles')
            )
            ->discoverResources(in: app_path(path: 'Filament/Resources/Core'), for: 'App\Filament\Resources\Core')
            ->resources(resources: $this->registerResources())
            ->discoverPages(in: app_path(path: 'Filament/Pages'), for: 'App\Filament\Pages')
            ->pages(pages: [
                Dashboard::class,
            ])
            ->widgets(widgets: [
                ImpersonationWidget::class,
                ActivityLog::class,
            ])
            ->middleware(middleware: [
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                SettingsPlugin::make()
                    ->pages([
                        Branding::class,
                    ]),
            ])
            ->userMenuItems([
                Action::make('settings')
                    ->label((string) __('navigation.labels.update_profile'))
                    ->url('/settings/profile')
                    ->icon(Heroicon::PencilSquare),

                'logout' => fn (Action $action): Action => $action
                    ->label(session()->has('impersonation') ? (string) __('navigation.labels.leave_impersonation') : $action->getLabel())
                    ->url(session()->has('impersonation') ? route('impersonate.leave') : $action->getUrl())
                    ->postToUrl(session()->has('impersonation'))
                    ->icon(session()->has('impersonation') ? Heroicon::ArrowLeftEndOnRectangle : $action->getIcon()),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * @return array<int, class-string>
     */
    private function registerResources(): array
    {
        $resources = [];

        if (config(key: 'modules.clients.enabled', default: false)) {
            $resources[] = ClientResource::class;
        }

        if (config(key: 'modules.socialite.enabled', default: false)) {
            $resources[] = SocialiteProviderResource::class;
        }

        return $resources;
    }

    /**
     * Get the primary color palette with fallback
     *
     * @return array<int, string>
     */
    private function getPrimaryColorPalette(): array
    {
        try {
            return app(BrandingService::class)->getFilamentPrimaryColorPalette();
        } catch (Throwable) {
            // Return default Filament primary color palette if settings not available
            return [
                50 => 'oklch(0.985 0 0)',
                100 => 'oklch(0.97 0.013 47.604)',
                200 => 'oklch(0.94 0.026 47.604)',
                300 => 'oklch(0.88 0.053 47.604)',
                400 => 'oklch(0.8 0.106 47.604)',
                500 => 'oklch(0.705 0.213 47.604)',
                600 => 'oklch(0.63 0.239 47.604)',
                700 => 'oklch(0.51 0.213 47.604)',
                800 => 'oklch(0.43 0.186 47.604)',
                900 => 'oklch(0.36 0.159 47.604)',
                950 => 'oklch(0.205 0.106 47.604)',
            ];
        }
    }
}
