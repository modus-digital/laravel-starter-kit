<?php

namespace App\Filament\Resources\Core\RBAC\Permissions\Pages;

use App\Filament\Resources\Core\RBAC\Permissions\PermissionResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewPermission extends ViewRecord
{
    protected static string $resource = PermissionResource::class;
}
