<?php

namespace App\Filament\Resources\RBAC\RoleResource\Pages;

use App\Filament\Resources\RBAC\RoleResource;
use Filament\Resources\Pages\ListRecords;

// Page for listing all roles in the system.
class ListRoles extends ListRecords
{
    // The resource class this page belongs to.
    protected static string $resource = RoleResource::class;
}
