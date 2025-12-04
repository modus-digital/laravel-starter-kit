<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\RBAC\Roles\Pages;

use App\Enums\RBAC\Role as RBACRole;
use App\Filament\Resources\Core\RBAC\Roles\RoleResource;
use App\Filament\Resources\Core\RBAC\Roles\Schemas\RoleForm;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

final class EditRole extends EditRecord
{
    public array $originalData = [];

    protected static string $resource = RoleResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(
            auth()->user()->can('update:roles'),
            403,
            'You do not have permission to edit roles.'
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Store original data for comparison in activity log
        $this->originalData = [
            'name' => $data['name'] ?? null,
            'permissions' => $this->record->permissions->pluck('name')->toArray(),
        ];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->isSystemRole($this->record)) {
            Notification::make()
                ->title('Cannot edit system role')
                ->body('System roles cannot be edited. They are managed by the application.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        }

        // Remove permission fields - they're synced in afterSave
        foreach (RoleForm::PERMISSION_FIELDS as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if (! $this->isSystemRole($this->record)) {
            $this->syncPermissions();
            $this->logActivity();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => ! $this->isSystemRole($this->record))
                ->requiresConfirmation()
                ->modalHeading('Delete Role')
                ->modalDescription('Are you sure you want to delete this role? This action cannot be undone.')
                ->modalSubmitActionLabel('Delete Role')
                ->after(function (): void {
                    Activity::inLog('administration')
                        ->event('rbac.role.deleted')
                        ->causedBy(Auth::user())
                        ->performedOn($this->record)
                        ->withProperties([
                            'role' => [
                                'id' => $this->record->id,
                                'name' => $this->record->name,
                                'permissions_count' => $this->record->permissions->count(),
                            ],
                            'permissions' => $this->record->permissions->pluck('name')->toArray(),
                        ])
                        ->log('');
                }),
        ];
    }

    private function logActivity(): void
    {
        $changes = [];

        // Check if name changed
        if ($this->originalData['name'] !== $this->record->name) {
            $changes['name'] = [
                'old' => $this->originalData['name'],
                'new' => $this->record->name,
            ];
        }

        // Check if permissions changed
        $newPermissions = $this->record->permissions->pluck('name')->toArray();
        $addedPermissions = array_diff($newPermissions, $this->originalData['permissions']);
        $removedPermissions = array_diff($this->originalData['permissions'], $newPermissions);

        if ($addedPermissions !== [] || $removedPermissions !== []) {
            $changes['permissions'] = [
                'added' => array_values($addedPermissions),
                'removed' => array_values($removedPermissions),
            ];
        }

        // Only log if there are changes
        if ($changes !== []) {
            Activity::inLog('administration')
                ->event('rbac.role.updated')
                ->causedBy(Auth::user())
                ->performedOn($this->record)
                ->withProperties([
                    'role' => [
                        'id' => $this->record->id,
                        'name' => $this->record->name,
                        'permissions_count' => count($newPermissions),
                    ],
                    'changes' => $changes,
                ])
                ->log('');
        }
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

    private function isSystemRole(Model $record): bool
    {
        return collect(RBACRole::cases())
            ->contains(fn (RBACRole $enum): bool => $enum->value === $record->name);
    }
}
