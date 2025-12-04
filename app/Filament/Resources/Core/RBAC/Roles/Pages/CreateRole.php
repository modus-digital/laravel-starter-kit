<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Roles\Pages;

use App\Filament\Resources\Core\RBAC\Roles\RoleResource;
use App\Filament\Resources\Core\RBAC\Roles\Schemas\RoleForm;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

final class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(
            auth()->user()->can('create:roles'),
            403,
            'You do not have permission to create roles.'
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['guard_name'] = 'web';

        // Remove permission fields - they're synced in afterCreate
        foreach (RoleForm::PERMISSION_FIELDS as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncPermissions();
        $this->logActivity();
    }

    private function logActivity(): void
    {
        $permissions = $this->record->permissions->pluck('name')->toArray();

        Activity::inLog('administration')
            ->event('rbac.role.created')
            ->causedBy(Auth::user())
            ->performedOn($this->record)
            ->withProperties([
                'role' => [
                    'id' => $this->record->id,
                    'name' => $this->record->name,
                    'permissions_count' => count($permissions),
                ],
                'permissions' => $permissions,
            ])
            ->log('');
    }

    private function syncPermissions(): void
    {
        $state = $this->form->getState();

        $permissions = collect(RoleForm::PERMISSION_FIELDS)
            ->flatMap(fn (string $field): array => $state[$field] ?? [])
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->record->syncPermissions($permissions);
    }
}
