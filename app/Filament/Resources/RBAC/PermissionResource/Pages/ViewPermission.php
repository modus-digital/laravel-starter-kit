<?php

namespace App\Filament\Resources\RBAC\PermissionResource\Pages;

use App\Filament\Resources\RBAC\PermissionResource;
use Filament\Resources\Pages\ViewRecord;

/**
 * Page for viewing details of a specific permission.
 */
class ViewPermission extends ViewRecord
{
  /**
   * The resource class this page belongs to.
   *
   * @var string
   */
  protected static string $resource = PermissionResource::class;
}
