<?php

declare(strict_types=1);

namespace App\Filament\Resources\RBAC\Permissions\Pages;

use App\Filament\Resources\RBAC\Permissions\PermissionResource;
use Filament\Resources\Pages\ListRecords;

final class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
