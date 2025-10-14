<?php

declare(strict_types=1);

namespace App\Filament\Resources\RBAC\Roles\Pages;

use App\Filament\Resources\RBAC\Roles\RoleResource;
use Filament\Resources\Pages\ListRecords;

final class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
