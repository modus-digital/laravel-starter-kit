<?php

namespace App\Filament\Resources\Core\RBAC\Permissions\Pages;

use App\Filament\Resources\Core\RBAC\Permissions\PermissionResource;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
