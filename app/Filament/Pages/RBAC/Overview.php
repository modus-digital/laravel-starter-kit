<?php

declare(strict_types=1);

namespace App\Filament\Pages\RBAC;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class Overview extends Page
{
    protected static ?string $navigationLabel = 'Access Control';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.rbac.overview';

    protected static ?string $slug = 'rbac';
}
