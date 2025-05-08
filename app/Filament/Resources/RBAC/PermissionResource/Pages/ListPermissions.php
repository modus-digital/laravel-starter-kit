<?php

namespace App\Filament\Resources\RBAC\PermissionResource\Pages;

use App\Filament\Resources\RBAC\PermissionResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Page for listing all permissions in the system.
 */
class ListPermissions extends ListRecords
{
  /**
   * The resource class this page belongs to.
   *
   * @var string
   */
  protected static string $resource = PermissionResource::class;
}
