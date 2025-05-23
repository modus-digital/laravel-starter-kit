<?php

namespace App\Filament\Pages;

use App\Enums\RBAC\Permission;
use Illuminate\Contracts\Support\Htmlable;
use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups as BaseBackups;

class BackupsPage extends BaseBackups
{
    public static ?int $navigationSort = 2;

    /**
     * Determine if the user can access the backups page.
     *
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo(Permission::CAN_ACCESS_BACKUPS);
    }

    /**
     * Get the slug for the backups page.
     *
     * @return string
     */
    public static function getSlug(): string
    {
        return '/core/backups';
    }

    /**
     * Get the navigation icon for the backups page.
     * This is based on heroicon components
     *
     * @return string|Htmlable|null
     */
    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'heroicon-o-inbox-arrow-down';
    }

    /**
     * Get the navigation label for the backups page.
     *
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.backups');
    }

    /**
     * Get the heading for the backups page.
     *
     * @return string
     */
    public function getHeading(): string
    {
        return __('admin.navigation.pages.backups');
    }

    /**
     * Get the navigation group for the backups page.
     *
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.applicatie-info');
    }
}
