<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Resources\Modules\Clients\ClientResource;
use App\Filament\Resources\Modules\SocialiteProviders\SocialiteProviderResource;
use App\Filament\Widgets\ActivityLog;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class ControlPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id(id: 'control')
            ->path(path: 'control')
            ->topbar(false)
            ->viteTheme('resources/css/filament/control/theme.css')
            ->maxContentWidth(Width::Full)
            ->colors(colors: [
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path(path: 'Filament/Resources/Core'), for: 'App\Filament\Resources\Core')
            ->resources(resources: $this->registerResources())
            ->discoverPages(in: app_path(path: 'Filament/Pages'), for: 'App\Filament\Pages')
            ->pages(pages: [
                Dashboard::class,
            ])
            ->widgets(widgets: [
                AccountWidget::class,
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
}
