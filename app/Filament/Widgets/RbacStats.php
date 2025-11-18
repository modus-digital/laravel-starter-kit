<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RbacStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 3;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Active users in the system')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total Roles', Role::count())
                ->description('Defined roles')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),
            Stat::make('Total Permissions', Permission::count())
                ->description('Available permissions')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('warning'),
        ];
    }
}
