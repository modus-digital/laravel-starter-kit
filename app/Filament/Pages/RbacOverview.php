<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class RbacOverview extends Page
{
    protected string $view = 'filament.pages.rbac-overview';

    protected static ?string $navigationLabel = 'Access Control';

    protected static ?string $title = 'Roles and permissions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'management/access-control';

    public function getHeading(): string
    {
        return 'Roles and permissions';
    }

    public static function getNavigationGroup(): string
    {
        return __('navigation.groups.management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.labels.access_control');
    }
}
