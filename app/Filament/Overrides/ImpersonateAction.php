<?php

declare(strict_types=1);

namespace App\Filament\Overrides;

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Permission;
use App\Enums\RBAC\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

final class ImpersonateAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $impersonatableRoles = array_map(
            callback: fn (Role $role) => $role->value,
            array: array_filter(
                array: Role::cases(),
                callback: fn (Role $role): bool => $role !== Role::ADMIN && $role !== Role::SUPER_ADMIN
            )
        );

        $this->icon(Heroicon::ArrowLeftEndOnRectangle);
        $this->label(__('admin.users.table.impersonate.label'));
        $this->name('impersonate-action');
        $this->color(function (?User $record) use ($impersonatableRoles): array {
            /** @var User|null $currentUser */
            $currentUser = Auth::user();

            if (! $currentUser || ! $record || $record->id === $currentUser->id || ! $currentUser->hasPermissionTo(Permission::IMPERSONATE_USERS) || $record->status === ActivityStatus::INACTIVE) {
                return Color::Gray;
            }

            /** @var \Spatie\Permission\Models\Role|null $firstRole */
            $firstRole = $record->roles->first();
            if (! $firstRole || ! in_array($firstRole->name, $impersonatableRoles)) {
                return Color::Gray;
            }

            return Color::Green;
        });

        $this->disabled(function (?User $record) use ($impersonatableRoles): bool {
            /** @var User|null $currentUser */
            $currentUser = Auth::user();

            if (! $currentUser || ! $record) {
                return true;
            }

            if ($record->id === $currentUser->id) {
                return true;
            }
            if ($record->status === ActivityStatus::INACTIVE) {
                return true;
            }
            /** @var \Spatie\Permission\Models\Role|null $firstRole */
            $firstRole = $record->roles->first();
            if (! $firstRole || ! in_array($firstRole->name, $impersonatableRoles)) {
                return true;
            }

            return ! $currentUser->hasPermissionTo(Permission::IMPERSONATE_USERS);
        });

        $this->action(function (?User $record) {
            /** @var User|null $currentUser */
            $currentUser = Auth::user();

            if (! $currentUser || ! $record || ! $currentUser->hasPermissionTo(Permission::IMPERSONATE_USERS)) {
                Notification::make()
                    ->title(__('admin.users.table.impersonate.error.title'))
                    ->body(__('admin.users.table.impersonate.error.body'))
                    ->color(Color::Red)
                    ->icon(Heroicon::ExclamationTriangle)
                    ->send();

                return;
            }

            session()->put('impersonation', [
                'is_impersonating' => true,
                'original_user_id' => $currentUser->id,
                'return_url' => url()->previous(),
                'can_bypass_2fa' => true,
            ]);

            Auth::login($record);

            Activity::inLog('impersonation')
                ->event('impersonate.start')
                ->performedOn($record)
                ->causedBy($currentUser)
                ->withProperties([
                    'user' => [
                        'id' => $record->id,
                        'name' => $record->name,
                        'email' => $record->email,
                        'status' => $record->status->getLabel(),
                        'roles' => Role::from($record->roles->first()->name)->getLabel(),
                    ]
                ])
                ->log('');

            return redirect()->to(path: route('dashboard'));
        });

    }
}
