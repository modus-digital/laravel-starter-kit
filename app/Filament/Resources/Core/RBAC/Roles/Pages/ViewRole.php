<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Roles\Pages;

use App\Filament\Resources\Core\RBAC\Roles\RoleResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;
}
