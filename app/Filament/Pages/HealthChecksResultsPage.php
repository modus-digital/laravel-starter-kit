<?php

namespace App\Filament\Pages;

use Override;
use ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults as BaseHealthCheckResults;

class HealthChecksResultsPage extends BaseHealthCheckResults
{
    #[Override]
    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function getSlug(): string
    {
        return '/core/health-checks';
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return 'Gezondheidschecks';
    }

    #[Override]
    public function getHeading(): string
    {
        return 'Gezondheidschecks';
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return 'Applicatie-info';
    }
}
