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

        $nonImpersonatableRoles = [Role::ADMIN->value, Role::SUPER_ADMIN->value];

        $this->icon(Heroicon::ArrowLeftEndOnRectangle);
        $this->label(__('admin.users.table.impersonate.label'));
        $this->name('impersonate-action');
        $this->color(fn (?User $record): array => $this->colorAction($record, $nonImpersonatableRoles));
        $this->disabled(fn (?User $record): bool => $this->disableAction($record, $nonImpersonatableRoles));
        $this->action(fn (?User $record) => $this->impersonate($record));
    }

    /**
     * @param  array<string>  $roles
     * @return array<int, mixed>
     */
    private function colorAction(?User $record, array $roles): array
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser || ! $record || $record->id === $currentUser->id || ! $currentUser->hasPermissionTo(Permission::IMPERSONATE_USERS) || $record->status === ActivityStatus::INACTIVE) {
            return Color::Gray;
        }

        /** @var \Spatie\Permission\Models\Role|null $firstRole */
        $firstRole = $record->roles->first();
        if (! $firstRole || in_array($firstRole->name, $roles)) {
            return Color::Gray;
        }

        return Color::Green;
    }

    /**
     * @param  array<string|int, string>  $roles
     */
    private function disableAction(?User $record, array $roles): bool
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        assert($currentUser instanceof User);
        assert($record instanceof User);

        if ($record->id === $currentUser->id) {
            return true;
        }
        if ($record->status === ActivityStatus::INACTIVE) {
            return true;
        }
        /** @var \Spatie\Permission\Models\Role|null $firstRole */
        $firstRole = $record->roles->first();
        if (! $firstRole || \in_array($firstRole->name, $roles)) {
            return true;
        }

        return ! $currentUser->hasPermissionTo(Permission::IMPERSONATE_USERS);
    }

    private function impersonate(?User $record): void
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();

        assert($currentUser instanceof User);
        assert($record instanceof User);

        if (! $currentUser->hasPermissionTo(Permission::IMPERSONATE_USERS)) {
            Notification::make()
                ->title(__('admin.users.table.impersonate.error.title'))
                ->body(__('admin.users.table.impersonate.error.body'))
                ->color(Color::Red)
                ->icon(Heroicon::ExclamationTriangle)
                ->send();
        }

        // Store impersonation data after migration
        session()->put('impersonation', [
            'is_impersonating' => true,
            'original_user_id' => $currentUser->id,
            'return_url' => url()->previous(),
            'can_bypass_2fa' => true,
        ]);

        Auth::loginUsingId($record->id);

        /**
         * ! ADMIN PANEL ONLY
         * ------------------------------------------------------------
         * Update the password hash in session for AuthenticateSession middleware
         * This prevents the middleware from logging out the impersonated user
         */
        if ($record->hasPermissionTo(Permission::ACCESS_CONTROL_PANEL)) {
            session()->put('password_hash_'.Auth::getDefaultDriver(), $record->getAuthPassword());
        }

        Activity::inLog('impersonation')
            ->event('impersonate.start')
            ->performedOn($record)
            ->causedBy($currentUser)
            ->withProperties([
                'target' => $record->name,
                'user' => [
                    'id' => $record->id,
                    'name' => $record->name,
                    'email' => $record->email,
                    'status' => $record->status->getLabel(),
                    'roles' => $record->roles->first()?->name
                        ? (Role::tryFrom($record->roles->first()->name)?->getLabel() ?? str($record->roles->first()->name)->headline()->toString())
                        : null,
                ],
            ])
            ->log('');

        // Redirect to dashboard after successful impersonation
        $this->redirect(route('dashboard'));
    }
}
