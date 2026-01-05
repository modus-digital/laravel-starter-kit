<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RbacStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 3;

    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.widgets.rbac_stats.total_users'), User::count())
                ->description(__('admin.widgets.rbac_stats.users_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make(__('admin.widgets.rbac_stats.total_roles'), Role::count())
                ->description(__('admin.widgets.rbac_stats.roles_description'))
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),
            Stat::make(__('admin.widgets.rbac_stats.total_permissions'), Permission::count())
                ->description(__('admin.widgets.rbac_stats.permissions_description'))
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('warning'),
        ];
    }
}
