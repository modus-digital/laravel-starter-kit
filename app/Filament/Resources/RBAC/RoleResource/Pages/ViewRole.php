<?php

namespace App\Filament\Resources\RBAC\RoleResource\Pages;

use App\Filament\Resources\RBAC\RoleResource;
use Filament\Resources\Pages\ViewRecord;

/**
 * Page for viewing details of a specific role.
 */
class ViewRole extends ViewRecord
{
  /**
   * The resource class this page belongs to.
   *
   * @var string
   */
  protected static string $resource = RoleResource::class;
}
