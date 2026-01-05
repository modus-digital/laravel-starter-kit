<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\RBAC\Permission;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

final class RbacOverview extends Page
{
    protected string $view = 'filament.pages.rbac-overview';

    protected static ?string $navigationLabel = 'Access Control';

    protected static ?string $title = 'Roles and permissions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'management/access-control';

    public static function canAccess(): bool
    {
        // Can access if user can read roles or permissions
        return auth()->user()?->hasPermissionTo(Permission::READ_ROLES) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::READ_ROLES) ?? false;
    }

    public static function getNavigationGroup(): string
    {
        return __('navigation.groups.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.labels.access_control');
    }

    public function getHeading(): string
    {
        return __('navigation.labels.access_control');
    }
}
