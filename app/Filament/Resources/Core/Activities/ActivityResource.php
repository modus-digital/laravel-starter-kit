<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Activities;

use App\Enums\RBAC\Permission;
use App\Filament\Resources\Core\Activities\Pages\ListActivities;
use App\Filament\Resources\Core\Activities\Tables\ActivitiesTable;
use App\Models\Activity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

final class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = 'icon-logs';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'monitoring/activities';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::ACCESS_ACTIVITY_LOGS) ?? false;
    }

    public static function canCreate(): bool
    {
        // Activities are auto-generated, not created manually
        return false;
    }

    public static function canEdit($record): bool
    {
        // Activities are read-only
        return false;
    }

    public static function canDelete($record): bool
    {
        // Activities should not be deleted manually
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::ACCESS_ACTIVITY_LOGS) ?? false;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.activities.navigation_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('navigation.groups.logs_and_monitoring');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Activity::count();

        if ($count > 100) {
            return '99+';
        }

        return $count > 0 ? (string) $count : null;
    }

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }
}
