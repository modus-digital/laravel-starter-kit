<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\RBAC\Permission;
use Illuminate\Contracts\Support\Htmlable;
use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups as BaseBackups;

/**
 * Backups page for managing application backups.
 *
 * This page provides interfaces for:
 * - Viewing existing backups
 * - Creating new backups
 * - Managing backup schedules
 * - Restoring from backups
 *
 * @since 1.0.0
 */
class BackupsPage extends BaseBackups
{
    /**
     * The sort order for navigation items.
     */
    public static ?int $navigationSort = 2;

    /**
     * Determine if the current user can access the backups page.
     *
     * @return bool True if the user has permission to access backups
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo(Permission::CAN_ACCESS_BACKUPS);
    }

    /**
     * Get the URL slug for the backups page.
     *
     * @return string The page slug
     */
    public static function getSlug(): string
    {
        return '/core/backups';
    }

    /**
     * Get the navigation icon for the backups page.
     *
     * @return string|Htmlable|null The heroicon component name
     */
    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'heroicon-o-inbox-arrow-down';
    }

    /**
     * Get the navigation label for the backups page.
     *
     * @return string The navigation label
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.backups');
    }

    /**
     * Get the page heading for the backups page.
     *
     * @return string The page heading
     */
    public function getHeading(): string
    {
        return __('admin.navigation.pages.backups');
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
