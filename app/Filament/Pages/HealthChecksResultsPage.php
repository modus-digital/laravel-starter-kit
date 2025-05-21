<?php

namespace App\Filament\Pages;

use Override;
use ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults as BaseHealthCheckResults;

class HealthChecksResultsPage extends BaseHealthCheckResults
{
    public static ?int $navigationSort = 1;

    public static function getSlug(): string
    {
        return '/core/health-checks';
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.health');
    }

    #[Override]
    public function getHeading(): string
    {
        return __('admin.navigation.pages.health');
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.applicatie-info');
    }
}
