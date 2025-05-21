<?php

namespace App\Providers\Filament;

use App\Filament\Pages\BackupsPage;
use App\Filament\Pages\HealthChecksResultsPage;
use App\Filament\Pages\Settings;
use App\Http\Middleware\Filament\ApplyUserTheme;
use App\Http\Middleware\Filament\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Kenepa\TranslationManager\TranslationManagerPlugin;
use Outerweb\FilamentSettings\Filament\Plugins\FilamentSettingsPlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
use Vormkracht10\FilamentMails\Facades\FilamentMails;
use Vormkracht10\FilamentMails\FilamentMailsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $applicationName = setting('general.app_name');

        return $panel
            ->default()
            ->id('admin')
            ->darkMode(false, false)
            ->path('admin')
            ->brandName($applicationName)
            ->colors(['primary' => Color::Blue])
            ->routes(fn() => FilamentMails::routes())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([AccountWidget::class, FilamentInfoWidget::class])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                ApplyUserTheme::class,
            ])
            ->plugins([
                FilamentSpatieLaravelHealthPlugin::make()->usingPage(HealthChecksResultsPage::class),
                FilamentSpatieLaravelBackupPlugin::make()->usingPage(BackupsPage::class),
                TranslationManagerPlugin::make(),
                FilamentMailsPlugin::make(),
                FilamentSettingsPlugin::make()->pages([Settings::class])
            ])
            ->navigationGroups([
                NavigationGroup::make()->label(__('admin.navigation.groups.beheer')),
                NavigationGroup::make()->label(__('admin.navigation.groups.toegangsbeheer')),
                NavigationGroup::make()->label(__('admin.navigation.groups.applicatie-info')),
            ])
            ->renderHook(
                name: PanelsRenderHook::SIDEBAR_FOOTER,
                hook: fn() => Blade::render('<x-layouts.navigation.filament-back />')
            );
    }
}
