<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\RBAC\Role as RBACRole;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Spatie\Permission\Models\Role;

final class RolePermissionMatrix extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.widgets.role_permission_matrix.heading'))
            ->description(__('admin.widgets.role_permission_matrix.description'))
            ->query(
                Role::query()->with('permissions')
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.widgets.role_permission_matrix.role'))
                    ->formatStateUsing(function (string $state): string {
                        $enum = RBACRole::tryFrom($state);

                        return $enum?->getLabel() ?? str($state)->headline()->toString();
                    })
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->badge()
                    ->color('info'),
                TextColumn::make('permissions_count')
                    ->label(__('admin.widgets.role_permission_matrix.total_permissions'))
                    ->counts('permissions')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                TextColumn::make('users_count')
                    ->label(__('admin.widgets.role_permission_matrix.users'))
                    ->counts('users')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),
            ])
            ->defaultSort('name')
            ->paginated(false);
    }
}
