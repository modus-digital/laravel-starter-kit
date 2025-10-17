<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
            ->id('control')
            ->path('control')
            ->viteTheme('resources/css/filament/control/theme.css')
            ->colors(['primary' => Color::Amber])
            ->discoverResources(...$this->discover('resources'))
            ->discoverPages(...$this->discover('pages'))
            ->discoverWidgets(...$this->discover('widgets'))
            ->pages($this->getPages())
            ->widgets($this->getWidgets())
            ->middleware($this->getMiddleware())
            ->authMiddleware($this->getAuthMiddleware());
    }

    private function discover(string $type): array
    {
        return match ($type) {
            'resources' => [app_path('Filament/Resources'), 'App\Filament\Resources'],
            'pages' => [app_path('Filament/Pages'), 'App\Filament\Pages'],
            'widgets' => [app_path('Filament/Widgets'), 'App\Filament\Widgets'],
        };
    }

    private function getPages(): array
    {
        return [
            Dashboard::class,
        ];
    }

    private function getWidgets(): array
    {
        return [
            AccountWidget::class,
            FilamentInfoWidget::class,
        ];
    }

    private function getMiddleware(): array
    {
        return [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ];
    }

    private function getAuthMiddleware(): array
    {
        return [
            Authenticate::class,
        ];
    }
}
