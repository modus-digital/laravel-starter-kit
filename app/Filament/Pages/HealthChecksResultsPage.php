<?php

namespace App\Filament\Pages;

use App\Enums\RBAC\Permission;
use ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults as BaseHealthCheckResults;

class HealthChecksResultsPage extends BaseHealthCheckResults
{
    public static ?int $navigationSort = 1;

    /**
     * Determine if the user can access the health checks results page.
     *
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo(Permission::CAN_ACCESS_HEALTH_CHECKS);
    }

    /**
     * Get the slug for the health checks results page.
     *
     * @return string
     */
    public static function getSlug(): string
    {
        return '/core/health-checks';
    }

    /**
     * Get the navigation label for the health checks results page.
     *
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.health');
    }

    /**
     * Get the heading for the health checks results page.
     *
     * @return string
     */
    public function getHeading(): string
    {
        return __('admin.navigation.pages.health');
    }

    /**
     * Get the navigation group for the health checks results page.
     *
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.applicatie-info');
    }
}
