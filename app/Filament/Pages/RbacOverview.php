<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class RbacOverview extends Page
{
    protected string $view = 'filament.pages.rbac-overview';
    protected static ?string $navigationLabel = 'Access Control';
    protected static ?string $title = 'Roles and permissions';
    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'system/access-control';

    public function getHeading(): string
    {
        return 'Roles and permissions';
    }
}
