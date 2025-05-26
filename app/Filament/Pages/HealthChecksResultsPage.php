<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\RBAC\Permission;
use ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults as BaseHealthCheckResults;

/**
 * Health checks results page for monitoring application health.
 *
 * This page provides interfaces for:
 * - Viewing health check results
 * - Monitoring system status
 * - Checking service availability
 * - Application diagnostics
 *
 * @since 1.0.0
 */
class HealthChecksResultsPage extends BaseHealthCheckResults
{
    /**
     * The sort order for navigation items.
     */
    public static ?int $navigationSort = 1;

    /**
     * Determine if the current user can access the health checks results page.
     *
     * @return bool True if the user has permission to access health checks
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo(Permission::CAN_ACCESS_HEALTH_CHECKS);
    }

    /**
     * Get the URL slug for the health checks results page.
     *
     * @return string The page slug
     */
    public static function getSlug(): string
    {
        return '/core/health-checks';
    }

    /**
     * Get the navigation label for the health checks results page.
     *
     * @return string The navigation label
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.health');
    }

    /**
     * Get the page heading for the health checks results page.
     *
     * @return string The page heading
     */
    public function getHeading(): string
    {
        return __('admin.navigation.pages.health');
    }

    /**
     * Get the navigation group this page belongs to.
     *
     * @return string|null The navigation group name
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.applicatie-info');
    }
}
