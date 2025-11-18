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
            ->heading('Roles & Permissions Matrix')
            ->description('Overview of which roles have which permissions')
            ->query(
                Role::query()->with('permissions')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->formatStateUsing(fn (string $state): string => RBACRole::from($state)->getLabel())
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->badge()
                    ->color('info'),
                TextColumn::make('permissions_count')
                    ->label('Total Permissions')
                    ->counts('permissions')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                TextColumn::make('users_count')
                    ->label('Users')
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
